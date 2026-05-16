<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function asAdmin()
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);
        return $this->actingAs($admin, 'admin');
    }

    public function test_users_index_renders_paginated(): void
    {
        User::factory()->count(30)->create();

        $this->asAdmin()
            ->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Users/Index')
                ->has('users.data', 25)
                ->where('users.total', 30)
            );
    }

    public function test_users_search_by_name(): void
    {
        User::factory()->create(['name' => 'Ardi Syahputra']);
        User::factory()->create(['name' => 'Sari Dewi']);

        $this->asAdmin()
            ->get('/admin/users?search=Ardi')
            ->assertInertia(fn ($p) => $p
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Ardi Syahputra')
            );
    }

    public function test_user_show_renders_with_relations(): void
    {
        $user = User::factory()->create();

        $this->asAdmin()
            ->get("/admin/users/{$user->id}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Users/Show')
                ->where('user.id', $user->id)
            );
    }

    public function test_grant_premium_creates_subscription(): void
    {
        // Ensure premium plan exists for service to resolve plan_id
        \App\Models\Plan::factory()->premium()->create();

        $user = User::factory()->create();

        $this->asAdmin()
            ->post("/admin/users/{$user->id}/grant-premium", [
                'months' => 3,
                'reason' => 'CS compensation',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status'  => 'active',
        ]);
    }

    public function test_revoke_premium_expires_subscription(): void
    {
        $plan = \App\Models\Plan::factory()->premium()->create();
        $user = User::factory()->create();
        \App\Models\Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'expires_at' => now()->addYear(),
        ]);

        $this->asAdmin()
            ->post("/admin/users/{$user->id}/revoke-premium")
            ->assertRedirect();

        $sub = $user->subscriptions()->first();
        $this->assertTrue($sub->expires_at->lte(now()->addMinute()));
    }
}
