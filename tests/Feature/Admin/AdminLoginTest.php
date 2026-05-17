<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Admin/Auth/Login'));
    }

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $admin = Admin::create([
            'name'     => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        $this->post('/admin/login', [
            'email' => 'a@a.com', 'password' => 'password123',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_wrong_password_fails(): void
    {
        Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        $this->post('/admin/login', [
            'email' => 'a@a.com', 'password' => 'wrong-pw-here',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_inactive_admin_cannot_login(): void
    {
        Admin::create([
            'name'     => 'A', 'email' => 'a@a.com',
            'password' => Hash::make('password123'),
            'is_active'=> false,
        ]);

        $this->post('/admin/login', [
            'email' => 'a@a.com', 'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_logout(): void
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest('admin');
    }
}
