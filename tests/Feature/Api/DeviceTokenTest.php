<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_a_device_token_upserts_by_token(): void
    {
        $user = User::factory()->create();
        $auth = ['Authorization' => 'Bearer ' . $user->createToken('d')->plainTextToken];

        $this->postJson('/api/devices', ['token' => 'fcm-abc', 'platform' => 'android'], $auth)
            ->assertOk();
        // Same token again — must not duplicate.
        $this->postJson('/api/devices', ['token' => 'fcm-abc', 'platform' => 'android'], $auth)
            ->assertOk();

        $this->assertDatabaseCount('device_tokens', 1);
        $this->assertDatabaseHas('device_tokens', ['user_id' => $user->id, 'token' => 'fcm-abc']);
    }

    public function test_device_registration_requires_auth(): void
    {
        $this->postJson('/api/devices', ['token' => 'x', 'platform' => 'android'])
            ->assertStatus(401);
    }
}
