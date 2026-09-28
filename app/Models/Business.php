<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'area_id',
        'member_id',
        'business_name',
        'owner_name',
        'description',
        'address',
        'phone',
        'whatsapp',
        'email',
        'password',
        'email_verified_at',
        'website',
        'logo_path',
        'gallery_images',
        'status',
        'membership_status',
        'facebook',
        'instagram',
        'youtube',
        'linkedin',
        'approved_at',
        'payment_id',
        'payment_status',
        'payment_amount',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'gallery_images' => 'array',
        'approved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::retrieved(function ($business) {
            if ($business->status === 'approved' && $business->membership_status === 'active' && $business->approved_at && $business->approved_at->addYear()->isPast()) {
                $business->membership_status = 'inactive';
                $business->saveQuietly();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'approved')
                     ->where('membership_status', 'active');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'category_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function paymentLinks()
    {
        return $this->hasMany(BusinessPaymentLink::class)->latest();
    }

    /**
     * A business is due for renewal only once it was previously approved AND
     * that 1-year approval window has now completed. Payment links are only
     * generated for these (a business never approved yet goes through the
     * normal registration flow instead).
     */
    public function isRenewalDue(): bool
    {
        return !is_null($this->approved_at) && now()->greaterThanOrEqualTo($this->approved_at->copy()->addYear());
    }
}
