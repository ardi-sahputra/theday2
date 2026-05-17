<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_returns_currently_running_only(): void
    {
        Plan::factory()->premium()->create();

        PlanDiscount::factory()->active()->create();
        PlanDiscount::factory()->upcoming()->create();
        PlanDiscount::factory()->ended()->create();

        $this->assertSame(1, PlanDiscount::active()->count());
    }

    public function test_upcoming_scope(): void
    {
        Plan::factory()->premium()->create();
        PlanDiscount::factory()->upcoming()->create();
        PlanDiscount::factory()->active()->create();

        $this->assertSame(1, PlanDiscount::upcoming()->count());
    }

    public function test_ended_scope(): void
    {
        Plan::factory()->premium()->create();
        PlanDiscount::factory()->ended()->create();
        PlanDiscount::factory()->active()->create();

        $this->assertSame(1, PlanDiscount::ended()->count());
    }

    public function test_status_returns_correct_label(): void
    {
        Plan::factory()->premium()->create();

        $this->assertSame('active',   PlanDiscount::factory()->active()->create()->status());
        $this->assertSame('upcoming', PlanDiscount::factory()->upcoming()->create()->status());
        $this->assertSame('ended',    PlanDiscount::factory()->ended()->create()->status());
    }
}
