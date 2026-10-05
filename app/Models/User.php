<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if (! $user->isForceDeleting()) {
                $user->email = null;
                  $user->member_code = null;
                $user->saveQuietly();
            }
        });
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'account_status',
        'rejection_reason',
        'member_code',
        'payment_id',
        'payment_status',
        'payment_amount',
    ];

    public function getFormattedMemberIdAttribute(): string
    {
        return $this->member_code ?: ('SSAM' . sprintf('%04d', $this->id));
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->relationLoaded('memberProfile') || $this->memberProfile) {
            $profile = $this->memberProfile;
            if ($profile && ($profile->first_name || $profile->last_name)) {
                $fullName = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
                    $profile->first_name,
                    $profile->middle_name,
                    $profile->last_name,
                ]))));
                if (!empty($fullName)) {
                    return $fullName;
                }
            }
        }
        return $this->name ?: '';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function awardApplications()
    {
        return $this->hasMany(AwardApplication::class);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function registeredEvents()
    {
        return $this->belongsToMany(Event::class, 'event_registrations', 'user_id', 'event_id');
    }

    /**
     * Scope to only fetch pure community members (excludes Administrator and Sub-Admin)
     */
    public function scopeOnlyMembers($query)
    {
        return $query->role('Member')->whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['Administrator', 'Sub-Admin']);
        });
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'payable')->latest('id');
    }
}
