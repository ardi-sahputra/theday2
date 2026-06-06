<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\GetBudgetItemsTableAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetPlanner\StoreBudgetItemRequest;
use App\Http\Requests\BudgetPlanner\UpdateBudgetItemRequest;
use App\Models\Vendor;
use App\Models\WeddingBudgetItem;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        [$data, $vendor] = $this->resolveVendorLink($request->validated());

        $item = $budget->items()->create([
            ...$data,
            'planned_amount' => $data['planned_amount'] ?? 0,
        ]);

        $this->writeThroughToVendor($vendor, $request->validated());

        $item->load(['category', 'vendor']);

        return response()->json([
            'message' => 'Pengeluaran berhasil disimpan.',
            'item'    => $this->itemsTable->itemResource($item->refresh()->load('vendor')),
        ], 201);
    }

    public function update(UpdateBudgetItemRequest $request, WeddingBudgetItem $item): JsonResponse
    {
        $this->authorizeItem($request, $item);

        [$data, $vendor] = $this->resolveVendorLink($request->validated(), $item);

        $item->update($data);

        // A PATCH may omit vendor_id while still editing a linked item's money —
        // fall back to the item's current vendor so write-through still lands.
        $vendor ??= $item->vendor;
        $this->writeThroughToVendor($vendor, $request->validated());

        $item->load(['category', 'vendor']);

        return response()->json([
            'message' => 'Pengeluaran berhasil diperbarui.',
            'item'    => $this->itemsTable->itemResource($item->refresh()->load('vendor')),
        ]);
    }

    /**
     * Validate vendor ownership + 1-vendor-1-item uniqueness, strip the transient
     * write-through fields, and when an item is linked null out the manual amount
     * fields (the vendor is the source of truth).
     *
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: \App\Models\Vendor|null}
     */
    private function resolveVendorLink(array $data, ?WeddingBudgetItem $current = null): array
    {
        // These never persist on the item — they flow through to the vendor.
        unset($data['vendor_total_cost'], $data['vendor_paid_amount']);

        if (! array_key_exists('vendor_id', $data) || $data['vendor_id'] === null) {
            return [$data, null];
        }

        $userId = EffectiveUser::resolve()?->id;

        $vendor = Vendor::query()
            ->where('id', $data['vendor_id'])
            ->where('user_id', $userId)
            ->first();

        if ($vendor === null) {
            throw ValidationException::withMessages([
                'vendor_id' => 'Vendor tidak ditemukan.',
            ]);
        }

        // Enforce 1 vendor = 1 budget item (the DB unique index is the backstop).
        $taken = WeddingBudgetItem::query()
            ->where('vendor_id', $vendor->id)
            ->when($current, fn ($q) => $q->whereKeyNot($current->getKey()))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'vendor_id' => 'Vendor ini sudah terhubung ke pengeluaran lain.',
            ]);
        }

        // Re-linking means the couple wants this vendor back in the budget — lift
        // any prior "removed from budget" exclusion.
        if ($vendor->budget_excluded) {
            $vendor->update(['budget_excluded' => false]);
        }

        // Linked item mirrors the vendor — clear manual amounts so they can't drift.
        $data['actual_amount'] = null;
        $data['dp_amount']     = null;
        $data['dp_paid']       = false;
        $data['final_amount']  = null;
        $data['final_paid']    = false;

        return [$data, $vendor];
    }

    /**
     * Second door: money edited on a linked budget item writes back to the vendor
     * (single source of truth, no drift). paid is clamped to total when both set.
     *
     * @param  array<string, mixed>  $validated
     */
    private function writeThroughToVendor(?Vendor $vendor, array $validated): void
    {
        if ($vendor === null) {
            return;
        }

        $update = [];
        if (array_key_exists('vendor_total_cost', $validated)) {
            $update['total_cost'] = $validated['vendor_total_cost'];
        }
        if (array_key_exists('vendor_paid_amount', $validated)) {
            $update['paid_amount'] = (int) ($validated['vendor_paid_amount'] ?? 0);
        }

        if ($update === []) {
            return;
        }

        // Clamp paid to total when both are known after the merge.
        $total = $update['total_cost'] ?? $vendor->total_cost;
        if ($total !== null && isset($update['paid_amount']) && $update['paid_amount'] > (int) $total) {
            $update['paid_amount'] = (int) $total;
        }

        $vendor->update($update);
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

        // Linked to a vendor: "remove from budget" means detach this line for good
        // (the vendor record stays). Flag the vendor so the sync won't resurrect it,
        // then drop the projection row so the unique(vendor_id) slot is freed.
        if ($item->vendor_id !== null) {
            $item->vendor?->update(['budget_excluded' => true]);
            $item->forceDelete();

            return response()->json(['message' => 'Pengeluaran dilepas dari anggaran. Data vendor tetap tersimpan.']);
        }

        // Manual item: archive instead of hard delete for normal user flow.
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
