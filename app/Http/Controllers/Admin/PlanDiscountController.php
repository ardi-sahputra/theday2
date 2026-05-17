<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanDiscountRequest;
use App\Http\Requests\Admin\UpdatePlanDiscountRequest;
use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PlanDiscountController extends Controller
{
    public function index(): Response
    {
        $discounts = PlanDiscount::with('plan')
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->through(fn (PlanDiscount $d) => [
                'id'        => $d->id,
                'label'     => $d->label,
                'plan_name' => $d->plan->name,
                'percent'   => $d->percent,
                'starts_at' => $d->starts_at->toIso8601String(),
                'ends_at'   => $d->ends_at->toIso8601String(),
                'status'    => $d->status(),
            ]);

        return Inertia::render('Admin/Discounts/Index', ['discounts' => $discounts]);
    }

    public function create(): Response
    {
        $plans = Plan::where('slug', 'premium')->get(['id', 'name', 'price']);

        return Inertia::render('Admin/Discounts/Create', ['plans' => $plans]);
    }

    public function store(StorePlanDiscountRequest $request): RedirectResponse
    {
        $discount = PlanDiscount::create($request->validated());

        return redirect()->route('admin.discounts.index')
            ->with('success', __('admin.discounts.flash.created', ['label' => $discount->label]));
    }

    public function edit(PlanDiscount $discount): Response
    {
        $plans = Plan::where('slug', 'premium')->get(['id', 'name', 'price']);

        return Inertia::render('Admin/Discounts/Edit', [
            'discount' => [
                'id'        => $discount->id,
                'plan_id'   => $discount->plan_id,
                'label'     => $discount->label,
                'percent'   => $discount->percent,
                'starts_at' => $discount->starts_at->format('Y-m-d\TH:i'),
                'ends_at'   => $discount->ends_at->format('Y-m-d\TH:i'),
                'status'    => $discount->status(),
            ],
            'plans' => $plans,
        ]);
    }

    public function update(PlanDiscount $discount, UpdatePlanDiscountRequest $request): RedirectResponse
    {
        $discount->update($request->validated());

        return redirect()->route('admin.discounts.index')
            ->with('success', __('admin.discounts.flash.updated', ['label' => $discount->label]));
    }

    public function destroy(PlanDiscount $discount): RedirectResponse
    {
        if ($discount->status() === 'active') {
            throw ValidationException::withMessages([
                'discount' => __('admin.discounts.flash.cannot_delete_active'),
            ]);
        }

        $label = $discount->label;
        $discount->delete();

        return redirect()->route('admin.discounts.index')
            ->with('success', __('admin.discounts.flash.deleted', ['label' => $label]));
    }
}
