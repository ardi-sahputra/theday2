<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Enums\BudgetCategoryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetPlanner\StoreBudgetCategoryRequest;
use App\Http\Requests\BudgetPlanner\UpdateBudgetCategoryRequest;
use App\Models\WeddingBudgetCategory;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetCategoryController extends Controller
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $initialize,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        $cats = $budget->categories()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($c) => [
                'id'          => $c->id,
                'name'        => $c->name,
                'slug'        => $c->slug,
                'type'        => $c->type->value,
                'is_archived' => $c->is_archived,
                'sort_order'  => $c->sort_order,
            ]);

        return response()->json(['categories' => $cats]);
    }

    public function store(StoreBudgetCategoryRequest $request): JsonResponse
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        $maxOrder = $budget->categories()->max('sort_order') ?? 0;

        $cat = $budget->categories()->create([
            'name'       => $request->validated('name'),
            'type'       => BudgetCategoryType::Custom,
            'sort_order' => $maxOrder + 10,
        ]);

        return response()->json([
            'message'  => 'Kategori berhasil ditambahkan.',
            'category' => [
                'id'          => $cat->id,
                'name'        => $cat->name,
                'type'        => $cat->type->value,
                'is_archived' => $cat->is_archived,
            ],
        ], 201);
    }

    public function update(UpdateBudgetCategoryRequest $request, WeddingBudgetCategory $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        $data = $request->validated();

        // Block renaming system categories
        if (isset($data['name']) && $category->type === BudgetCategoryType::System) {
            unset($data['name']);
        }

        $category->update($data);

        return response()->json([
            'message'  => 'Kategori berhasil diperbarui.',
            'category' => [
                'id'          => $category->id,
                'name'        => $category->name,
                'type'        => $category->type->value,
                'is_archived' => $category->is_archived,
            ],
        ]);
    }

    public function destroy(Request $request, WeddingBudgetCategory $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        $activeItems = $category->activeItems()->count();
        if ($activeItems > 0) {
            return response()->json([
                'message'      => "Kategori masih memiliki {$activeItems} item aktif. Arsipkan item terlebih dahulu.",
                'items_count'  => $activeItems,
            ], 422);
        }

        $category->update(['is_archived' => true]);

        return response()->json(['message' => 'Kategori berhasil diarsipkan.']);
    }

    private function authorizeCategory(Request $request, WeddingBudgetCategory $category): void
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        if ($category->budget_id !== $budget->id) {
            abort(403);
        }
    }
}
