<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminGiftManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();
    }

    protected function asAdmin()
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123')]);
        return $this->actingAs($admin, 'admin');
    }

    public function test_non_admin_cannot_access_admin_gift_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/gifts')->assertRedirect('/admin/login');
    }

    public function test_admin_can_list_all_gifts(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        Gift::factory()->count(3)->create(['plan_id' => $plan->id]);

        $this->asAdmin()
            ->get('/admin/gifts')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Gifts/Index')
                ->has('gifts.data', 3)
            );
    }

    public function test_admin_creates_admin_source_gift_without_payment(): void
    {
        $plan = Plan::where('slug', 'premium')->first();

        $this->asAdmin()
            ->post('/admin/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'link',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('gifts', [
            'source'         => 'admin',
            'sender_user_id' => null,
            'amount'         => 0,
            'status'         => 'pending',
            'plan_id'        => $plan->id,
        ]);
    }

    public function test_admin_can_override_duration_days(): void
    {
        $plan = Plan::where('slug', 'premium')->first();

        $this->asAdmin()
            ->post('/admin/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'link',
                'duration_days' => 365,
            ]);

        $this->assertDatabaseHas('gifts', ['duration_days' => 365]);
    }

    public function test_admin_can_delete_pending_gift(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->admin()->create(['plan_id' => $plan->id, 'status' => 'pending']);

        $this->asAdmin()
            ->delete("/admin/gifts/{$gift->id}")
            ->assertRedirect('/admin/gifts');

        $this->assertDatabaseMissing('gifts', ['id' => $gift->id]);
    }

    public function test_admin_cannot_delete_claimed_gift(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->admin()->claimed()->create(['plan_id' => $plan->id]);

        $this->asAdmin()
            ->delete("/admin/gifts/{$gift->id}")
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('gifts', ['id' => $gift->id]);
    }
}
