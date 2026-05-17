<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationBroadcast extends Model
{
    use HasFactory;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_SENT      = 'sent';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'admin_id',
        'title',
        'body',
        'action_url',
        'category',
        'target_type',
        'target_user_ids',
        'scheduled_at',
        'sent_at',
        'cancelled_at',
        'recipient_count',
    ];

    protected function casts(): array
    {
        return [
            'category'        => NotificationCategory::class,
            'target_user_ids' => 'array',
            'scheduled_at'    => 'datetime',
            'sent_at'         => 'datetime',
            'cancelled_at'    => 'datetime',
            'recipient_count' => 'integer',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function status(): string
    {
        if ($this->cancelled_at !== null) {
            return self::STATUS_CANCELLED;
        }
        if ($this->sent_at !== null) {
            return self::STATUS_SENT;
        }
        if ($this->scheduled_at === null) {
            return self::STATUS_DRAFT;
        }

        return $this->scheduled_at->isFuture() ? self::STATUS_SCHEDULED : self::STATUS_PENDING;
    }

    public function isEditable(): bool
    {
        return $this->sent_at === null && $this->cancelled_at === null;
    }

    public function isCancellable(): bool
    {
        return $this->sent_at === null && $this->cancelled_at === null
            && in_array($this->status(), [self::STATUS_SCHEDULED, self::STATUS_PENDING], true);
    }
}
