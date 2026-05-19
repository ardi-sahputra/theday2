<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CoupleLinkFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleLink extends Model
{
    /** @use HasFactory<CoupleLinkFactory> */
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE  = 'active';
    public const STATUS_REVOKED = 'revoked';

    public const INVITE_TTL_DAYS = 7;

    protected $fillable = [
        'owner_id',
        'partner_id',
        'invited_email',
        'token_hash',
        'status',
        'invited_at',
        'linked_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'linked_at'  => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->invited_at->addDays(self::INVITE_TTL_DAYS)->isPast();
    }
}
