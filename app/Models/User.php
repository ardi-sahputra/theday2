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
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'locale',
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

    /**
     * Locale used for notifications/emails (HasLocalePreference).
     * Falls back to app default when not set.
     */
    public function preferredLocale(): ?string
    {
        return in_array($this->locale, ['id', 'en'], true)
            ? $this->locale
            : config('app.locale');
    }

    /**
     * Name to use as the payment customer identity (e.g. Mayar invoice).
     * Prefers the couple's names over the raw account name, because the
     * account name is often a Google display name unrelated to the wedding.
     */
    public function customerDisplayName(): string
    {
        $profile = $this->coupleProfile;
        if ($profile && ($profile->groom_name || $profile->bride_name)) {
            return $this->coupleLabel($profile->groom_name, $profile->bride_name);
        }

        $details = $this->invitations()
            ->whereHas('details')
            ->with('details')
            ->latest()
            ->first()?->details;

        if ($details && ($details->groom_name || $details->bride_name)) {
            return $this->coupleLabel($details->groom_name, $details->bride_name);
        }

        return $this->name;
    }

    private function coupleLabel(?string $groom, ?string $bride): string
    {
        $groom = trim((string) $groom);
        $bride = trim((string) $bride);

        return match (true) {
            $groom !== '' && $bride !== '' => "{$groom} & {$bride}",
            $groom !== ''                  => $groom,
            $bride !== ''                  => $bride,
            default                        => $this->name,
        };
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

    public function sentGifts(): HasMany
    {
        return $this->hasMany(Gift::class, 'sender_user_id');
    }

    /** CoupleLink where this user is the owner (inviter). */
    public function coupleLink(): HasOne
    {
        return $this->hasOne(CoupleLink::class, 'owner_id');
    }

    /** CoupleLink where this user is the partner (invitee), active only. */
    public function partnerOf(): HasOne
    {
        return $this->hasOne(CoupleLink::class, 'partner_id')
            ->where('status', CoupleLink::STATUS_ACTIVE);
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latestOfMany();
    }

    /**
     * Returns the user whose billing context applies.
     * When the authenticated user is a partner in an active CoupleLink,
     * this returns the owner so subscription/quota methods are inherited.
     */
    private function billingSubject(): self
    {
        $effective = \App\Support\EffectiveUser::resolve();
        if ($effective !== null && $effective->id !== $this->id) {
            return $effective;
        }
        return $this;
    }

    /**
     * The active subscription for the effective (billing) user.
     * Partners transparently inherit the owner's subscription.
     */
    public function effectiveActiveSubscription(): ?Subscription
    {
        return $this->billingSubject()->activeSubscription;
    }

    public function hasActiveSubscription(): bool
    {
        return $this->effectiveActiveSubscription() !== null;
    }

    public function currentPlan(): ?Plan
    {
        return $this->effectiveActiveSubscription()?->plan;
    }

    public function isPremium(): bool
    {
        return $this->effectiveActiveSubscription()?->plan->slug === 'premium';
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
        $subscription = $this->effectiveActiveSubscription();

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

        $published = $this->billingSubject()->invitations()->where('status', 'published')->count();

        return $published < $quota;
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(\App\Models\UserNotification::class);
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(\App\Models\NotificationPreference::class);
    }
}
