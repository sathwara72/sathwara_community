<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One opening of the Razorpay popup and how it ended. The browser reports opened / cancelled /
 * failed / paid; the server then reports verified / verification_failed / completed.
 */
class PaymentAttempt extends Model
{
    public const STATUSES = [
        'opened' => 'Popup opened',
        'cancelled' => 'Cancelled',
        'failed' => 'Payment failed',
        'paid' => 'Paid, not verified yet',
        'verified' => 'Verified, not saved',
        'verification_failed' => 'Verification failed',
        'completed' => 'Completed',
    ];

    // Statuses the browser may report
    public const CLIENT_STATUSES = ['opened', 'cancelled', 'failed', 'paid'];

    // Set by the server; the browser can no longer change them
    public const SERVER_STATUSES = ['verified', 'verification_failed', 'completed'];

    protected $fillable = [
        'attempt_key',
        'purpose',
        'description',
        'amount',
        'payer_name',
        'payer_contact',
        'payer_email',
        'user_id',
        'status',
        'razorpay_payment_id',
        'razorpay_order_id',
        'error',
        'razorpay_status',
        'razorpay_checked_at',
        'transaction_id',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'razorpay_checked_at' => 'datetime',
    ];

    /**
     * Apply a status reported by the browser. Returns false when the change is not allowed.
     */
    public function applyClientStatus(string $status): bool
    {
        if (in_array($this->status, self::SERVER_STATUSES, true)) {
            return false;
        }

        return match ($status) {
            'opened' => !$this->exists,
            // Closing the popup after a failure keeps the failure (it says more)
            'cancelled' => !$this->exists || $this->status === 'opened',
            'failed' => $this->status !== 'paid',
            'paid' => true,
            default => false,
        };
    }

    /**
     * Server-side outcome for a Razorpay payment (verified / verification_failed / completed).
     * Updates the attempt the browser logged for it, or starts one if the browser never did.
     */
    public static function recordOutcome(string $paymentId, string $status, array $attributes = []): void
    {
        try {
            $attempt = self::where('razorpay_payment_id', $paymentId)->latest('id')->first();

            // A completed attempt is final: a later re-submit of the same payment must not undo it
            if ($attempt && $attempt->status === 'completed' && $status !== 'completed') {
                return;
            }

            if (!$attempt) {
                $attempt = new self([
                    'attempt_key' => (string) Str::uuid(),
                    'razorpay_payment_id' => $paymentId,
                    'user_id' => auth('web')->id(),
                    'ip' => request()->ip(),
                    'user_agent' => Str::limit((string) request()->userAgent(), 250, ''),
                ]);
            }

            // Keep what the browser logged; only fill the gaps
            foreach ($attributes as $key => $value) {
                if ($value !== null && ($key === 'error' || $key === 'transaction_id' || empty($attempt->{$key}))) {
                    $attempt->{$key} = $value;
                }
            }
            if ($status !== 'verification_failed' && !array_key_exists('error', $attributes)) {
                $attempt->error = null;
            }
            $attempt->status = $status;
            $attempt->save();
        } catch (\Throwable $e) {
            // Logging must never get in the way of a payment
            report($e);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getPurposeLabelAttribute(): string
    {
        return Transaction::PURPOSES[$this->purpose] ?? ($this->purpose ? ucfirst(str_replace('_', ' ', $this->purpose)) : '-');
    }
}
