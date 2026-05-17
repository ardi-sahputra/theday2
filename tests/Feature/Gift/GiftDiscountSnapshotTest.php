<?php

declare(strict_types=1);

namespace Tests\Feature\Gift;

use App\Models\Plan;
use App\Models\PlanDiscount;
use App\Models\Transaction;
use App\Models\User;
use App\Services\GiftPurchaseService;
use App\Services\MayarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GiftDiscountSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $mayarMock = Mockery::mock(MayarService::class);
        $mayarMock->shouldReceive('createInvoice')->andReturn([
            'mayar_invoice_id'     => 'inv-test',
            'mayar_transaction_id' => 'tx-test',
            'payment_url'          => 'https://mayar.test/invoice/inv-test',
        ]);

        $this->app->instance(MayarService::class, $mayarMock);
    }

    public function test_gift_snapshots_discounted_amount_when_discount_active(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);
        PlanDiscount::factory()->active()->create(['plan_id' => $plan->id, 'percent' => 20]);

        $sender = User::factory()->create();
        $service = app(GiftPurchaseService::class);

        $result = $service->createUserGift($sender, [
            'plan_id'         => $plan->id,
            'delivery_mode'   => 'link',
            'recipient_email' => null,
            'message'         => null,
        ]);

        $this->assertSame(39200, (int) $result['gift']->amount);
        $transactionAmount = Transaction::where('gift_id', $result['gift']->id)->value('amount');
        $this->assertSame(39200, (int) $transactionAmount);
    }

    public function test_gift_uses_full_price_when_no_active_discount(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);

        $sender = User::factory()->create();
        $service = app(GiftPurchaseService::class);

        $result = $service->createUserGift($sender, [
            'plan_id'         => $plan->id,
            'delivery_mode'   => 'link',
            'recipient_email' => null,
            'message'         => null,
        ]);

        $this->assertSame(49000, (int) $result['gift']->amount);
    }
}
