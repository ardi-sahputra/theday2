<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name'     => 'Admin Test',
            'email'    => 'admin-' . uniqid() . '@test.local',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_guest_cannot_view_plans_index(): void
    {
        $this->get('/admin/plans')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_plans_index(): void
    {
        Plan::factory()->free()->create();
        Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/plans')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Plans/Index')
                ->has('plans', 2)
            );
    }

    public function test_admin_can_view_premium_edit_page(): void
    {
        $premium = Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->get("/admin/plans/{$premium->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Plans/Edit')
                ->where('plan.slug', 'premium')
            );
    }

    public function test_admin_cannot_view_free_edit_page(): void
    {
        $free = Plan::factory()->free()->create();

        $this->actingAs($this->admin(), 'admin')
            ->get("/admin/plans/{$free->id}/edit")
            ->assertForbidden();
    }

    public function test_admin_can_update_premium_plan(): void
    {
        $premium = Plan::factory()->premium()->create([
            'price'         => 35000,
            'duration_days' => 90,
        ]);

        $payload = [
            'name'               => 'Premium',
            'price'              => 49000,
            'duration_days'      => 365,
            'max_invitations'    => 2,
            'max_gallery_photos' => 9999,
            'custom_music'       => true,
            'remove_watermark'   => true,
            'custom_domain'      => true,
            'analytics_access'   => true,
            'features'           => ['Undangan tidak terbatas', 'Tanpa watermark'],
            'is_active'          => true,
        ];

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/plans/{$premium->id}", $payload)
            ->assertRedirect('/admin/plans')
            ->assertSessionHas('success');

        $premium->refresh();
        $this->assertSame(49000, (int) $premium->price);
        $this->assertSame(365, $premium->duration_days);
    }

    public function test_admin_cannot_update_free_plan(): void
    {
        $free = Plan::factory()->free()->create();

        $payload = [
            'name'               => 'Free',
            'price'              => 99999,
            'duration_days'      => 30,
            'max_invitations'    => 1,
            'max_gallery_photos' => 5,
            'custom_music'       => false,
            'remove_watermark'   => false,
            'custom_domain'      => false,
            'analytics_access'   => false,
            'features'           => ['x'],
            'is_active'          => true,
        ];

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/plans/{$free->id}", $payload)
            ->assertForbidden();

        $this->assertSame(0, (int) $free->fresh()->price);
    }

    public function test_validation_rejects_negative_price(): void
    {
        $premium = Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/plans/{$premium->id}", [
                'name'               => 'Premium',
                'price'              => -1,
                'duration_days'      => 365,
                'max_invitations'    => 2,
                'max_gallery_photos' => 9999,
                'custom_music'       => true,
                'remove_watermark'   => true,
                'custom_domain'      => true,
                'analytics_access'   => true,
                'features'           => ['x'],
                'is_active'          => true,
            ])
            ->assertSessionHasErrors('price');
    }

    public function test_validation_rejects_empty_features(): void
    {
        $premium = Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/plans/{$premium->id}", [
                'name'               => 'Premium',
                'price'              => 49000,
                'duration_days'      => 365,
                'max_invitations'    => 2,
                'max_gallery_photos' => 9999,
                'custom_music'       => true,
                'remove_watermark'   => true,
                'custom_domain'      => true,
                'analytics_access'   => true,
                'features'           => [],
                'is_active'          => true,
            ])
            ->assertSessionHasErrors('features');
    }
}
