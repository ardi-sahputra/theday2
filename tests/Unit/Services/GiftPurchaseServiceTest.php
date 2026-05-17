<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use App\Services\GiftPurchaseService;
use App\Services\MayarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftPurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_admin_gift_snapshots_duration_and_zero_amount(): void
    {
        $plan = Plan::factory()->premium()->create(['duration_days' => 60]);

        $service = app(GiftPurchaseService::class);
        $gift = $service->createAdminGift([
            'plan_id'         => $plan->id,
            'delivery_mode'   => 'link',
            'recipient_email' => null,
            'message'         => null,
        ]);

        $this->assertSame('admin', $gift->source);
        $this->assertNull($gift->sender_user_id);
        $this->assertSame(60, $gift->duration_days);
        $this->assertEquals(0, (float) $gift->amount);
        $this->assertSame('pending', $gift->status);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $gift->expires_at->timestamp, 5);
    }

    public function test_create_admin_gift_with_custom_duration_and_expiry(): void
    {
        $plan = Plan::factory()->premium()->create(['duration_days' => 30]);

        $service = app(GiftPurchaseService::class);
        $gift = $service->createAdminGift([
            'plan_id'           => $plan->id,
            'delivery_mode'     => 'link',
            'duration_days'     => 365,
            'custom_expires_at' => now()->addDays(90),
        ]);

        $this->assertSame(365, $gift->duration_days);
        $this->assertEqualsWithDelta(now()->addDays(90)->timestamp, $gift->expires_at->timestamp, 5);
    }

    public function test_create_admin_gift_generates_unique_code(): void
    {
        $plan = Plan::factory()->premium()->create();
        $service = app(GiftPurchaseService::class);

        $g1 = $service->createAdminGift(['plan_id' => $plan->id, 'delivery_mode' => 'link']);
        $g2 = $service->createAdminGift(['plan_id' => $plan->id, 'delivery_mode' => 'link']);

        $this->assertNotSame($g1->code, $g2->code);
        $this->assertStringStartsWith('GIFT-', $g1->code);
    }

    public function test_create_user_gift_snapshots_plan_data_and_inserts_transaction(): void
    {
        $plan = Plan::factory()->premium()->create(['duration_days' => 90, 'price' => 35000]);
        $sender = User::factory()->create();

        $this->mock(MayarService::class, function ($mock) {
            $mock->shouldReceive('createInvoice')->andReturn([
                'payment_url'         => 'https://mayar.test/pay/abc',
                'mayar_invoice_id'    => 'inv-123',
                'mayar_transaction_id'=> 'txn-xyz',
            ]);
        });

        $service = app(GiftPurchaseService::class);
        $result = $service->createUserGift($sender, [
            'plan_id'         => $plan->id,
            'delivery_mode'   => 'link',
            'recipient_email' => null,
            'message'         => 'Selamat ya',
        ]);

        $gift = $result['gift'];
        $this->assertSame('user', $gift->source);
        $this->assertSame($sender->id, $gift->sender_user_id);
        $this->assertSame(90, $gift->duration_days);
        $this->assertEquals(35000, (float) $gift->amount);
        $this->assertSame('awaiting_payment', $gift->status);
        $this->assertSame('Selamat ya', $gift->message);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $gift->expires_at->timestamp, 5);

        $txn = Transaction::where('gift_id', $gift->id)->first();
        $this->assertNotNull($txn);
        $this->assertSame($sender->id, $txn->user_id);
        $this->assertSame($plan->id, $txn->plan_id);
        $this->assertEquals(35000, (float) $txn->amount);
        $this->assertSame('inv-123', $txn->payment_gateway_id);

        $this->assertSame('https://mayar.test/pay/abc', $result['payment_url']);
    }

    public function test_create_user_gift_email_mode_requires_recipient_email(): void
    {
        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();

        $this->mock(MayarService::class, function ($mock) {
            $mock->shouldReceive('createInvoice')->andReturn([
                'payment_url'         => 'https://x.test',
                'mayar_invoice_id'    => 'i',
                'mayar_transaction_id'=> 't',
            ]);
        });

        $service = app(GiftPurchaseService::class);
        $result = $service->createUserGift($sender, [
            'plan_id'         => $plan->id,
            'delivery_mode'   => 'email',
            'recipient_email' => 'friend@example.com',
            'message'         => null,
        ]);

        $this->assertSame('email', $result['gift']->delivery_mode);
        $this->assertSame('friend@example.com', $result['gift']->recipient_email);
    }
}
