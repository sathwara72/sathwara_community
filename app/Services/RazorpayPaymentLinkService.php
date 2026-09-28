<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RazorpayPaymentLinkService
{
    /**
     * Create a Razorpay Payment Link valid for 24 hours.
     *
     * @return array{id: string, short_url: string, expires_at: \Illuminate\Support\Carbon}
     */
    public function createLink(Business $business, float $amount): array
    {
        $keyId = Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', ''));
        $keySecret = Setting::get('razorpay_key_secret', env('RAZORPAY_KEY_SECRET', ''));

        $expiresAt = now()->addHours(24);

        $payload = [
            'amount' => (int) round($amount * 100),
            'currency' => 'INR',
            'description' => 'Business Renewal Fee - ' . $business->business_name,
            'customer' => [
                'name' => $business->owner_name ?: $business->business_name,
                'email' => $business->email ?: null,
                'contact' => $this->normalizePhone($business->whatsapp ?: $business->phone),
            ],
            'notify' => [
                'sms' => false,
                'email' => false,
            ],
            'reminder_enable' => false,
            'expire_by' => $expiresAt->timestamp,
            'reference_id' => 'BIZ-' . $business->id . '-' . time(),
            'callback_url' => route('business.renewal.callback'),
            'callback_method' => 'get',
        ];

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->asJson()
            ->post('https://api.razorpay.com/v1/payment_links', $payload);

        if (!$response->successful()) {
            Log::error('Razorpay payment link creation failed', [
                'business_id' => $business->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('Failed to create Razorpay payment link: ' . $response->body());
        }

        $data = $response->json();

        return [
            'id' => $data['id'],
            'short_url' => $data['short_url'],
            'expires_at' => $expiresAt,
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }

        return '+' . ltrim($digits, '+');
    }
}
