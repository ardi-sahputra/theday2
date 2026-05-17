<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\ChecklistTask;
use App\Models\User;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckChecklistDueCommand extends Command
{
    protected $signature = 'notifications:check-checklist-due';

    protected $description = 'Publish checklist task due-soon notifications';

    public function handle(NotificationPublisher $publisher): int
    {
        $today    = Carbon::today();
        $upcoming = $today->copy()->addDays(7);

        $counts = ChecklistTask::query()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $upcoming])
            ->whereNull('completed_at')
            ->with('weddingPlan')
            ->get()
            ->groupBy(fn ($t) => optional($t->weddingPlan)->user_id);

        foreach ($counts as $userId => $tasks) {
            if (empty($userId)) {
                continue;
            }
            $user = User::find($userId);
            if ($user === null) {
                continue;
            }

            $publisher->publish(
                user: $user,
                type: NotificationType::ChecklistTaskDueSoon,
                payload: ['count' => $tasks->count()],
                groupKey: 'checklist:' . $today->toDateString(),
                actionUrl: '/dashboard/checklist',
            );
        }

        return self::SUCCESS;
    }
}
