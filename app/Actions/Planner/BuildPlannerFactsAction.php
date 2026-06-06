<?php

declare(strict_types=1);

namespace App\Actions\Planner;

use App\Actions\BudgetPlanner\BuildBudgetSummaryAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Enums\ChecklistTaskStatus;
use App\Models\WeddingPlan;
use App\Support\Formatters\RupiahFormatter;
use Carbon\Carbon;

/**
 * Deterministic planner facts — pure query/math, no AI. Drives the momentum
 * strip and the "facts" rows; also reused as the spine of the AI context.
 */
final class BuildPlannerFactsAction
{
    /** Days ahead counted as a vendor payment "due soon". */
    private const PAYMENT_WINDOW_DAYS = 14;

    public function __construct(
        private readonly InitializeWeddingBudgetAction $initBudget,
        private readonly BuildBudgetSummaryAction $summary,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(WeddingPlan $plan): array
    {
        $today = Carbon::today();

        $daysToGo  = $plan->event_date !== null ? (int) round($today->diffInDays($plan->event_date, false)) : null;
        $weeksToGo = $daysToGo !== null ? (int) floor($daysToGo / 7) : null;

        return [
            'has_event_date' => $plan->event_date !== null,
            'days_to_go'     => $daysToGo,
            'weeks_to_go'    => $weeksToGo,
            'checklist'      => $this->checklistFacts($plan, $today),
            'budget'         => $this->budgetFacts($plan),
            'payments_due'   => $this->paymentsDue($plan, $today),
        ];
    }

    /**
     * @return array{done:int,todo:int,total:int,overdue:int,due_this_week:int,progress:int}
     */
    private function checklistFacts(WeddingPlan $plan, Carbon $today): array
    {
        $base = $plan->checklistTasks()->where('status', '!=', ChecklistTaskStatus::Archived->value);

        $done = (clone $base)->where('status', ChecklistTaskStatus::Done->value)->count();
        $todo = (clone $base)->where('status', ChecklistTaskStatus::Todo->value)->count();

        $overdue = (clone $base)
            ->where('status', ChecklistTaskStatus::Todo->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->count();

        $total = $done + $todo;

        $dueThisWeek = (clone $base)
            ->where('status', ChecklistTaskStatus::Todo->value)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today->toDateString(), $today->copy()->addDays(7)->toDateString()])
            ->count();

        return [
            'done'          => $done,
            'todo'          => $todo,
            'total'         => $total,
            'overdue'       => $overdue,
            'due_this_week' => $dueThisWeek,
            'progress'      => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function budgetFacts(WeddingPlan $plan): array
    {
        $budget  = $this->initBudget->execute($plan->user);
        $summary = $this->summary->execute($budget);

        return [
            'has_budget'       => $summary['has_budget'],
            'total_budget'     => $summary['total_budget'],
            'total_actual'     => $summary['total_actual'],
            'forecast_total'   => $summary['forecast_total'],
            'is_forecast_over' => $summary['is_forecast_over'],
            'remaining'        => $summary['remaining_budget'],
            'formatted'        => [
                'forecast_total' => $summary['formatted']['forecast_total'],
                'remaining'      => $summary['formatted']['remaining_budget'],
                'total_actual'   => $summary['formatted']['total_actual'],
            ],
        ];
    }

    /**
     * Linked budget items with a due date inside the window that aren't settled.
     *
     * @return array<int, array<string, mixed>>
     */
    private function paymentsDue(WeddingPlan $plan, Carbon $today): array
    {
        $budget = $this->initBudget->execute($plan->user);

        return $budget->activeItems()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today->toDateString(), $today->copy()->addDays(self::PAYMENT_WINDOW_DAYS)->toDateString()])
            ->with('vendor')
            ->get()
            ->filter(fn ($i) => $i->computed_payment_status !== 'paid')
            ->map(fn ($i) => [
                'item'      => $i->title,
                'due_date'  => $i->due_date->format('Y-m-d'),
                'days'      => (int) round($today->diffInDays($i->due_date, false)),
                'amount'    => (int) ($i->planned_amount - $i->terpakai),
                'amount_fmt'=> RupiahFormatter::formatOrZero(max(0, (int) ($i->planned_amount - $i->terpakai))),
            ])
            ->sortBy('days')
            ->values()
            ->all();
    }
}
