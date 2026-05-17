<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminArticleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_articles_index(): void
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/articles')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Admin/Articles/Index'));
    }

    public function test_guest_redirected(): void
    {
        $this->get('/admin/articles')->assertRedirect('/admin/login');
    }
}
