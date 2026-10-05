<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'sponsorship_type_id',
        'user_id',
        'name',
        'contact_person',
        'mobile',
        'email',
        'amount',
        'logo_path',
        'city',
        'address',
        'notes',
        'payment_status',
        'payment_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sponsorshipType()
    {
        return $this->belongsTo(SponsorshipType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'payable')->latest('id');
    }
}
