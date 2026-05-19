<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetPlanner\UpdateBudgetRequest;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;

class UpdateBudgetController extends Controller
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $initialize,
    ) {}

    public function update(UpdateBudgetRequest $request): JsonResponse
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());
        $budget->update($request->validated());

        return response()->json([
            'message'      => 'Budget berhasil diperbarui.',
            'total_budget' => $budget->total_budget,
        ]);
    }
}
