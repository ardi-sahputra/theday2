<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'guest_enabled',
        'payment_enabled',
        'gift_enabled',
        'reminder_enabled',
        'onboarding_enabled',
        'engagement_enabled',
        'system_enabled',
    ];

    protected function casts(): array
    {
        return [
            'guest_enabled'      => 'boolean',
            'payment_enabled'    => 'boolean',
            'gift_enabled'       => 'boolean',
            'reminder_enabled'   => 'boolean',
            'onboarding_enabled' => 'boolean',
            'engagement_enabled' => 'boolean',
            'system_enabled'     => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enabledFor(NotificationCategory $category): bool
    {
        return (bool) $this->{$category->preferenceColumn()};
    }
}
