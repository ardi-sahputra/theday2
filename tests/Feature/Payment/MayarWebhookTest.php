<?php

namespace Tests\Feature\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MayarWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingTransaction(User $user, Plan $plan, string $mayarInvoiceId): Transaction
    {
        return Transaction::create([
            'user_id'            => $user->id,
            'plan_id'            => $plan->id,
            'invoice_number'     => 'INV-20260423-001',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'payment_gateway_id' => $mayarInvoiceId,
            'status'             => PaymentStatus::Pending,
        ]);
    }

    public function test_payment_received_event_activates_premium(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $this->makePendingTransaction($user, $plan, 'mayar-uuid-wh-001');

        Http::fake([
            config('mayar.base_url') . '/invoice/mayar-uuid-wh-001' => Http::response([
                'data' => ['transactionStatus' => 'paid'],
            ]),
        ]);

        $response = $this->postJson('/webhooks/mayar', [
            'event' => 'payment.received',
            'data'  => ['id' => 'mayar-uuid-wh-001', 'transactionStatus' => 'paid'],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'OK');

        $this->assertDatabaseHas('transactions', [
            'payment_gateway_id' => 'mayar-uuid-wh-001',
            'status'             => 'paid',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status'  => 'active',
        ]);
    }

    public function test_non_payment_received_events_are_ignored(): void
    {
        $response = $this->postJson('/webhooks/mayar', [
            'event' => 'payment.reminder',
            'data'  => ['id' => 'some-id'],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ignored');
    }

    public function test_webhook_with_unknown_transaction_returns_404(): void
    {
        $response = $this->postJson('/webhooks/mayar', [
            'event' => 'payment.received',
            'data'  => ['id' => 'non-existent-mayar-id', 'transactionStatus' => 'paid'],
        ]);

        $response->assertNotFound();
    }

    public function test_webhook_is_idempotent_for_already_paid_transaction(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $transaction = $this->makePendingTransaction($user, $plan, 'mayar-uuid-wh-002');
        $transaction->update(['status' => PaymentStatus::Paid, 'paid_at' => now()]);

        $response = $this->postJson('/webhooks/mayar', [
            'event' => 'payment.received',
            'data'  => ['id' => 'mayar-uuid-wh-002', 'transactionStatus' => 'paid'],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'OK');

        Http::assertNothingSent();
    }

    public function test_webhook_returns_200_when_mayar_verification_says_not_paid(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $this->makePendingTransaction($user, $plan, 'mayar-uuid-wh-003');

        Http::fake([
            config('mayar.base_url') . '/invoice/mayar-uuid-wh-003' => Http::response([
                'data' => ['transactionStatus' => 'unpaid'],
            ]),
        ]);

        $response = $this->postJson('/webhooks/mayar', [
            'event' => 'payment.received',
            'data'  => ['id' => 'mayar-uuid-wh-003', 'transactionStatus' => 'paid'],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'not_paid');

        $this->assertDatabaseHas('transactions', [
            'payment_gateway_id' => 'mayar-uuid-wh-003',
            'status'             => 'pending',
        ]);
    }

    public function test_webhook_does_not_activate_when_paid_amount_is_less_than_expected(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $this->makePendingTransaction($user, $plan, 'mayar-uuid-wh-amt'); // amount 35000

        Http::fake([
            config('mayar.base_url') . '/invoice/mayar-uuid-wh-amt' => Http::response([
                'data' => ['transactionStatus' => 'paid', 'amount' => 100],
            ]),
        ]);

        $response = $this->postJson('/webhooks/mayar', [
            'event' => 'payment.received',
            'data'  => ['id' => 'mayar-uuid-wh-amt'],
        ]);

        $response->assertOk()->assertJsonPath('status', 'not_paid');
        $this->assertDatabaseHas('transactions', [
            'payment_gateway_id' => 'mayar-uuid-wh-amt',
            'status'             => 'pending',
        ]);
    }

    public function test_webhook_rejects_invalid_callback_token(): void
    {
        config(['mayar.webhook_token' => 'whsec_secret']);

        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $this->makePendingTransaction($user, $plan, 'mayar-uuid-wh-tok');

        $this->postJson('/webhooks/mayar', [
            'event' => 'payment.received',
            'data'  => ['id' => 'mayar-uuid-wh-tok'],
        ])->assertStatus(401);

        $this->assertDatabaseHas('transactions', [
            'payment_gateway_id' => 'mayar-uuid-wh-tok',
            'status'             => 'pending',
        ]);
        Http::assertNothingSent();
    }

    public function test_webhook_accepts_valid_callback_token(): void
    {
        Mail::fake();
        config(['mayar.webhook_token' => 'whsec_secret']);

        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $this->makePendingTransaction($user, $plan, 'mayar-uuid-wh-tok2');

        Http::fake([
            config('mayar.base_url') . '/invoice/mayar-uuid-wh-tok2' => Http::response([
                'data' => ['transactionStatus' => 'paid', 'amount' => 35000],
            ]),
        ]);

        $this->withHeaders(['X-Callback-Token' => 'whsec_secret'])
            ->postJson('/webhooks/mayar', [
                'event' => 'payment.received',
                'data'  => ['id' => 'mayar-uuid-wh-tok2'],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'OK');
    }

    public function test_webhook_activates_addon_for_addon_transactions(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $subscription = Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'starts_at'  => now()->subDays(1),
            'expires_at' => now()->addDays(60),
        ]);

        $transaction = Transaction::create([
            'user_id'            => $user->id,
            'plan_id'            => null,
            'subscription_id'    => $subscription->id,
            'addon_quantity'     => 2,
            'invoice_number'     => 'INV-20260423-002',
            'amount'             => 30000,
            'payment_method'     => PaymentMethod::Mayar,
            'payment_gateway_id' => 'mayar-uuid-addon-wh',
            'status'             => PaymentStatus::Pending,
        ]);

        Http::fake([
            config('mayar.base_url') . '/invoice/mayar-uuid-addon-wh' => Http::response([
                'data' => ['transactionStatus' => 'paid'],
            ]),
        ]);

        $response = $this->postJson('/webhooks/mayar', [
            'event' => 'payment.received',
            'data'  => ['id' => 'mayar-uuid-addon-wh', 'transactionStatus' => 'paid'],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'OK');

        $this->assertDatabaseHas('invitation_addons', [
            'user_id'  => $user->id,
            'quantity' => 2,
        ]);
    }
}
