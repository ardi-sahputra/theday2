<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class R2Check extends Command
{
    protected $signature = 'r2:check';
    protected $description = 'Verify R2 (uploads disk) connectivity: write, url, read, delete';

    public function handle(): int
    {
        $diskName = config('filesystems.uploads');
        $this->info("Uploads disk: {$diskName}");

        try {
            $disk = Storage::disk($diskName);
            $key  = 'healthcheck/'.Str::uuid().'.txt';

            $disk->put($key, 'ok '.now()->toIso8601String());
            $this->info("✓ write OK: {$key}");

            $url = $disk->url($key);
            $this->info("✓ url: {$url}");

            if (! $disk->exists($key)) {
                $this->error('✗ read FAIL: object not found after write');
                return self::FAILURE;
            }
            $this->info('✓ read OK (exists)');

            $disk->delete($key);
            $this->info('✓ delete OK');

            $this->info('R2 OK');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('R2 CHECK FAILED: '.$e->getMessage());
            $this->warn('If region/signature error: try R2_DEFAULT_REGION=us-east-1');
            return self::FAILURE;
        }
    }
}
