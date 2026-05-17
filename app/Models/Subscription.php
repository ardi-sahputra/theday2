<?php

// app/Models/Subscription.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'expires_at',
        'grace_until',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'   => 'datetime',
            'expires_at'  => 'datetime',
            'grace_until' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(InvitationAddon::class);
    }

    public function activeAddons(): HasMany
    {
        return $this->hasMany(InvitationAddon::class)
            ->whereNotNull('paid_at')
            ->where('expires_at', '>', now());
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeByUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function isPremium(): bool
    {
        return $this->plan?->slug === 'premium';
    }

    /** Subscription masih dalam masa aktif premium */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /** Subscription expired tapi masih dalam grace period */
    public function isInGracePeriod(): bool
    {
        return $this->status === 'grace'
            && $this->grace_until !== null
            && $this->grace_until->isFuture();
    }

    /** Grace period sudah habis */
    public function isFullyExpired(): bool
    {
        if (! $this->isPremium()) {
            return false;
        }

        return $this->status === 'expired'
            || ($this->grace_until !== null && $this->grace_until->isPast() && $this->status !== 'active');
    }

    /** User berhak atas fitur premium (hanya saat active) */
    public function hasPremiumAccess(): bool
    {
        return $this->isPremium() && $this->isActive();
    }

    /** Free plan tidak pernah expired */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->status === 'expired' || $this->expires_at->isPast();
    }

    /** Sisa hari grace period */
    public function graceDaysRemaining(): int
    {
        if (! $this->isInGracePeriod() || $this->grace_until === null) {
            return 0;
        }

        return (int) now()->diffInDays($this->grace_until, absolute: false);
    }

    /** Kuota undangan published yang diperbolehkan */
    public function invitationQuota(): int
    {
        if (! $this->isPremium()) {
            return 1;
        }

        if (! $this->isActive()) {
            return 0;
        }

        return 2 + (int) $this->activeAddons()->sum('quantity');
    }

    public function canPublishInvitation(): bool
    {
        $quota     = $this->invitationQuota();
        $published = $this->user->invitations()->where('status', 'published')->count();

        return $published < $quota;
    }

    /** Sisa hari sampai expires_at */
    public function daysRemaining(): int
    {
        if ($this->expires_at === null || $this->isExpired()) {
            return 0;
        }

        return (int) now()->diffInDays($this->expires_at, absolute: false);
    }
}
