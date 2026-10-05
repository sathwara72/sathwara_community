<?php

namespace App\Http\Controllers;

use App\Services\PaymentMailer;
use App\Services\RazorpayVerifier;
use App\Mail\BusinessRenewalReceiptMail;
use App\Models\BusinessPaymentLink;
use App\Models\Setting;
use App\Services\GuestPassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = Setting::get('razorpay_webhook_secret', env('RAZORPAY_WEBHOOK_SECRET', ''));
        $signature = $request->header('X-Razorpay-Signature', '');

        if (empty($secret) || empty($signature) || !hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $signature)) {
            Log::warning('Razorpay webhook: invalid signature');
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $payload = $request->json()->all();
        $event = $payload['event'] ?? null;

        try {
            if ($event === 'payment_link.paid') {
                $this->handlePaymentLinkPaid($payload);
            } elseif ($event === 'order.paid') {
                $this->handleOrderPaid($payload);
            }
        } catch (\Throwable $e) {
            Log::error('Razorpay webhook processing error: ' . $e->getMessage(), ['payload' => $payload]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Safety net for guest pass purchases: issues the pass even if the buyer closed the browser
     * after paying and never came back to the site.
     */
    private function handleOrderPaid(array $payload): void
    {
        $order = $payload['payload']['order']['entity'] ?? null;
        $paymentId = $payload['payload']['payment']['entity']['id'] ?? null;

        if (!$order || !$paymentId || !GuestPassService::isGuestOrder($order)) {
            return;
        }

        app(GuestPassService::class)->fulfillPaidOrder($order, $paymentId);
    }

    private function handlePaymentLinkPaid(array $payload): void
    {
        $linkEntity = $payload['payload']['payment_link']['entity'] ?? null;
        $paymentEntity = $payload['payload']['payment']['entity'] ?? null;

        if (!$linkEntity || empty($linkEntity['id'])) {
            return;
        }

        $link = BusinessPaymentLink::where('razorpay_link_id', $linkEntity['id'])->first();

        if (!$link || $link->status === 'paid') {
            return;
        }

        $razorpayPaymentId = $paymentEntity['id'] ?? null;

        $link->status = 'paid';
        $link->paid_at = now();
        $link->razorpay_payment_id = $razorpayPaymentId;
        $link->save();

        $business = $link->business;
        if (!$business) {
            return;
        }

        $business->approved_at = now();
        $business->status = 'approved';
        $business->membership_status = 'active';
        $business->payment_status = 'paid';
        $business->payment_id = $razorpayPaymentId;
        $business->payment_amount = $link->amount;
        $business->save();

        app(RazorpayVerifier::class)->record($razorpayPaymentId, 'business_renewal', (float) $link->amount, $business, $business->owner_name . ' (' . $business->business_name . ')', $business->phone);

        PaymentMailer::send($business->email, new BusinessRenewalReceiptMail($business, $link), 'Business renewal', [
            'Business' => $business->business_name,
            'Amount' => '₹' . number_format((float) $link->amount, 2),
            'Payment ID' => $link->razorpay_payment_id,
            'Phone' => $business->phone,
        ]);
    }
}
