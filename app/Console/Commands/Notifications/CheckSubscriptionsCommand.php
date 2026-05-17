<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\Subscription;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckSubscriptionsCommand extends Command
{
    protected $signature = 'notifications:check-subscriptions';

    protected $description = 'Publish expiring/expired subscription notifications';

    public function handle(NotificationPublisher $publisher): int
    {
        $now       = Carbon::now();
        $sevenDays = $now->copy()->addDays(7);

        Subscription::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', $now)
            ->where('expires_at', '<=', $sevenDays)
            ->with('user')
            ->chunkById(200, function ($subs) use ($publisher, $now) {
                foreach ($subs as $sub) {
                    if (! $sub->user) {
                        continue;
                    }
                    $daysLeft = max(1, (int) ceil($now->diffInHours(Carbon::parse($sub->expires_at), false) / 24));
                    $publisher->publish(
                        user: $sub->user,
                        type: NotificationType::SubscriptionExpiringSoon,
                        payload: ['days' => $daysLeft],
                        groupKey: 'subscription:expiring:' . $sub->id,
                        actionUrl: '/dashboard/billing',
                    );
                }
            });

        Subscription::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->with('user')
            ->chunkById(200, function ($subs) use ($publisher) {
                foreach ($subs as $sub) {
                    if (! $sub->user) {
                        continue;
                    }
                    $publisher->publish(
                        user: $sub->user,
                        type: NotificationType::SubscriptionExpired,
                        payload: [],
                        groupKey: 'subscription:expired:' . $sub->id,
                        actionUrl: '/dashboard/billing',
                    );
                }
            });

        return self::SUCCESS;
    }
}
