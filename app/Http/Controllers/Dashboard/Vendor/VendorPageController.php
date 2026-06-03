<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\EffectiveUser;
use App\Support\VendorCategories;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class VendorPageController extends Controller
{
    public function index(): Response
    {
        $user = EffectiveUser::resolve();

        $models = Vendor::query()
            ->where('user_id', $user?->id)
            ->orderByDesc('booked_at')
            ->orderBy('created_at')
            ->get();

        $vendors = $models
            ->map(fn (Vendor $v) => VendorController::vendorResource($v))
            ->values();

        $presentKeys = $models->pluck('category')->unique()->values()->all();

        $categories = collect(VendorCategories::all())
            ->map(fn (array $c) => ['key' => $c['key'], 'label' => $c['label']])
            ->values();

        return Inertia::render('Dashboard/Vendor/Index', [
            'vendors'       => $vendors,
            'stats'         => $this->stats($vendors, $models),
            'gapCategories' => VendorCategories::gap($presentKeys),
            'categories'    => $categories,
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $vendors
     * @param  Collection<int, Vendor>  $models
     * @return array<string, int>
     */
    private function stats(Collection $vendors, Collection $models): array
    {
        return [
            'total'           => $vendors->count(),
            'lunas'           => $vendors->where('status_key', 'lunas')->count(),
            'dp'              => $vendors->where('status_key', 'dp')->count(),
            'total_committed' => (int) $models->sum(fn (Vendor $v) => (int) ($v->total_cost ?? 0)),
            'total_paid'      => (int) $models->sum(fn (Vendor $v) => (int) ($v->paid_amount ?? 0)),
        ];
    }
}
