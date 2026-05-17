<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name'     => 'Test Admin',
            'email'    => 'admin@test.local',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_guest_cannot_access_discounts_index(): void
    {
        $this->get('/admin/discounts')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_discounts_index(): void
    {
        Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/discounts')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Admin/Discounts/Index'));
    }

    public function test_admin_can_create_discount(): void
    {
        $premium = Plan::factory()->premium()->create();

        $payload = [
            'plan_id'   => $premium->id,
            'label'     => 'Promo Akhir Tahun',
            'percent'   => 20,
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at'   => now()->addDays(7)->toDateTimeString(),
        ];

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', $payload)
            ->assertRedirect('/admin/discounts')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('plan_discounts', [
            'plan_id' => $premium->id,
            'label'   => 'Promo Akhir Tahun',
            'percent' => 20,
        ]);
    }

    public function test_cannot_create_discount_for_free_plan(): void
    {
        $free = Plan::factory()->free()->create();

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', [
                'plan_id'   => $free->id,
                'label'     => 'Test',
                'percent'   => 20,
                'starts_at' => now()->addDay()->toDateTimeString(),
                'ends_at'   => now()->addDays(7)->toDateTimeString(),
            ])
            ->assertForbidden();
    }

    public function test_percent_must_be_between_1_and_99(): void
    {
        $premium = Plan::factory()->premium()->create();
        $admin = $this->admin();
        $base = [
            'plan_id'   => $premium->id,
            'label'     => 'Test',
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at'   => now()->addDays(7)->toDateTimeString(),
        ];

        $this->actingAs($admin, 'admin')
            ->post('/admin/discounts', array_merge($base, ['percent' => 0]))
            ->assertSessionHasErrors('percent');

        $this->actingAs($admin, 'admin')
            ->post('/admin/discounts', array_merge($base, ['percent' => 100]))
            ->assertSessionHasErrors('percent');
    }

    public function test_ends_at_must_be_after_starts_at(): void
    {
        $premium = Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', [
                'plan_id'   => $premium->id,
                'label'     => 'Test',
                'percent'   => 20,
                'starts_at' => now()->addDays(7)->toDateTimeString(),
                'ends_at'   => now()->addDay()->toDateTimeString(),
            ])
            ->assertSessionHasErrors('ends_at');
    }

    public function test_overlap_with_existing_discount_rejected(): void
    {
        $premium = Plan::factory()->premium()->create();
        PlanDiscount::factory()->create([
            'plan_id'   => $premium->id,
            'label'     => 'Existing Promo',
            'starts_at' => now()->addDays(5),
            'ends_at'   => now()->addDays(15),
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', [
                'plan_id'   => $premium->id,
                'label'     => 'New Promo',
                'percent'   => 30,
                'starts_at' => now()->addDays(10)->toDateTimeString(),
                'ends_at'   => now()->addDays(20)->toDateTimeString(),
            ])
            ->assertSessionHasErrors('starts_at');
    }

    public function test_admin_can_update_discount(): void
    {
        $premium = Plan::factory()->premium()->create();
        $discount = PlanDiscount::factory()->upcoming()->create([
            'plan_id' => $premium->id,
            'percent' => 20,
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/discounts/{$discount->id}", [
                'plan_id'   => $premium->id,
                'label'     => $discount->label,
                'percent'   => 30,
                'starts_at' => $discount->starts_at->toDateTimeString(),
                'ends_at'   => $discount->ends_at->toDateTimeString(),
            ])
            ->assertRedirect('/admin/discounts');

        $this->assertSame(30, $discount->fresh()->percent);
    }

    public function test_cannot_delete_active_discount(): void
    {
        $premium = Plan::factory()->premium()->create();
        $active = PlanDiscount::factory()->active()->create(['plan_id' => $premium->id]);

        $this->actingAs($this->admin(), 'admin')
            ->delete("/admin/discounts/{$active->id}")
            ->assertSessionHasErrors('discount');

        $this->assertDatabaseHas('plan_discounts', ['id' => $active->id]);
    }

    public function test_can_delete_upcoming_discount(): void
    {
        $premium = Plan::factory()->premium()->create();
        $upcoming = PlanDiscount::factory()->upcoming()->create(['plan_id' => $premium->id]);

        $this->actingAs($this->admin(), 'admin')
            ->delete("/admin/discounts/{$upcoming->id}")
            ->assertRedirect('/admin/discounts');

        $this->assertDatabaseMissing('plan_discounts', ['id' => $upcoming->id]);
    }

    public function test_can_delete_ended_discount(): void
    {
        $premium = Plan::factory()->premium()->create();
        $ended = PlanDiscount::factory()->ended()->create(['plan_id' => $premium->id]);

        $this->actingAs($this->admin(), 'admin')
            ->delete("/admin/discounts/{$ended->id}")
            ->assertRedirect('/admin/discounts');

        $this->assertDatabaseMissing('plan_discounts', ['id' => $ended->id]);
    }
}
