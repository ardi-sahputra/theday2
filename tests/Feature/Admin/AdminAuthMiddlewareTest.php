<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_user_guard_cannot_access_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_can_access(): void
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        // Dashboard route is added in Phase 4; this test will be unskipped then.
        $this->markTestSkipped('Dashboard route added in Phase 4');
    }
}
