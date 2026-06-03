<?php

declare(strict_types=1);

namespace App\Actions\BudgetPlanner;

use App\Enums\BudgetCategoryType;
use App\Models\Vendor;
use App\Models\WeddingBudgetItem;
use App\Support\VendorCategories;
use Illuminate\Support\Facades\DB;

/**
 * Ensure a vendor has a matching budget line. The budget item is the "second
 * door" onto the same data — money lives on the vendor, the item mirrors it.
 *
 * Called when a vendor is created/updated so the Budget Planner reflects it
 * without the couple opening that tab. Idempotent: one item per vendor.
 */
final class SyncVendorToBudgetItemAction
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $initialize,
    ) {}

    public function execute(Vendor $vendor): ?WeddingBudgetItem
    {
        $user = $vendor->user;
        if ($user === null) {
            return null;
        }

        // Couple explicitly removed this vendor from the budget — respect that,
        // don't recreate or resurrect the line.
        if ($vendor->budget_excluded) {
            return null;
        }

        return DB::transaction(function () use ($vendor, $user) {
            $budget = $this->initialize->execute($user);

            $slug = VendorCategories::budgetSlug($vendor->category);

            // Land in the mapped category; create it if a custom budget lacks it.
            $category = $budget->categories()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name'       => VendorCategories::label($vendor->category) ?? 'Lainnya',
                    'type'       => BudgetCategoryType::System,
                    'sort_order' => 999,
                ],
            );

            $item = WeddingBudgetItem::query()->where('vendor_id', $vendor->id)->first();

            if ($item === null) {
                // First time: seed the target (planned) from the vendor cost.
                return $budget->items()->create([
                    'category_id'    => $category->id,
                    'vendor_id'      => $vendor->id,
                    'title'          => $vendor->name,
                    'planned_amount' => (int) ($vendor->total_cost ?? 0),
                    'is_archived'    => false,
                ]);
            }

            // Already linked: keep it in the mapped category and un-archive so the
            // line stays visible. Don't touch planned_amount — that's the couple's
            // target and may have been edited independently.
            $item->update([
                'category_id' => $category->id,
                'is_archived' => false,
            ]);

            return $item;
        });
    }
}
