<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_summary_returns_greeting_payload(): void
    {
        $user = User::factory()->create(['name' => 'Ardi']);
        $auth = ['Authorization' => 'Bearer ' . $user->createToken('d')->plainTextToken];

        $this->getJson('/api/home/summary', $auth)
            ->assertOk()
            ->assertJsonStructure(['greeting_name', 'wedding_date']);
    }

    public function test_home_summary_requires_auth(): void
    {
        $this->getJson('/api/home/summary')->assertStatus(401);
    }
}
