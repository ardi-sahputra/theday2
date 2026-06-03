<?php

declare(strict_types=1);

namespace App\Actions\BudgetPlanner;

use App\Models\WeddingBudget;
use App\Support\Formatters\RupiahFormatter;

final class BuildBudgetSummaryAction
{
    public function execute(WeddingBudget $budget): array
    {
        $items = $budget->activeItems()->with('vendor')->get();

        $totalPlanned = $items->sum('planned_amount');
        $totalActual  = $items->sum(fn ($i) => $i->terpakai);

        // Forecast = projected final spend from current commitments. Per line we
        // take the greater of (committed, already spent): a booked vendor commits
        // its full total_cost even if only the DP is paid; a manual line commits
        // its plan. You can never finish below what's already been paid.
        $totalForecast = (int) $items->sum(function ($i) {
            $committed = $i->isLinkedToVendor()
                ? (int) ($i->vendor->total_cost ?? 0)
                : (int) $i->planned_amount;

            return max($committed, (int) $i->terpakai);
        });

        $totalBudget      = $budget->total_budget;
        $remainingBudget  = $totalBudget !== null ? ($totalBudget - $totalActual) : null;
        $plannedVsActual  = $totalPlanned - $totalActual;
        $isOverbudget     = $totalBudget !== null && $totalActual > $totalBudget;
        $overbudgetAmount = $isOverbudget ? ($totalActual - $totalBudget) : 0;

        // Forward-looking: is the projected total already over the ceiling, even
        // before it's been spent? null budget = can't judge yet.
        $forecastVsBudget = $totalBudget !== null ? ($totalBudget - $totalForecast) : null;
        $isForecastOver   = $totalBudget !== null && $totalForecast > $totalBudget;
        $forecastOverAmount = $isForecastOver ? ($totalForecast - $totalBudget) : 0;

        $usagePercentage = null;
        if ($totalBudget !== null && $totalBudget > 0) {
            $usagePercentage = min(round(($totalActual / $totalBudget) * 100, 2), 100);
        }

        // Load categories with their items once; reused for the overbudget count and the
        // per-category breakdown below. Uses the terpakai accessor so DP tracking is included.
        $categoriesWithItems = $budget->activeCategories()->with('activeItems.vendor')->get();

        $overbudgetCategoriesCount = $categoriesWithItems
            ->filter(function ($cat) {
                $planned = $cat->activeItems->sum('planned_amount');
                $actual  = $cat->activeItems->sum(fn ($i) => $i->terpakai);
                return $planned > 0 && $actual > $planned;
            })
            ->count();

        return [
            'total_budget'               => $totalBudget,
            'total_planned'              => $totalPlanned,
            'total_actual'               => $totalActual,
            'remaining_budget'           => $remainingBudget,
            'planned_vs_actual_gap'      => $plannedVsActual,
            'usage_percentage'           => $usagePercentage,
            'is_total_overbudget'        => $isOverbudget,
            'overbudget_amount'          => $overbudgetAmount,
            'overbudget_categories_count' => $overbudgetCategoriesCount,
            'has_budget'                 => $totalBudget !== null,
            'forecast_total'             => $totalForecast,
            'forecast_vs_budget'         => $forecastVsBudget,
            'is_forecast_over'           => $isForecastOver,
            'forecast_over_amount'       => $forecastOverAmount,
            'categories'                 => $categoriesWithItems
                ->map(fn ($cat) => [
                    'name'    => $cat->name,
                    'planned' => (int) $cat->activeItems->sum('planned_amount'),
                    'actual'  => (int) $cat->activeItems->sum(fn ($i) => $i->terpakai),
                ])
                ->filter(fn ($c) => $c['planned'] > 0 || $c['actual'] > 0)
                ->values()
                ->all(),
            'formatted'                  => [
                'total_budget'          => RupiahFormatter::format($totalBudget),
                'total_planned'         => RupiahFormatter::formatOrZero($totalPlanned),
                'total_actual'          => RupiahFormatter::formatOrZero($totalActual),
                'remaining_budget'      => $remainingBudget !== null ? RupiahFormatter::format($remainingBudget) : null,
                'planned_vs_actual_gap' => RupiahFormatter::formatOrZero($plannedVsActual),
                'overbudget_amount'     => RupiahFormatter::formatOrZero($overbudgetAmount),
                'forecast_total'        => RupiahFormatter::formatOrZero($totalForecast),
                'forecast_vs_budget'    => $forecastVsBudget !== null ? RupiahFormatter::format(abs($forecastVsBudget)) : null,
                'forecast_over_amount'  => RupiahFormatter::formatOrZero($forecastOverAmount),
            ],
        ];
    }
}
