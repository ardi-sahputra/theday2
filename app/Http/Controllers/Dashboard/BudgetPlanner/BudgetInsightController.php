<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\BuildBudgetInsightAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;

class BudgetInsightController extends Controller
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $initialize,
        private readonly BuildBudgetInsightAction $insights,
    ) {}

    public function index(): JsonResponse
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        // Explicit refresh: allowed to call the AI when the data has changed.
        return response()->json($this->insights->execute($budget, generate: true));
    }
}
