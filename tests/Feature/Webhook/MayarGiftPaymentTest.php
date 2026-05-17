<?php

declare(strict_types=1);

namespace Tests\Feature\Webhook;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\GiftReceivedMail;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MayarService;
use App\Services\PaymentActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MayarGiftPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function mockMayarPaid(): void
    {
        $this->mock(MayarService::class, function ($mock) {
            $mock->shouldReceive('getInvoice')->andReturn(['status' => 'paid', 'transactionStatus' => 'paid']);
        });
    }

    public function test_paid_gift_transaction_promotes_gift_to_pending(): void
    {
        Mail::fake();
        $this->mockMayarPaid();

        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();
        $gift = Gift::factory()->awaitingPayment()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
            'delivery_mode'  => 'link',
        ]);
        $txn = Transaction::create([
            'user_id'            => $sender->id,
            'plan_id'            => $plan->id,
            'gift_id'            => $gift->id,
            'invoice_number'     => 'INV-1',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'inv-1',
        ]);

        $service = app(PaymentActivationService::class);
        $service->verifyAndActivate($txn);

        $gift->refresh();
        $this->assertSame('pending', $gift->status);
        Mail::assertNothingQueued();
    }

    public function test_paid_gift_email_mode_dispatches_received_mail(): void
    {
        Mail::fake();
        $this->mockMayarPaid();

        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();
        $gift = Gift::factory()->awaitingPayment()->email('recipient@example.com')->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
        ]);
        $txn = Transaction::create([
            'user_id'            => $sender->id,
            'plan_id'            => $plan->id,
            'gift_id'            => $gift->id,
            'invoice_number'     => 'INV-2',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'inv-2',
        ]);

        app(PaymentActivationService::class)->verifyAndActivate($txn);

        Mail::assertQueued(GiftReceivedMail::class, fn ($m) => $m->hasTo('recipient@example.com'));
    }

    public function test_paid_gift_does_not_grant_premium_to_sender(): void
    {
        Mail::fake();
        $this->mockMayarPaid();

        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();
        $gift = Gift::factory()->awaitingPayment()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
        ]);
        $txn = Transaction::create([
            'user_id'            => $sender->id,
            'plan_id'            => $plan->id,
            'gift_id'            => $gift->id,
            'invoice_number'     => 'INV-3',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'inv-3',
        ]);

        app(PaymentActivationService::class)->verifyAndActivate($txn);

        $this->assertSame(0, Subscription::where('user_id', $sender->id)->count());
    }

    public function test_paid_gift_webhook_is_idempotent(): void
    {
        Mail::fake();
        $this->mockMayarPaid();

        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();
        $gift = Gift::factory()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
            'status'         => 'pending', // already promoted
        ]);
        $txn = Transaction::create([
            'user_id'            => $sender->id,
            'plan_id'            => $plan->id,
            'gift_id'            => $gift->id,
            'invoice_number'     => 'INV-4',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'inv-4',
        ]);

        app(PaymentActivationService::class)->verifyAndActivate($txn);

        $gift->refresh();
        $this->assertSame('pending', $gift->status); // unchanged
    }
}
