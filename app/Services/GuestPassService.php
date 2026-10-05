<?php

namespace App\Services;

use App\Mail\EventPassPurchasedMail;
use App\Models\Area;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Pass purchases by non-members (no login): email + mobile. Member emails must log in instead.
 *
 * Payment is verified server-side: the amount is fixed by a Razorpay order we create,
 * and a registration is only created for an order that Razorpay reports as paid.
 */
class GuestPassService
{
    public const ORDER_PURPOSE = 'guest_event_pass';

    /**
     * Normalise an Indian mobile number to 10 digits, or null when it is not a valid one.
     */
    public static function normalizeMobile(?string $mobile): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $mobile);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[6-9]\d{9}$/', $digits) ? $digits : null;
    }

    public static function isBlockedEmailDomain(string $email): bool
    {
        $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));
        if ($domain === '') {
            return true;
        }

        foreach (config('guest_pass.blocked_domains', []) as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.' . $blocked)) {
                return true;
            }
        }

        // Placeholder-looking addresses such as dummy123@gmail.com
        return str_starts_with(strtolower(strstr($email, '@', true)), 'dummy');
    }

    /**
     * Number of passes still available, or null when the event has no limit.
     */
    public static function remainingPasses(Event $event): ?int
    {
        if (empty($event->total_pass_limit)) {
            return null;
        }

        return max(0, (int) $event->total_pass_limit - (int) $event->total_passes_count);
    }

    public static function totalFor(Event $event, int $personCount): float
    {
        return round((float) ($event->pass_fee ?? 0) * $personCount, 2);
    }

    /**
     * Create a Razorpay order for the given purchase. Everything needed to fulfil it later
     * (even if the browser never comes back) travels in the order notes.
     *
     * @return array<string, mixed> Razorpay order entity
     */
    public function createOrder(Event $event, string $email, string $mobile, ?int $areaId, ?string $name, int $personCount): array
    {
        $amount = self::totalFor($event, $personCount);

        $response = Http::withBasicAuth($this->keyId(), $this->keySecret())
            ->asJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round($amount * 100),
                'currency' => 'INR',
                'receipt' => 'GP-' . $event->id . '-' . time(),
                'notes' => [
                    'purpose' => self::ORDER_PURPOSE,
                    'event_id' => (string) $event->id,
                    'email' => $email,
                    'mobile' => $mobile,
                    'area_id' => (string) $areaId,
                    'name' => (string) $name,
                    'person_count' => (string) $personCount,
                ],
            ]);

        if (!$response->successful()) {
            Log::error('Guest pass: Razorpay order creation failed', [
                'event_id' => $event->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('Failed to create Razorpay order.');
        }

        return $response->json();
    }

    /**
     * @return array<string, mixed>|null Razorpay order entity, or null when it cannot be fetched
     */
    public function fetchOrder(string $orderId): ?array
    {
        $response = Http::withBasicAuth($this->keyId(), $this->keySecret())
            ->get('https://api.razorpay.com/v1/orders/' . urlencode($orderId));

        if (!$response->successful()) {
            Log::error('Guest pass: Razorpay order fetch failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    public function isValidSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $secret = $this->keySecret();
        if ($secret === '' || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $orderId . '|' . $paymentId, $secret), $signature);
    }

    public static function isGuestOrder(array $order): bool
    {
        return ($order['notes']['purpose'] ?? null) === self::ORDER_PURPOSE;
    }

    /**
     * Create the registration for a paid Razorpay order. Idempotent: the browser callback and the
     * `order.paid` webhook can both call this and only the first one creates (and emails) the pass.
     *
     * @param array<string, mixed> $order Razorpay order entity (must be paid)
     * @return array{0: ?EventRegistration, 1: bool} [registration, createdNow]
     */
    public function fulfillPaidOrder(array $order, string $paymentId): array
    {
        $orderId = $order['id'] ?? null;
        $notes = $order['notes'] ?? [];

        if (!$orderId || !self::isGuestOrder($order) || ($order['status'] ?? null) !== 'paid') {
            return [null, false];
        }

        $existing = EventRegistration::where('razorpay_order_id', $orderId)->first();
        if ($existing) {
            return [$existing, false];
        }

        $event = Event::find($notes['event_id'] ?? null);
        if (!$event) {
            Log::error('Guest pass: paid order for unknown event', ['order_id' => $orderId, 'notes' => $notes]);
            return [null, false];
        }

        $personCount = max(1, (int) ($notes['person_count'] ?? 1));
        $amountPaid = ((int) ($order['amount_paid'] ?? 0)) / 100;

        return $this->createRegistration(
            $event,
            (string) ($notes['email'] ?? ''),
            (string) ($notes['mobile'] ?? ''),
            ($notes['area_id'] ?? '') !== '' ? (int) $notes['area_id'] : null,
            ($notes['name'] ?? '') !== '' ? $notes['name'] : null,
            $personCount,
            $amountPaid,
            $paymentId,
            $orderId
        );
    }

    /**
     * Create the registration for a free event (no payment involved).
     *
     * @return array{0: ?EventRegistration, 1: bool}
     */
    public function fulfillFreePass(Event $event, string $email, string $mobile, ?int $areaId, ?string $name, int $personCount): array
    {
        return $this->createRegistration($event, $email, $mobile, $areaId, $name, $personCount, 0.0, null, null);
    }

    /**
     * @return array{0: ?EventRegistration, 1: bool}
     */
    private function createRegistration(
        Event $event,
        string $email,
        string $mobile,
        ?int $areaId,
        ?string $name,
        int $personCount,
        float $amount,
        ?string $paymentId,
        ?string $orderId
    ): array {
        $area = $areaId ? Area::find($areaId) : null;

        // The email is not verified, so the pass is never attached to a member account.
        try {
            $registration = DB::transaction(function () use ($event, $email, $mobile, $area, $name, $personCount, $amount, $paymentId, $orderId) {
                $passNumber = EventSequenceService::nextPassNumber($event->id);

                return EventRegistration::create([
                    'event_id' => $event->id,
                    'pass_number' => $passNumber,
                    'registration_type' => 'pass',
                    'user_id' => null,
                    'status' => 'approved',
                    'form_data' => [
                        'full_name' => $name ?: 'Guest',
                        'email' => $email,
                        'contact_number' => $mobile,
                        'mobile' => $mobile,
                        'area_id' => $area?->id,
                        'area' => $area?->name ?? '',
                        'person_count' => $personCount,
                        'registration_no' => $passNumber,
                        'is_guest' => true,
                        'email_verified' => false,
                        'submission_date' => now()->format('d-M-Y h:i A'),
                    ],
                    'payment_id' => $paymentId,
                    'razorpay_order_id' => $orderId,
                    'payment_status' => 'paid',
                    'payment_amount' => $amount,
                ]);
            });
        } catch (UniqueConstraintViolationException $e) {
            if (!$orderId) {
                throw $e;
            }

            // The other path (browser callback vs webhook) won the race for this order
            return [EventRegistration::where('razorpay_order_id', $orderId)->first(), false];
        }

        PassTokenService::getOrGenerateTokens($registration);

        $this->notifyAdmins($event, $registration, $name ?: 'Guest');
        $this->sendPassEmail($event, $registration, $email, $personCount, null);

        return [$registration, true];
    }

    private function notifyAdmins(Event $event, EventRegistration $registration, string $name): void
    {
        try {
            AdminNotifier::send(
                permission: 'events_manage',
                type: 'event_pass_registered',
                title: 'New Event Pass Registration',
                message: "{$name} (guest) registered for \"{$event->title}\" (Pass #{$registration->pass_number})",
                url: route('admin.events.registrations.edit', $registration->id),
                meta: ['event_id' => $event->id, 'registration_id' => $registration->id],
                color: 'sky'
            );
        } catch (\Throwable $e) {
            Log::error('Guest pass: admin notification failed: ' . $e->getMessage());
        }
    }

    private function sendPassEmail(Event $event, EventRegistration $registration, string $email, int $personCount, ?User $user): void
    {
        $passes = [];
        for ($i = 1; $i <= $personCount; $i++) {
            $passes[] = sprintf('%03d', $i);
        }

        try {
            Mail::to($email)->send(new EventPassPurchasedMail($event, $registration, $user, $passes, $personCount));
        } catch (\Throwable $e) {
            Log::error('Guest pass: pass email failed: ' . $e->getMessage(), ['registration_id' => $registration->id]);
        }
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
