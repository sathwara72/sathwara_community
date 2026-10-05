<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One successful payment. transaction_no (TXN-000001) is the Mandal's own reference for it.
 */
class Transaction extends Model
{
    public const PURPOSES = [
        'membership' => 'Membership registration',
        'business_registration' => 'Business registration',
        'business_renewal' => 'Business renewal',
        'event_pass' => 'Event pass',
        'guest_event_pass' => 'Event pass (no login)',
        'yuva_melo_fee' => 'Yuva Melo form fee',
        'sponsorship' => 'Sponsorship',
    ];

    protected $fillable = [
        'payment_id',
        'method',
        'purpose',
        'amount',
        'payable_type',
        'payable_id',
        'payer_name',
        'payer_contact',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (Transaction $transaction) {
            $transaction->transaction_no = 'TXN-' . str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT);
            $transaction->saveQuietly();
        });
    }

    /**
     * A payment taken at the office (cash / cheque / bank transfer) and entered by an admin.
     */
    public static function recordOffice(string $purpose, float $amount, ?Model $payable = null, ?string $payerName = null, ?string $payerContact = null): ?self
    {
        if ($amount <= 0) {
            return null;
        }

        return self::create([
            'method' => 'office',
            'purpose' => $purpose,
            'amount' => $amount,
            'payable_type' => $payable?->getMorphClass(),
            'payable_id' => $payable?->getKey(),
            'payer_name' => $payerName,
            'payer_contact' => $payerContact,
            'recorded_by' => auth('web')->id(),
        ]);
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getPurposeLabelAttribute(): string
    {
        return self::PURPOSES[$this->purpose] ?? ucfirst(str_replace('_', ' ', $this->purpose));
    }
}
