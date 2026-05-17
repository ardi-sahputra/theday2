<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PlanController extends Controller
{
    public function index(): Response
    {
        $plans = Plan::orderBy('sort_order')->get()->map(fn (Plan $p) => [
            'id'                 => $p->id,
            'name'               => $p->name,
            'slug'               => $p->slug,
            'price'              => $p->price,
            'duration_days'      => $p->duration_days,
            'max_invitations'    => $p->max_invitations,
            'max_gallery_photos' => $p->max_gallery_photos,
            'is_active'          => $p->is_active,
            'editable'           => $p->slug === 'premium',
        ]);

        return Inertia::render('Admin/Plans/Index', ['plans' => $plans]);
    }

    public function edit(Plan $plan): Response
    {
        if ($plan->slug !== 'premium') {
            throw new AccessDeniedHttpException('Only Premium plan is editable.');
        }

        return Inertia::render('Admin/Plans/Edit', [
            'plan' => [
                'id'                 => $plan->id,
                'name'               => $plan->name,
                'slug'               => $plan->slug,
                'price'              => $plan->price,
                'duration_days'      => $plan->duration_days,
                'max_invitations'    => $plan->max_invitations,
                'max_gallery_photos' => $plan->max_gallery_photos,
                'custom_music'       => (bool) $plan->custom_music,
                'remove_watermark'   => (bool) $plan->remove_watermark,
                'custom_domain'      => (bool) $plan->custom_domain,
                'analytics_access'   => (bool) $plan->analytics_access,
                'features'           => $plan->features ?? [],
                'is_active'          => (bool) $plan->is_active,
            ],
        ]);
    }

    public function update(Plan $plan, UpdatePlanRequest $request): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()
            ->route('admin.plans.index')
            ->with('success', __('admin.plans.flash.updated', ['name' => $plan->name]));
    }
}
