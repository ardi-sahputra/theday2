<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\GetBudgetItemsTableAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetPlanner\StoreBudgetItemRequest;
use App\Http\Requests\BudgetPlanner\UpdateBudgetItemRequest;
use App\Models\WeddingBudgetItem;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetItemController extends Controller
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $initialize,
        private readonly GetBudgetItemsTableAction $itemsTable,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $budget  = $this->initialize->execute(EffectiveUser::resolve());
        $filters = $request->only(['search', 'category_id', 'payment_status', 'has_actual', 'sort']);
        $items   = $this->itemsTable->execute($budget, $filters);

        return response()->json(['items' => $items]);
    }

    public function store(StoreBudgetItemRequest $request): JsonResponse
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        // Verify category belongs to this budget
        $cat = $budget->categories()->findOrFail($request->validated('category_id'));

        $item = $budget->items()->create([
            ...$request->validated(),
            'planned_amount' => $request->validated('planned_amount') ?? 0,
        ]);

        $item->load('category');

        return response()->json([
            'message' => 'Pengeluaran berhasil disimpan.',
            'item'    => $this->itemsTable->itemResource($item),
        ], 201);
    }

    public function update(UpdateBudgetItemRequest $request, WeddingBudgetItem $item): JsonResponse
    {
        $this->authorizeItem($request, $item);

        $item->update($request->validated());
        $item->load('category');

        return response()->json([
            'message' => 'Pengeluaran berhasil diperbarui.',
            'item'    => $this->itemsTable->itemResource($item),
        ]);
    }

    public function updatePayment(Request $request, WeddingBudgetItem $item): JsonResponse
    {
        $this->authorizeItem($request, $item);

        $data = $request->validate([
            'dp_paid'    => ['sometimes', 'boolean'],
            'final_paid' => ['sometimes', 'boolean'],
        ]);

        $update = [];

        if (isset($data['dp_paid'])) {
            $update['dp_paid']    = $data['dp_paid'];
            $update['dp_paid_at'] = $data['dp_paid'] ? now() : null;
        }

        if (isset($data['final_paid'])) {
            $update['final_paid']    = $data['final_paid'];
            $update['final_paid_at'] = $data['final_paid'] ? now() : null;
        }

        $item->update($update);
        $item->load('category');

        return response()->json([
            'message' => 'Status pembayaran diperbarui.',
            'item'    => $this->itemsTable->itemResource($item),
        ]);
    }

    public function destroy(Request $request, WeddingBudgetItem $item): JsonResponse
    {
        $this->authorizeItem($request, $item);

        // Archive instead of hard delete for normal user flow
        $item->update(['is_archived' => true]);

        return response()->json(['message' => 'Pengeluaran berhasil diarsipkan.']);
    }

    private function authorizeItem(Request $request, WeddingBudgetItem $item): void
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        if ($item->budget_id !== $budget->id) {
            abort(403);
        }
    }
}
