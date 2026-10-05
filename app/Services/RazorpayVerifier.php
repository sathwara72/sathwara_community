<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side checks for Razorpay Checkout payments. Fails closed: a payment is only accepted
 * when Razorpay confirms it, it covers the fee, and it has not paid for anything else already.
 */
class RazorpayVerifier
{
    /**
     * @return array{valid: bool, error: ?string}
     */
    public function verify(?string $paymentId, float $expectedAmount): array
    {
        if ($expectedAmount <= 0) {
            return ['valid' => true, 'error' => null];
        }

        if (empty($paymentId)) {
            return ['valid' => false, 'error' => 'Payment is required. Please complete the online payment.'];
        }

        if ($this->isUsed($paymentId)) {
            return ['valid' => false, 'error' => 'This payment transaction ID has already been utilized.'];
        }

        // Tests never reach Razorpay; everywhere else an unverifiable payment is refused
        if (!$this->checksWithRazorpay()) {
            return ['valid' => true, 'error' => null];
        }

        if (!$this->configured()) {
            Log::critical('Razorpay keys are not configured; refusing payment ' . $paymentId);
            return ['valid' => false, 'error' => 'Online payment is not configured. Please contact the office.'];
        }

        try {
            $response = Http::withBasicAuth($this->keyId(), $this->keySecret())
                ->timeout(10)
                ->get('https://api.razorpay.com/v1/payments/' . urlencode($paymentId));
        } catch (\Throwable $e) {
            Log::error('Razorpay verification exception: ' . $e->getMessage());
            return ['valid' => false, 'error' => 'Failed to connect to Razorpay to verify payment. Please try again.'];
        }

        if (!$response->successful()) {
            Log::error('Razorpay payment fetch failed: ' . $response->body());
            return ['valid' => false, 'error' => 'Payment could not be verified with Razorpay.'];
        }

        $data = $response->json();
        $amountPaid = ((int) ($data['amount'] ?? 0)) / 100;
        if ($amountPaid < $expectedAmount) {
            return ['valid' => false, 'error' => "Payment amount (₹{$amountPaid}) does not match the required fee (₹{$expectedAmount})."];
        }

        // Only money actually collected counts. "authorized" is captured here first; failed, created
        // (abandoned / timed out), refunded or any other status is never accepted.
        $status = $data['status'] ?? '';
        if ($status === 'authorized') {
            $status = $this->capture($paymentId, (int) $data['amount'], $data['currency'] ?? 'INR');
        }
        if ($status !== 'captured') {
            Log::warning("Razorpay payment {$paymentId} refused, status: {$status}");
            return ['valid' => false, 'error' => 'Payment transaction was not successful on Razorpay. If money was deducted, it will be refunded automatically by your bank.'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Capture an authorized payment. Returns the payment's status afterwards ("captured" on success).
     */
    private function capture(string $paymentId, int $amountPaise, string $currency): string
    {
        try {
            $response = Http::withBasicAuth($this->keyId(), $this->keySecret())
                ->timeout(15)
                ->asJson()
                ->post('https://api.razorpay.com/v1/payments/' . urlencode($paymentId) . '/capture', [
                    'amount' => $amountPaise,
                    'currency' => $currency,
                ]);

            if ($response->successful()) {
                return (string) ($response->json('status') ?? '');
            }

            // Auto-capture may have captured it a moment earlier: ask again
            $recheck = Http::withBasicAuth($this->keyId(), $this->keySecret())
                ->timeout(10)
                ->get('https://api.razorpay.com/v1/payments/' . urlencode($paymentId));

            return (string) ($recheck->json('status') ?? '');
        } catch (\Throwable $e) {
            Log::error("Razorpay capture failed for {$paymentId}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Record a successful payment in the transactions ledger (gives it a TXN number) once the thing
     * it paid for has been saved. Never called for failed or cancelled payments.
     */
    public function record(?string $paymentId, string $purpose, ?float $amount = null, ?Model $payable = null, ?string $payerName = null, ?string $payerContact = null): ?Transaction
    {
        if (empty($paymentId)) {
            return null;
        }

        try {
            return Transaction::create([
                'payment_id' => $paymentId,
                'method' => 'razorpay',
                'purpose' => $purpose,
                'amount' => (float) $amount,
                'payable_type' => $payable?->getMorphClass(),
                'payable_id' => $payable?->getKey(),
                'payer_name' => $payerName,
                'payer_contact' => $payerContact,
                'recorded_by' => auth('web')->id(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            Log::warning("Payment {$paymentId} was already recorded", ['purpose' => $purpose]);
            return Transaction::where('payment_id', $paymentId)->first();
        }
    }

    public function isUsed(string $paymentId): bool
    {
        return DB::table('transactions')->where('payment_id', $paymentId)->exists()
            // Payments accepted before the ledger existed
            || DB::table('users')->where('payment_id', $paymentId)->exists()
            || DB::table('businesses')->where('payment_id', $paymentId)->exists()
            || DB::table('business_payment_links')->where('razorpay_payment_id', $paymentId)->exists()
            || DB::table('event_registrations')->where('payment_id', $paymentId)->exists()
            || DB::table('event_sponsors')->where('payment_id', $paymentId)->exists();
    }

    /**
     * Fetch a Payment Link from Razorpay (never trust the status in the redirect URL).
     *
     * @return array<string, mixed>|null
     */
    public function fetchPaymentLink(string $linkId): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->keyId(), $this->keySecret())
                ->timeout(10)
                ->get('https://api.razorpay.com/v1/payment_links/' . urlencode($linkId));
        } catch (\Throwable $e) {
            Log::error('Razorpay payment link fetch exception: ' . $e->getMessage());
            return null;
        }

        return $response->successful() ? $response->json() : null;
    }

    protected function checksWithRazorpay(): bool
    {
        return !app()->environment('testing');
    }

    public function configured(): bool
    {
        return $this->keyId() !== '' && $this->keySecret() !== '';
    }

    private function keyId(): string
    {
        return (string) Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', ''));
    }

    private function keySecret(): string
    {
        return (string) Setting::get('razorpay_key_secret', env('RAZORPAY_KEY_SECRET', ''));
    }
}
