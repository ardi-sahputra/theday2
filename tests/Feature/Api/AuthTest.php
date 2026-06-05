<?php

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Plan::create([
            'name'               => 'Free',
            'slug'               => 'free',
            'price'              => 0,
            'duration_days'      => 0,
            'max_invitations'    => 3,
            'max_gallery_photos' => 10,
            'custom_music'       => false,
            'remove_watermark'   => false,
            'custom_domain'      => false,
            'analytics_access'   => false,
            'is_active'          => true,
            'sort_order'         => 0,
        ]);
    }

    public function test_login_returns_token_and_user(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'pixel-test',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_rejects_bad_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'pixel-test',
        ])->assertStatus(422);
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ardi',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'device_name' => 'pixel-test',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'email']]);
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_me_requires_token(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_user_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('pixel-test')->plainTextToken;

        $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('pixel-test')->plainTextToken;

        $this->postJson('/api/auth/logout', [], ['Authorization' => "Bearer {$token}"])
            ->assertOk();

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
