<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InitializeBudgetPlannerController extends Controller
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $action,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $budget = $this->action->execute(EffectiveUser::resolve());

        return response()->json([
            'message'   => 'Budget planner berhasil diinisialisasi.',
            'budget_id' => $budget->id,
        ], 201);
    }
}
