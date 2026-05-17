<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\WeddingPlan;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckWeddingCountdownCommand extends Command
{
    protected $signature = 'notifications:check-wedding-countdown';

    protected $description = 'Publish wedding countdown milestone notifications (H-30/H-7/H-1)';

    public function handle(NotificationPublisher $publisher): int
    {
        $milestones = [30, 7, 1];
        $today      = Carbon::today();

        foreach ($milestones as $daysLeft) {
            $target = $today->copy()->addDays($daysLeft);

            WeddingPlan::query()
                ->whereDate('event_date', $target->toDateString())
                ->with('user')
                ->chunkById(200, function ($plans) use ($publisher, $daysLeft) {
                    foreach ($plans as $plan) {
                        if (! $plan->user) {
                            continue;
                        }
                        $publisher->publish(
                            user: $plan->user,
                            type: NotificationType::WeddingCountdown,
                            payload: ['days' => $daysLeft],
                            groupKey: 'wedding:countdown:' . $plan->id . ':' . $daysLeft,
                            actionUrl: '/dashboard',
                        );
                    }
                });
        }

        return self::SUCCESS;
    }
}
