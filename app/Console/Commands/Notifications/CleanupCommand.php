<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupCommand extends Command
{
    protected $signature = 'notifications:cleanup';

    protected $description = 'Delete notifications past their TTL';

    public function handle(): int
    {
        $unreadTtl = (int) config('notifications.cleanup.unread_ttl_days', 90);
        $readTtl   = (int) config('notifications.cleanup.read_ttl_days', 180);
        $chunk     = (int) config('notifications.cleanup.chunk_size', 5000);

        $unreadCutoff = Carbon::now()->subDays($unreadTtl);
        $readCutoff   = Carbon::now()->subDays($readTtl);

        $totalDeleted = 0;
        do {
            $deleted = UserNotification::query()
                ->where(function ($q) use ($unreadCutoff, $readCutoff) {
                    $q->where(fn ($q2) => $q2->whereNull('read_at')->where('created_at', '<', $unreadCutoff))
                      ->orWhere(fn ($q2) => $q2->whereNotNull('read_at')->where('read_at', '<', $readCutoff));
                })
                ->limit($chunk)
                ->delete();

            $totalDeleted += $deleted;
            if ($deleted > 0) {
                $this->info("Deleted: {$deleted}");
            }
        } while ($deleted > 0);

        $this->info("Total deleted: {$totalDeleted}");

        return self::SUCCESS;
    }
}
