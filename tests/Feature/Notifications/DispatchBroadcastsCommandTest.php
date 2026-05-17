<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Admin;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatchBroadcastsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_due_broadcast_to_all_users(): void
    {
        $admin = Admin::factory()->create();
        User::factory()->count(3)->create();

        $bcast = NotificationBroadcast::create([
            'admin_id'     => $admin->id,
            'title'        => 'Hi',
            'category'     => 'system',
            'target_type'  => 'all',
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('notifications:dispatch-broadcasts')->assertSuccessful();

        $this->assertNotNull($bcast->fresh()->sent_at);
        $this->assertSame(3, $bcast->fresh()->recipient_count);
        $this->assertSame(3, UserNotification::where('type', 'system.broadcast')->count());
    }

    public function test_dispatches_only_to_target_users(): void
    {
        $admin = Admin::factory()->create();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        User::factory()->create();

        NotificationBroadcast::create([
            'admin_id'        => $admin->id,
            'title'           => 'Personal',
            'category'        => 'system',
            'target_type'     => 'users',
            'target_user_ids' => [$u1->id, $u2->id],
            'scheduled_at'    => now()->subMinute(),
        ]);

        $this->artisan('notifications:dispatch-broadcasts');

        $this->assertSame(2, UserNotification::where('type', 'system.broadcast')->count());
    }

    public function test_skips_cancelled_broadcast(): void
    {
        $admin = Admin::factory()->create();
        User::factory()->create();

        NotificationBroadcast::create([
            'admin_id'     => $admin->id,
            'title'        => 'X',
            'category'     => 'system',
            'target_type'  => 'all',
            'scheduled_at' => now()->subMinute(),
            'cancelled_at' => now(),
        ]);

        $this->artisan('notifications:dispatch-broadcasts');

        $this->assertSame(0, UserNotification::count());
    }

    public function test_idempotent_when_run_twice(): void
    {
        $admin = Admin::factory()->create();
        User::factory()->count(2)->create();

        NotificationBroadcast::create([
            'admin_id'     => $admin->id,
            'title'        => 'Y',
            'category'     => 'system',
            'target_type'  => 'all',
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('notifications:dispatch-broadcasts');
        $this->artisan('notifications:dispatch-broadcasts');

        $this->assertSame(2, UserNotification::count());
    }
}
