<?php

// app/Models/User.php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar_url',
        'google_id',
        'onboarding_completed_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'password'                => 'hashed',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    // ─── Model Events ─────────────────────────────────────────────

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            // Soft-delete semua undangan milik user ini
            $user->invitations()->each(fn ($inv) => $inv->delete());
        });
    }

    // ─── Relationships ────────────────────────────────────────────

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function weddingPlan(): HasOne
    {
        return $this->hasOne(WeddingPlan::class);
    }

    public function coupleProfile(): HasOne
    {
        return $this->hasOne(CoupleProfile::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function invitationAddons(): HasMany
    {
        return $this->hasMany(InvitationAddon::class);
    }

    public function sentGifts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Gift::class, 'sender_user_id');
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latestOfMany();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function currentPlan(): ?Plan
    {
        return $this->activeSubscription?->plan;
    }

    public function isPremium(): bool
    {
        return $this->activeSubscription?->plan->slug === 'premium';
    }

    public function isFree(): bool
    {
        return ! $this->isPremium();
    }

    public function planSlug(): string
    {
        return $this->isPremium() ? 'premium' : 'free';
    }

    public function invitationQuota(): int
    {
        $subscription = $this->activeSubscription;

        if (! $subscription || ! $subscription->isPremium()) {
            return 1;
        }

        return $subscription->invitationQuota();
    }

    public function canPublishInvitation(): bool
    {
        $quota = $this->invitationQuota();

        if ($quota === 0) {
            return false;
        }

        $published = $this->invitations()->where('status', 'published')->count();

        return $published < $quota;
    }
}
