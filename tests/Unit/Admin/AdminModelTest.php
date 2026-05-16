<?php

namespace Tests\Unit\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_login_updates_timestamps_and_ip(): void
    {
        $admin = Admin::create([
            'name'     => 'Test Admin',
            'email'    => 'test@admin.com',
            'password' => bcrypt('secret123'),
        ]);

        $admin->recordLogin('1.2.3.4');

        $admin->refresh();
        $this->assertNotNull($admin->last_login_at);
        $this->assertEquals('1.2.3.4', $admin->last_login_ip);
    }

    public function test_admin_uses_uuid_primary_key(): void
    {
        $admin = Admin::create([
            'name'     => 'UUID Test',
            'email'    => 'uuid@admin.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->assertIsString($admin->id);
        $this->assertEquals(36, strlen($admin->id));
    }
}
