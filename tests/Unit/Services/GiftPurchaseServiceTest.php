<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Gift;
use App\Models\Plan;
use App\Services\GiftPurchaseService;
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
}
