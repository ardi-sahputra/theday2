<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Gift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SweepExpiredGifts extends Command
{
    protected $signature = 'gift:sweep-expired';
    protected $description = 'Mark abandoned and past-expiry gifts as expired.';

    public function handle(): int
    {
        $abandoned = Gift::abandonedAwaitingPayment()->update(['status' => 'expired']);
        $expired   = Gift::expiredSweep()->update(['status' => 'expired']);

        Log::info('gift.sweep', ['abandoned' => $abandoned, 'expired' => $expired]);

        $this->info("Swept gifts — abandoned: {$abandoned}, expired: {$expired}");
        return self::SUCCESS;
    }
}
