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
        'rejection_reason',
        'membership_status',
        'facebook',
        'instagram',
        'youtube',
        'linkedin',
        'approved_at',
        'renewal_reminder_sent_at',
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
        'renewal_reminder_sent_at' => 'datetime',
    ];

    /**
     * Set before saving to label the membership history row (not stored on the business).
     */
    public ?string $membershipSource = null;

    protected static function booted()
    {
        // Every new membership start (approval, renewal payment, admin activation or date edit) is kept as history
        static::saved(function (Business $business) {
            if (!$business->approved_at || (!$business->wasRecentlyCreated && !$business->wasChanged('approved_at'))) {
                return;
            }

            $paid = $business->wasChanged('payment_id') && $business->payment_id;
            $firstApproval = $business->wasRecentlyCreated || $business->getOriginal('approved_at') === null;

            $business->memberships()->create([
                'started_at' => $business->approved_at,
                'expires_at' => $business->approved_at->copy()->addYear(),
                'source' => $business->membershipSource ?? ($paid ? 'payment' : ($firstApproval && !$business->memberships()->exists() ? 'approval' : (auth('business')->check() ? 'free_renewal' : 'admin'))),
                'payment_id' => ($paid || $firstApproval) && $business->payment_status === 'paid' ? $business->payment_id : null,
                'amount' => ($paid || $firstApproval) && $business->payment_status === 'paid' ? $business->payment_amount : null,
                'recorded_by' => auth('web')->id(),
            ]);
            $business->membershipSource = null;
        });
    }

    /**
     * Listings shown to the public: approved, membership active and its year not yet over.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'approved')
                     ->where('membership_status', 'active')
                     ->where('approved_at', '>', now()->subYear());
    }

    public function isPubliclyListed(): bool
    {
        return $this->status === 'approved'
            && $this->membership_status === 'active'
            && $this->approved_at
            && $this->approved_at->copy()->addYear()->isFuture();
    }

    public static function renewalFee(): float
    {
        return (float) Setting::get('business_renewal_fee', Setting::get('business_registration_fee', '500'));
    }

    /**
     * The 1-year membership has ended. Free renewal: the next year starts automatically.
     * Paid renewal: the membership is closed until the owner pays.
     *
     * @return string|null 'renewed', 'closed', or null when the membership has not expired
     */
    public function applyMembershipExpiry(): ?string
    {
        if ($this->status !== 'approved' || !$this->approved_at || $this->approved_at->copy()->addYear()->isFuture()) {
            return null;
        }

        if (self::renewalFee() <= 0) {
            // Continue from the old expiry date (skipping any whole years missed)
            $start = $this->approved_at->copy()->addYear();
            while ($start->copy()->addYear()->lte(now())) {
                $start->addYear();
            }

            $this->membershipSource = 'auto_free_renewal';
            $this->approved_at = $start;
            $this->membership_status = 'active';
            $this->save();

            return 'renewed';
        }

        if ($this->membership_status !== 'inactive') {
            $this->membership_status = 'inactive';
            $this->save();
        }

        return 'closed';
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

    public function memberships()
    {
        return $this->hasMany(BusinessMembership::class)->latest('started_at');
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

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'payable')->latest('id');
    }
}
