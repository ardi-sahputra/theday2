<?php

declare(strict_types=1);

namespace App\Actions\BudgetPlanner;

use App\Models\WeddingBudget;
use App\Support\Formatters\RupiahFormatter;

final class BuildBudgetSummaryAction
{
    public function execute(WeddingBudget $budget): array
    {
        $items = $budget->activeItems()->get();

        $totalPlanned = $items->sum('planned_amount');
        $totalActual  = $items->sum(fn ($i) => $i->terpakai);

        $totalBudget      = $budget->total_budget;
        $remainingBudget  = $totalBudget !== null ? ($totalBudget - $totalActual) : null;
        $plannedVsActual  = $totalPlanned - $totalActual;
        $isOverbudget     = $totalBudget !== null && $totalActual > $totalBudget;
        $overbudgetAmount = $isOverbudget ? ($totalActual - $totalBudget) : 0;

        $usagePercentage = null;
        if ($totalBudget !== null && $totalBudget > 0) {
            $usagePercentage = min(round(($totalActual / $totalBudget) * 100, 2), 100);
        }

        // Load categories with their items once; reused for the overbudget count and the
        // per-category breakdown below. Uses the terpakai accessor so DP tracking is included.
        $categoriesWithItems = $budget->activeCategories()->with('activeItems')->get();

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
            ],
        ];
    }
}
