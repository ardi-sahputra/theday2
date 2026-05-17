<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchBroadcastsCommand extends Command
{
    protected $signature = 'notifications:dispatch-broadcasts';

    protected $description = 'Dispatch due admin broadcast notifications';

    public function handle(NotificationPublisher $publisher): int
    {
        $broadcastIds = NotificationBroadcast::query()
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->where('scheduled_at', '<=', now())
            ->pluck('id');

        foreach ($broadcastIds as $id) {
            DB::transaction(function () use ($publisher, $id) {
                /** @var NotificationBroadcast|null $bcast */
                $bcast = NotificationBroadcast::query()
                    ->whereKey($id)
                    ->whereNull('sent_at')
                    ->whereNull('cancelled_at')
                    ->lockForUpdate()
                    ->first();

                if ($bcast === null) {
                    return;
                }

                $sentCount = 0;
                $userQuery = $bcast->target_type === 'all'
                    ? User::query()
                    : User::query()->whereIn('id', $bcast->target_user_ids ?? []);

                $userQuery->chunkById(500, function ($users) use ($publisher, $bcast, &$sentCount) {
                    foreach ($users as $user) {
                        try {
                            $notif = $publisher->publish(
                                user: $user,
                                type: NotificationType::SystemBroadcast,
                                payload: [
                                    'broadcast_id' => $bcast->id,
                                    'title_raw'    => $bcast->title,
                                    'body_raw'     => $bcast->body,
                                ],
                                actionUrl: $bcast->action_url,
                            );
                            if ($notif !== null) {
                                $sentCount++;
                            }
                        } catch (Throwable $e) {
                            Log::warning('Broadcast publish failed', [
                                'broadcast_id' => $bcast->id,
                                'user_id'      => $user->id,
                                'error'        => $e->getMessage(),
                            ]);
                        }
                    }
                });

                $bcast->update([
                    'sent_at'         => now(),
                    'recipient_count' => $sentCount,
                ]);
            });
        }

        return self::SUCCESS;
    }
}
