<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessMembership extends Model
{
    protected $fillable = [
        'business_id',
        'started_at',
        'expires_at',
        'source',
        'payment_id',
        'amount',
        'recorded_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isCurrent(): bool
    {
        return $this->started_at->isPast() && $this->expires_at->isFuture();
    }
}
