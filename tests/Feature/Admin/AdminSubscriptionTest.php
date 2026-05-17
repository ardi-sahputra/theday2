<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function asAdmin()
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);
        return $this->actingAs($admin, 'admin');
    }

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();
    }

    public function test_index_renders_with_subscriptions(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        Subscription::factory()->count(3)->create(['plan_id' => $plan->id]);

        $this->asAdmin()
            ->get('/admin/subscriptions')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Subscriptions/Index')
                ->has('subscriptions.data', 3)
            );
    }

    public function test_extend_adds_one_month(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $sub = Subscription::factory()->create([
            'plan_id'    => $plan->id,
            'expires_at' => '2026-06-01 00:00:00',
            'status'     => 'active',
        ]);

        $this->asAdmin()
            ->post("/admin/subscriptions/{$sub->id}/extend", ['months' => 1])
            ->assertRedirect();

        $sub->refresh();
        $this->assertSame('2026-07-01 00:00:00', $sub->expires_at->toDateTimeString());
    }

    public function test_extend_rejects_invalid_months(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $sub = Subscription::factory()->create(['plan_id' => $plan->id, 'status' => 'active']);
        $admin = $this->asAdmin();

        $admin->post("/admin/subscriptions/{$sub->id}/extend", ['months' => 999])
            ->assertSessionHasErrors('months');

        $admin->post("/admin/subscriptions/{$sub->id}/extend", ['months' => -1])
            ->assertSessionHasErrors('months');
    }

    public function test_cancel_sets_status_cancelled(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $sub = Subscription::factory()->create([
            'plan_id' => $plan->id,
            'status'  => 'active',
        ]);

        $this->asAdmin()
            ->post("/admin/subscriptions/{$sub->id}/cancel")
            ->assertRedirect();

        $this->assertSame('cancelled', $sub->fresh()->status);
    }
}
