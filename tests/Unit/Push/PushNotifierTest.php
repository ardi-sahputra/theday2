<?php

namespace Tests\Unit\Push;

use App\Models\DeviceToken;
use App\Models\User;
use App\Services\Push\FcmClient;
use App\Services\Push\PushNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PushNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_to_each_device_token_with_route(): void
    {
        $user = User::factory()->create();
        DeviceToken::create(['user_id' => $user->id, 'token' => 'tok-1', 'platform' => 'android']);
        DeviceToken::create(['user_id' => $user->id, 'token' => 'tok-2', 'platform' => 'android']);

        $fcm = Mockery::mock(FcmClient::class);
        $fcm->shouldReceive('send')
            ->once()
            ->with(['tok-1', 'tok-2'], 'Judul', 'Isi', ['route' => '/planner']);

        $notifier = new PushNotifier($fcm);
        $notifier->send($user, 'Judul', 'Isi', '/planner');
    }

    public function test_no_devices_means_no_send(): void
    {
        $user = User::factory()->create();
        $fcm = Mockery::mock(FcmClient::class);
        $fcm->shouldNotReceive('send');

        (new PushNotifier($fcm))->send($user, 'Judul', 'Isi', '/planner');
        $this->assertTrue(true);
    }
}
