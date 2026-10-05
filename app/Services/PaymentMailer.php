<?php

namespace App\Services;

use App\Mail\PaymentEmailUndeliveredMail;
use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends payment / purchase emails. When the buyer's address is missing, cannot receive mail or the
 * send fails, the office is told that a payment was received but the receipt did not reach the buyer.
 */
class PaymentMailer
{
    /**
     * @param array<string, scalar|null> $details shown to the office if delivery fails (name, amount, payment id...)
     */
    public static function send(?string $email, Mailable $mail, string $purpose, array $details = []): bool
    {
        $email = trim((string) $email);

        $problem = self::addressProblem($email);
        if ($problem === null) {
            try {
                Mail::to($email)->send($mail);
                return true;
            } catch (\Throwable $e) {
                $problem = 'Sending failed: ' . $e->getMessage();
            }
        }

        Log::warning("Payment email not delivered ({$purpose}): {$problem}", ['email' => $email] + $details);
        self::alertOffice($email, $purpose, $problem, $details);

        return false;
    }

    private static function addressProblem(string $email): ?string
    {
        if ($email === '') {
            return 'No email address was given.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'The email address is not valid.';
        }

        // Catches typos such as "gmial.con" whose domain cannot receive mail
        $domain = substr(strrchr($email, '@'), 1);
        if (!app()->environment('testing') && function_exists('checkdnsrr')
            && !checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            return "The email domain \"{$domain}\" cannot receive mail.";
        }

        return null;
    }

    private static function alertOffice(string $email, string $purpose, string $problem, array $details): void
    {
        AdminNotifier::send(
            permission: null,
            type: 'payment_email_failed',
            title: 'Payment received — email not delivered',
            message: "{$purpose}: receipt could not be sent to " . ($email ?: '(no email)') . ". {$problem}",
            meta: $details,
            icon: 'mail',
            color: 'rose'
        );

        $officeEmail = Setting::get('contact_email', config('mail.from.address'));
        if (empty($officeEmail) || strcasecmp($officeEmail, $email) === 0) {
            return;
        }

        try {
            Mail::to($officeEmail)->send(new PaymentEmailUndeliveredMail($purpose, $email, $problem, $details));
        } catch (\Throwable $e) {
            Log::error('Office alert for undelivered payment email also failed: ' . $e->getMessage());
        }
    }
}
