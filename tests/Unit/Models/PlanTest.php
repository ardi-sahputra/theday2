<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_price_without_discount_equals_price(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);

        $this->assertSame(49000, $plan->effectivePrice());
        $this->assertFalse($plan->hasActiveDiscount());
        $this->assertNull($plan->currentDiscount());
    }

    public function test_effective_price_with_active_discount_applies_percent(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);
        PlanDiscount::factory()->active()->create(['plan_id' => $plan->id, 'percent' => 20]);

        $this->assertSame(39200, $plan->fresh()->effectivePrice());
        $this->assertTrue($plan->fresh()->hasActiveDiscount());
        $this->assertSame(20, $plan->fresh()->currentDiscount()->percent);
    }

    public function test_effective_price_with_upcoming_discount_uses_full_price(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);
        PlanDiscount::factory()->upcoming()->create(['plan_id' => $plan->id, 'percent' => 50]);

        $this->assertSame(49000, $plan->fresh()->effectivePrice());
        $this->assertFalse($plan->fresh()->hasActiveDiscount());
    }

    public function test_effective_price_rounds_to_integer(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49999]);
        PlanDiscount::factory()->active()->create(['plan_id' => $plan->id, 'percent' => 33]);

        // 49999 * 0.67 = 33499.33 → round = 33499
        $this->assertSame(33499, $plan->fresh()->effectivePrice());
    }
}
