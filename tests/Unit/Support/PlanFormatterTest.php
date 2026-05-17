<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\PlanFormatter;
use Tests\TestCase;

class PlanFormatterTest extends TestCase
{
    public function test_price_formats_idr_with_thousands_dot(): void
    {
        $this->assertSame('Rp 0', PlanFormatter::price(0));
        $this->assertSame('Rp 35.000', PlanFormatter::price(35000));
        $this->assertSame('Rp 49.000', PlanFormatter::price(49000));
        $this->assertSame('Rp 1.250.000', PlanFormatter::price(1250000));
    }

    public function test_period_id_returns_indonesian_label(): void
    {
        $this->assertSame('selamanya',   PlanFormatter::period(0, 'id'));
        $this->assertSame('per bulan',   PlanFormatter::period(30, 'id'));
        $this->assertSame('per 3 bulan', PlanFormatter::period(90, 'id'));
        $this->assertSame('per 6 bulan', PlanFormatter::period(180, 'id'));
        $this->assertSame('per tahun',   PlanFormatter::period(365, 'id'));
        $this->assertSame('per 2 tahun', PlanFormatter::period(730, 'id'));
        $this->assertSame('per 45 hari', PlanFormatter::period(45, 'id'));
    }

    public function test_period_en_returns_english_label(): void
    {
        $this->assertSame('forever',       PlanFormatter::period(0, 'en'));
        $this->assertSame('per month',     PlanFormatter::period(30, 'en'));
        $this->assertSame('per 3 months',  PlanFormatter::period(90, 'en'));
        $this->assertSame('per year',      PlanFormatter::period(365, 'en'));
        $this->assertSame('per 2 years',   PlanFormatter::period(730, 'en'));
        $this->assertSame('per 45 days',   PlanFormatter::period(45, 'en'));
    }

    public function test_discount_badge_formats_with_minus_sign(): void
    {
        // U+2212 minus sign (not hyphen-minus U+002D)
        $this->assertSame("\u{2212}20%", PlanFormatter::discountBadge(20));
        $this->assertSame("\u{2212}5%",  PlanFormatter::discountBadge(5));
        $this->assertSame("\u{2212}99%", PlanFormatter::discountBadge(99));
    }
}
