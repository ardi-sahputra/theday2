<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gift extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'sender_user_id',
        'plan_id',
        'recipient_email',
        'delivery_mode',
        'source',
        'duration_days',
        'amount',
        'message',
        'status',
        'claimed_by_user_id',
        'claimed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'duration_days' => 'integer',
            'claimed_at'    => 'datetime',
            'expires_at'    => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeClaimable(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    public function scopeExpiredSweep(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where('expires_at', '<', now());
    }

    public function scopeAbandonedAwaitingPayment(Builder $query): Builder
    {
        return $query->where('status', 'awaiting_payment')->where('created_at', '<', now()->subHours(24));
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function isClaimable(): bool
    {
        return $this->status === 'pending' && $this->expires_at?->isFuture();
    }

    public function monthsFromDuration(): int
    {
        return (int) ceil($this->duration_days / 30);
    }
}
