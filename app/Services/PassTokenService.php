<?php

namespace App\Services;

use App\Models\EventRegistration;
use App\Models\PassToken;
use Illuminate\Support\Str;

class PassTokenService
{
    /**
     * Generate or retrieve pass tokens for an EventRegistration
     *
     * @param EventRegistration $registration
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getOrGenerateTokens(EventRegistration $registration)
    {
        $existingTokens = PassToken::where('event_registration_id', $registration->id)->orderBy('pass_index')->get();

        $personCount = max(1, (int)($registration->form_data['person_count'] ?? 1));

        if ($existingTokens->count() >= $personCount) {
            return $existingTokens;
        }

        $appKey = config('app.key', 'sathwara_community_secret_key');

        for ($i = 1; $i <= $personCount; $i++) {
            $token = $existingTokens->firstWhere('pass_index', $i);
            if (!$token) {
                $passNoStr = sprintf('%03d', $i);
                $rawPayload = "evt:{$registration->event_id}:reg:{$registration->id}:p:{$i}:key:{$appKey}";
                $tokenHash = hash_hmac('sha256', $rawPayload, $appKey);

                $passCode = 'PASS-' . sprintf('%04d', $registration->event_id) . '-' . sprintf('%04d', $registration->id) . '-' . $passNoStr;

                PassToken::create([
                    'event_registration_id' => $registration->id,
                    'event_id' => $registration->event_id,
                    'pass_index' => $i,
                    'pass_code' => $passCode,
                    'token_hash' => $tokenHash,
                    'is_checked_in' => false,
                ]);
            }
        }

        return PassToken::where('event_registration_id', $registration->id)->orderBy('pass_index')->get();
    }

    /**
     * Verify QR token and record gate entry check-in
     *
     * @param string $tokenHash
     * @param int $eventId
     * @param int|null $scannerUserId
     * @return array
     */
    public static function verifyAndCheckIn(string $tokenHash, int $eventId, ?int $scannerUserId = null): array
    {
        $token = PassToken::with(['registration.user', 'registration.event'])->where('token_hash', trim($tokenHash))->first();

        if (!$token) {
            // Also check if scanned value was pass_code or raw JSON
            $token = PassToken::with(['registration.user', 'registration.event'])->where('pass_code', trim($tokenHash))->first();
        }

        if (!$token) {
            return [
                'success' => false,
                'status' => 'invalid',
                'message' => 'INVALID / FAKE PASS QR CODE!',
                'detail' => 'This QR code was not issued by the system and could be tampered or fake.',
            ];
        }

        if ((int)$token->event_id !== (int)$eventId) {
            return [
                'success' => false,
                'status' => 'wrong_event',
                'message' => 'WRONG EVENT PASS!',
                'detail' => 'This pass is valid, but issued for a different event: "' . ($token->event->title ?? 'Another Event') . '".',
                'token' => $token,
            ];
        }

        if ($token->is_checked_in) {
            $checkedInAtStr = $token->checked_in_at ? $token->checked_in_at->copy()->setTimezone(config('app.local_timezone'))->format('d M, h:i A') : 'earlier';
            $checkerName = $token->checkedInUser ? $token->checkedInUser->name : 'Gate Scanner';

            return [
                'success' => false,
                'status' => 'already_used',
                'message' => 'WARNING: PASS ALREADY USED!',
                'detail' => 'This pass was already scanned at ' . $checkedInAtStr . ' by ' . $checkerName . '.',
                'token' => $token,
            ];
        }

        // Perform check-in
        $token->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
            'checked_in_by' => $scannerUserId,
        ]);

        return [
            'success' => true,
            'status' => 'checked_in',
            'message' => 'ENTRY APPROVED!',
            'detail' => 'Pass #' . $token->pass_index . ' verified successfully.',
            'token' => $token,
        ];
    }

    /**
     * Get QR Code Image URL / API endpoint
     *
     * @param string $tokenHash
     * @return string
     */
    public static function getQrCodeImageUrl(string $tokenHash): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($tokenHash);
    }
}
