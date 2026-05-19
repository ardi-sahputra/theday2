<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\BuildBudgetSummaryAction;
use App\Actions\BudgetPlanner\BuildCategoryBreakdownAction;
use App\Actions\BudgetPlanner\GetBudgetItemsTableAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Support\EffectiveUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetPlannerPageController extends Controller
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $initialize,
        private readonly BuildBudgetSummaryAction $summary,
        private readonly BuildCategoryBreakdownAction $breakdown,
        private readonly GetBudgetItemsTableAction $itemsTable,
    ) {}

    public function index(Request $request): Response
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        $filters = $request->only([
            'search', 'category_id', 'payment_status', 'has_actual', 'sort',
        ]);

        $categories = $budget->activeCategories()->get()->map(fn ($c) => [
            'id'   => $c->id,
            'name' => $c->name,
            'type' => $c->type->value,
        ]);

        return Inertia::render('Dashboard/BudgetPlanner/Index', [
            'budget'     => [
                'id'           => $budget->id,
                'total_budget' => $budget->total_budget,
                'currency'     => $budget->currency,
                'notes'        => $budget->notes,
            ],
            'summary'           => $this->summary->execute($budget),
            'categoryBreakdown' => $this->breakdown->execute($budget),
            'items'             => $this->itemsTable->execute($budget, $filters),
            'categories'        => $categories,
            'filters'           => $filters,
        ]);
    }
}
