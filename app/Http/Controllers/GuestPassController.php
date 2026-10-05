<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Setting;
use App\Services\GuestPassService;
use App\Services\PassTokenService;
use App\Services\ReceiptNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

/**
 * Pass purchase for non-members on events that allow it (events.pass_purchase_access = 'anyone').
 */
class GuestPassController extends Controller
{
    private const SESSION_GUEST = 'guest_pass.guest';

    public function __construct(private GuestPassService $guestPasses)
    {
    }

    /**
     * Step 1: take the buyer's email, mobile and area. No login needed, even for registered members:
     * a member's purchase is linked to their account by email when the pass is issued.
     */
    public function saveDetails(Request $request, $id): JsonResponse
    {
        $event = $this->purchasableEvent($id);
        if ($event instanceof JsonResponse) {
            return $event;
        }

        $emailRules = ['required', 'string', 'max:255', config('guest_pass.check_dns') ? 'email:rfc,dns' : 'email:rfc'];
        $validator = Validator::make($request->all(), [
            'email' => $emailRules,
            'mobile' => ['required', 'string'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
        ], [
            'area_id.required' => 'Please select your area.',
            'area_id.exists' => 'Please select a valid area.',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $email = strtolower(trim($request->input('email')));
        $mobile = GuestPassService::normalizeMobile($request->input('mobile'));

        if (GuestPassService::isBlockedEmailDomain($email)) {
            return $this->error('Please enter a real email address. Temporary or dummy emails are not accepted.', 422);
        }
        if (!$mobile) {
            return $this->error('Please enter a valid 10-digit mobile number.', 422);
        }

        session([self::SESSION_GUEST => [
            'email' => $email,
            'mobile' => $mobile,
            'area_id' => (int) $request->input('area_id'),
            'expires_at' => now()->addMinutes((int) config('guest_pass.details_ttl_minutes'))->timestamp,
        ]]);

        return response()->json(['success' => true, 'email' => $email]);
    }

    /**
     * Step 2: validate the purchase and, for paid events, create the Razorpay order for it.
     * The amount is always computed here, never taken from the browser.
     */
    public function createOrder(Request $request, $id): JsonResponse
    {
        $event = $this->purchasableEvent($id);
        if ($event instanceof JsonResponse) {
            return $event;
        }

        $verified = $this->guestDetails();
        if (!$verified) {
            return $this->error('Please enter your email and mobile number first.', 403);
        }

        $personCount = $this->validPersonCount($request);
        if ($personCount instanceof JsonResponse) {
            return $personCount;
        }

        if ($problem = $this->availabilityProblem($event, $personCount)) {
            return $this->error($problem, 422);
        }

        if ((float) $event->pass_fee <= 0) {
            return response()->json(['success' => true, 'free' => true]);
        }

        try {
            $order = $this->guestPasses->createOrder(
                $event,
                $verified['email'],
                $verified['mobile'],
                $verified['area_id'],
                $this->cleanName($request),
                $personCount
            );
        } catch (\Throwable $e) {
            return $this->error('Could not start the payment. Please try again.', 502);
        }

        return response()->json([
            'success' => true,
            'free' => false,
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'key' => (string) Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', '')),
            'email' => $verified['email'],
            'mobile' => $verified['mobile'],
        ]);
    }

    /**
     * Step 3: create the registration (free events), or confirm the Razorpay payment and create it.
     */
    public function complete(Request $request, $id): JsonResponse
    {
        $event = $this->purchasableEvent($id);
        if ($event instanceof JsonResponse) {
            return $event;
        }

        $verified = $this->guestDetails();
        if (!$verified) {
            return $this->error('Please enter your email and mobile number first.', 403);
        }

        if ((float) $event->pass_fee <= 0) {
            $personCount = $this->validPersonCount($request);
            if ($personCount instanceof JsonResponse) {
                return $personCount;
            }
            if ($problem = $this->availabilityProblem($event, $personCount)) {
                return $this->error($problem, 422);
            }

            [$registration] = $this->guestPasses->fulfillFreePass(
                $event,
                $verified['email'],
                $verified['mobile'],
                $verified['area_id'],
                $this->cleanName($request),
                $personCount
            );

            return $this->purchased($event, $registration, $personCount, 0.0);
        }

        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => ['required', 'string', 'max:64'],
            'razorpay_payment_id' => ['required', 'string', 'max:64'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return $this->error('Payment details are missing.', 422);
        }

        $orderId = $request->input('razorpay_order_id');
        $paymentId = $request->input('razorpay_payment_id');

        if (!$this->guestPasses->isValidSignature($orderId, $paymentId, $request->input('razorpay_signature'))) {
            return $this->error('Payment could not be verified.', 422);
        }

        $order = $this->guestPasses->fetchOrder($orderId);
        if (!$order || !GuestPassService::isGuestOrder($order)
            || (int) ($order['notes']['event_id'] ?? 0) !== (int) $event->id
            || ($order['notes']['email'] ?? null) !== $verified['email']) {
            return $this->error('This payment does not match your booking.', 422);
        }

        if (($order['status'] ?? null) !== 'paid') {
            // Razorpay has not settled it yet; the order.paid webhook will issue the pass and email it.
            $message = 'Your payment is being confirmed. Your pass will be emailed to ' . $verified['email'] . ' shortly.';
            session()->flash('success', $message);

            return response()->json([
                'success' => true,
                'pending' => true,
                'redirect' => route('event.details', $event->id),
                'message' => $message,
            ]);
        }

        [$registration] = $this->guestPasses->fulfillPaidOrder($order, $paymentId);
        if (!$registration) {
            return $this->error('We received your payment but could not issue the pass. Please contact the organisers with payment ID ' . $paymentId . '.', 500);
        }

        return $this->purchased(
            $event,
            $registration,
            (int) ($registration->form_data['person_count'] ?? 1),
            (float) $registration->payment_amount
        );
    }

    private function purchased(Event $event, EventRegistration $registration, int $personCount, float $amount): JsonResponse
    {
        session()->flash('success', 'Pass purchased successfully! Your pass has been sent to ' . ($registration->form_data['email'] ?? 'your email') . '.');
        session()->flash('purchase_receipt', $this->receiptPayload($event, $registration, $personCount, $amount));

        return response()->json(['success' => true, 'redirect' => route('event.details', $event->id)]);
    }

    private function receiptPayload(Event $event, EventRegistration $registration, int $personCount, float $amount): array
    {
        $tokens = PassTokenService::getOrGenerateTokens($registration);
        $attendee = $registration->form_data['full_name'] ?? 'Guest';
        $basePassNo = (int) ($registration->pass_number ?: $registration->id);

        $passCards = [];
        foreach ($tokens as $idx => $token) {
            $passCards[] = [
                'passNo' => sprintf('%03d', $basePassNo + $idx),
                'passCode' => $token->pass_code,
                'qrUrl' => PassTokenService::getQrCodeImageUrl($token->token_hash),
                'attendee' => $attendee,
            ];
        }

        // The public receipt route is keyed by a guessable id, so guests get signed links instead.
        $downloadUrl = URL::signedRoute('receipts.guest_event_pass', ['id' => $registration->id]);
        $viewUrl = URL::signedRoute('receipts.guest_event_pass', ['id' => $registration->id, 'view' => 1]);

        return [
            'type' => 'event_pass',
            'receipt_no' => $registration->receipt_no ?: ReceiptNumberService::assign($registration, 'receipt_no'),
            'event_title' => $event->title,
            'event_date' => date('d M, Y', strtotime($event->date)),
            'event_time' => $event->time ? date('h:i A', strtotime($event->time)) : null,
            'event_venue' => $event->venue,
            'attendee_name' => $attendee,
            'member_code' => '-',
            'phone' => $registration->form_data['contact_number'] ?? '',
            'person_count' => $personCount,
            'purchased_now' => $personCount,
            'pass_fee' => (float) ($event->pass_fee ?? 0),
            'amount_paid' => $amount,
            'total_amount' => (float) ($registration->payment_amount ?? $amount),
            'payment_id' => $registration->payment_id,
            'payment_status' => $registration->payment_status ?? 'paid',
            'created_at' => now()->format('d M, Y h:i A'),
            'download_url' => $downloadUrl,
            'view_url' => $viewUrl,
            'passes' => $passCards,
        ];
    }

    /**
     * @return Event|JsonResponse
     */
    private function purchasableEvent($id)
    {
        $event = Event::published()->findOrFail($id);

        if (!$event->allowsGuestPassPurchase()) {
            return $this->error('This event is only open to logged-in members.', 403);
        }

        if (!empty($event->registration_end_date) && now()->toDateString() > $event->registration_end_date->toDateString()) {
            return $this->error('Pass purchase for this event closed on ' . $event->registration_end_date->format('d-M-Y') . '.', 422);
        }

        return $event;
    }

    /**
     * @return array{email: string, mobile: string, area_id: ?int}|null
     */
    private function guestDetails(): ?array
    {
        $guest = session(self::SESSION_GUEST);

        if (!$guest || now()->timestamp > ($guest['expires_at'] ?? 0)) {
            session()->forget(self::SESSION_GUEST);
            return null;
        }

        return ['email' => $guest['email'], 'mobile' => $guest['mobile'], 'area_id' => $guest['area_id'] ?? null];
    }

    /**
     * @return int|JsonResponse
     */
    private function validPersonCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'person_count' => ['required', 'integer', 'min:1', 'max:' . (int) config('guest_pass.max_persons_per_purchase')],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        return (int) $request->input('person_count');
    }

    private function availabilityProblem(Event $event, int $personCount): ?string
    {
        $remaining = GuestPassService::remainingPasses($event);

        if ($remaining === null || $personCount <= $remaining) {
            return null;
        }

        return $remaining > 0
            ? "Only {$remaining} pass(es) remaining for this event."
            : 'Sorry, all passes for this event have been sold out.';
    }

    private function cleanName(Request $request): ?string
    {
        $name = trim(strip_tags((string) $request->input('name')));

        return $name !== '' ? $name : null;
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
