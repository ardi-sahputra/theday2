<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_kpi(): void
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);
        User::factory()->count(4)->create();

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Dashboard')
                ->where('kpi.totalUsers', 4)
                ->has('signupTrend')
                ->has('recentUsers')
                ->has('recentPayments')
            );
    }
}
