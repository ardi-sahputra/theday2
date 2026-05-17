<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationCategory: string
{
    case Guest      = 'guest';
    case Payment    = 'payment';
    case Gift       = 'gift';
    case Reminder   = 'reminder';
    case Onboarding = 'onboarding';
    case Engagement = 'engagement';
    case System     = 'system';

    public function preferenceColumn(): string
    {
        return $this->value . '_enabled';
    }

    public function cooldownDays(): ?int
    {
        return match ($this) {
            self::Onboarding => config('notifications.cooldown.onboarding_days'),
            self::Engagement => config('notifications.cooldown.engagement_days'),
            default          => null,
        };
    }
}
