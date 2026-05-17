<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CleanupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_unread_older_than_ttl(): void
    {
        config(['notifications.cleanup.unread_ttl_days' => 90, 'notifications.cleanup.read_ttl_days' => 180]);
        Carbon::setTestNow('2026-05-17 00:00:00');
        $user = User::factory()->create();

        UserNotification::factory()->for($user)->create([
            'read_at'    => null,
            'created_at' => Carbon::parse('2026-02-01'),
            'updated_at' => Carbon::parse('2026-02-01'),
        ]);
        UserNotification::factory()->for($user)->create([
            'read_at'    => null,
            'created_at' => Carbon::parse('2026-04-01'),
            'updated_at' => Carbon::parse('2026-04-01'),
        ]);

        $this->artisan('notifications:cleanup')->assertSuccessful();

        $this->assertSame(1, UserNotification::count());
    }

    public function test_deletes_read_older_than_read_ttl(): void
    {
        config(['notifications.cleanup.unread_ttl_days' => 90, 'notifications.cleanup.read_ttl_days' => 180]);
        Carbon::setTestNow('2026-05-17 00:00:00');
        $user = User::factory()->create();

        UserNotification::factory()->for($user)->create([
            'read_at'    => Carbon::parse('2025-10-01'),
            'created_at' => Carbon::parse('2025-10-01'),
            'updated_at' => Carbon::parse('2025-10-01'),
        ]);
        UserNotification::factory()->for($user)->create([
            'read_at'    => Carbon::parse('2026-04-01'),
            'created_at' => Carbon::parse('2026-04-01'),
            'updated_at' => Carbon::parse('2026-04-01'),
        ]);

        $this->artisan('notifications:cleanup');

        $this->assertSame(1, UserNotification::count());
    }
}
