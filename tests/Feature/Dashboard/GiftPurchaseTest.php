<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use App\Services\MayarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();

        $this->mock(MayarService::class, function ($mock) {
            $mock->shouldReceive('createInvoice')->andReturn([
                'payment_url'          => 'https://mayar.test/pay/abc',
                'mayar_invoice_id'     => 'inv-x',
                'mayar_transaction_id' => 'txn-x',
            ]);
        });
    }

    public function test_guest_redirected_from_create_page(): void
    {
        $this->get('/dashboard/gifts/create')->assertRedirect('/login');
    }

    public function test_authenticated_user_views_create_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/dashboard/gifts/create')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Dashboard/Gifts/Create'));
    }

    public function test_user_creates_link_mode_gift_and_is_redirected_to_payment(): void
    {
        $user = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();

        $this->actingAs($user)
            ->post('/dashboard/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'link',
                'message'       => 'Happy birthday!',
            ])
            ->assertRedirect('https://mayar.test/pay/abc');

        $this->assertDatabaseHas('gifts', [
            'sender_user_id' => $user->id,
            'delivery_mode'  => 'link',
            'message'        => 'Happy birthday!',
            'status'         => 'awaiting_payment',
        ]);
    }

    public function test_email_mode_requires_recipient_email(): void
    {
        $user = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();

        $this->actingAs($user)
            ->post('/dashboard/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'email',
            ])
            ->assertSessionHasErrors('recipient_email');
    }

    public function test_message_max_280_chars(): void
    {
        $user = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();

        $this->actingAs($user)
            ->post('/dashboard/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'link',
                'message'       => str_repeat('a', 281),
            ])
            ->assertSessionHasErrors('message');
    }

    public function test_cannot_gift_free_plan(): void
    {
        $user = User::factory()->create();
        $freePlan = Plan::factory()->free()->create();

        $this->actingAs($user)
            ->post('/dashboard/gifts', [
                'plan_id'       => $freePlan->id,
                'delivery_mode' => 'link',
            ])
            ->assertSessionHasErrors('plan_id');
    }

    public function test_user_sees_own_gifts_in_index(): void
    {
        $user = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();
        Gift::factory()->count(2)->create(['sender_user_id' => $user->id, 'plan_id' => $plan->id]);
        Gift::factory()->create(['plan_id' => $plan->id]); // other user

        $this->actingAs($user)
            ->get('/dashboard/gifts')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Dashboard/Gifts/Index')
                ->has('gifts.data', 2)
            );
    }

    public function test_user_cannot_see_other_users_gift_detail(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create(['sender_user_id' => $other->id, 'plan_id' => $plan->id]);

        $this->actingAs($user)
            ->get("/dashboard/gifts/{$gift->id}")
            ->assertForbidden();
    }
}
