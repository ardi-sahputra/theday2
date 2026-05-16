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
}
