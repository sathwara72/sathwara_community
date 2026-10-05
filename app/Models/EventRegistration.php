<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'pass_number',
        'inam_number',
        'yuva_melo_number',
        'registration_type',
        'user_id',
        'status',
        'form_data',
        'is_selected',
        'payment_id',
        'razorpay_order_id',
        'payment_status',
        'payment_amount',
    ];

    protected $casts = [
        'pass_number' => 'integer',
        'inam_number' => 'integer',
        'yuva_melo_number' => 'integer',
        'form_data' => 'array',
        'is_selected' => 'boolean',
    ];

    /**
     * Get the active event-wise reference number based on type
     */
    public function getReferenceNumberAttribute(): ?int
    {
        return match ($this->registration_type) {
            'inam_vitran' => $this->inam_number,
            'yuva_melo' => $this->yuva_melo_number,
            default => $this->pass_number ?? $this->inam_number ?? $this->yuva_melo_number,
        };
    }

    /**
     * Get formatted reference number (e.g. 001)
     */
    public function getFormattedReferenceNumberAttribute(): string
    {
        $num = $this->reference_number;
        return $num ? str_pad((string)$num, 3, '0', STR_PAD_LEFT) : '-';
    }

    /**
     * Scopes for filtering by sequence type
     */
    public function scopePasses($query)
    {
        return $query->where('registration_type', 'pass')->orWhere(function ($q) {
            $q->whereNull('registration_type')->whereNotNull('pass_number');
        });
    }

    public function scopeInamSubmissions($query)
    {
        return $query->where('registration_type', 'inam_vitran')->orWhereNotNull('inam_number');
    }

    public function scopeYuvaMeloSubmissions($query)
    {
        return $query->where('registration_type', 'yuva_melo')->orWhereNotNull('yuva_melo_number');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function passTokens()
    {
        return $this->hasMany(PassToken::class, 'event_registration_id');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'payable')->latest('id');
    }
}
