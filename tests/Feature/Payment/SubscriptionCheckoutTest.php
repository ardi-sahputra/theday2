<?php

namespace Tests\Feature\Payment;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plan = Plan::create([
            'name'          => 'Premium',
            'slug'          => 'premium',
            'price'         => 35000,
            'duration_days' => 90,
        ]);
    }

    public function test_checkout_returns_payment_url(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        Http::fake([
            config('mayar.base_url') . '/invoice/create' => Http::response([
                'statusCode' => 200,
                'data'       => ['id' => 'mayar-uuid-sub-001', 'link' => 'https://pay.mayar.id/invoices/sub001'],
            ]),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.subscriptions.checkout'));

        $response->assertOk()
            ->assertJsonStructure(['payment_url'])
            ->assertJsonPath('payment_url', 'https://pay.mayar.id/invoices/sub001');

        $this->assertDatabaseHas('transactions', [
            'user_id'            => $user->id,
            'payment_gateway_id' => 'mayar-uuid-sub-001',
            'status'             => 'pending',
        ]);
    }

    public function test_checkout_blocked_when_premium_has_more_than_14_days(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        \App\Models\Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $this->plan->id,
            'status'     => 'active',
            'starts_at'  => now()->subDays(1),
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.subscriptions.checkout'));

        $response->assertStatus(422)
            ->assertJsonPath('error', 'Paket kamu masih aktif lebih dari 14 hari.');
    }

    public function test_checkout_blocked_for_lifetime_premium(): void
    {
        $lifetimePlan = Plan::create([
            'name'          => 'Premium',
            'slug'          => 'premium-lifetime-dup',
            'price'         => 49000,
            'duration_days' => 0,
        ]);
        // Simulate the canonical premium plan being lifetime — checkout()
        // resolves the plan by slug 'premium', so update this fixture's slug
        // instead of creating a second 'premium' row.
        $this->plan->delete();
        $lifetimePlan->update(['slug' => 'premium']);

        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        \App\Models\Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $lifetimePlan->id,
            'status'     => 'active',
            'starts_at'  => now()->subDays(1),
            'expires_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.subscriptions.checkout'));

        $response->assertStatus(422)
            ->assertJsonPath('error', 'Paket Premium kamu sudah aktif selamanya.');
    }

    public function test_checkout_returns_error_when_mayar_api_fails(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        Http::fake([
            config('mayar.base_url') . '/invoice/create' => Http::response([], 500),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('dashboard.subscriptions.checkout'));

        $response->assertStatus(500)
            ->assertJsonPath('error', 'Gagal memproses pembayaran. Silakan coba lagi.');
    }
}
