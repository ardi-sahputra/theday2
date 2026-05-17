<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckEngagementCommand extends Command
{
    protected $signature = 'notifications:check-engagement';

    protected $description = 'Engagement & re-activation notifications (weekly, cooldown 7 days)';

    public function handle(NotificationPublisher $publisher): int
    {
        $cooldown = (int) config('notifications.cooldown.engagement_days', 7);
        $cutoff   = Carbon::now()->subDays(14);

        // Inactive users: have never logged in OR last activity > 14 days ago.
        // Use `updated_at` as a coarse "last seen" proxy when no last_login_at column exists.
        $column = $this->resolveLastSeenColumn();

        User::query()
            ->where($column, '<', $cutoff)
            ->chunkById(200, function ($users) use ($publisher, $cooldown) {
                foreach ($users as $user) {
                    $publisher->publish(
                        user: $user,
                        type: NotificationType::EngagementInactive,
                        payload: [],
                        groupKey: 'engagement:inactive',
                        actionUrl: '/dashboard',
                        cooldownDays: $cooldown,
                    );
                }
            });

        return self::SUCCESS;
    }

    private function resolveLastSeenColumn(): string
    {
        // Prefer last_login_at if the column exists, else fall back to updated_at.
        $connection = (new User())->getConnection();
        if ($connection->getSchemaBuilder()->hasColumn('users', 'last_login_at')) {
            return 'last_login_at';
        }

        return 'updated_at';
    }
}
