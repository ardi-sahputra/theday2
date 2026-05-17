<?php

declare(strict_types=1);

namespace App\Support;

class PlanFormatter
{
    public static function price(int $idr): string
    {
        return 'Rp ' . number_format($idr, 0, ',', '.');
    }

    public static function period(int $days, string $locale = 'id'): string
    {
        if ($days === 0) {
            return $locale === 'en' ? 'forever' : 'selamanya';
        }

        if ($days === 30) {
            return $locale === 'en' ? 'per month' : 'per bulan';
        }

        if ($days === 365) {
            return $locale === 'en' ? 'per year' : 'per tahun';
        }

        if ($days % 365 === 0) {
            $years = intdiv($days, 365);
            return $locale === 'en'
                ? "per {$years} years"
                : "per {$years} tahun";
        }

        if ($days % 30 === 0 && $days < 365) {
            $months = intdiv($days, 30);
            return $locale === 'en'
                ? "per {$months} months"
                : "per {$months} bulan";
        }

        return $locale === 'en'
            ? "per {$days} days"
            : "per {$days} hari";
    }
}
