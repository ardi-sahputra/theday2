<?php

// app/Models/InvitationAddon.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationAddon extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'quantity',
        'price_per_unit',
        'total_price',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at'    => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isActive(): bool
    {
        return $this->paid_at !== null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /** Paid, and either lifetime (expires_at null) or not yet expired. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('paid_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
