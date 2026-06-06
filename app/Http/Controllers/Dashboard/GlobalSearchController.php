<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChecklistTask;
use App\Models\GuestList;
use App\Models\Vendor;
use App\Models\WeddingPlan;
use App\Support\EffectiveUser;
use App\Support\VendorCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Universal dashboard search across guests, vendors, invitations and tasks.
     * Returns grouped results scoped to the effective (couple-aware) user.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['groups' => []]);
        }

        $user = EffectiveUser::resolve();
        if (! $user) {
            return response()->json(['groups' => []]);
        }

        $like = '%'.$q.'%';
        $groups = [];

        // ── Tamu ──────────────────────────────────────────────────────────
        $guests = GuestList::where('user_id', $user->id)
            ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('phone_number', 'like', $like))
            ->limit(5)->get();
        if ($guests->isNotEmpty()) {
            $groups[] = [
                'type'  => 'guest',
                'items' => $guests->map(fn ($g) => [
                    'label'    => $g->name,
                    'sublabel' => $g->phone_number ?: ($g->category ?? ''),
                    'url'      => route('dashboard.guest-list.index', ['focus' => $g->id]),
                ])->all(),
            ];
        }

        // ── Vendor ────────────────────────────────────────────────────────
        $vendors = Vendor::where('user_id', $user->id)
            ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('category', 'like', $like))
            ->limit(5)->get();
        if ($vendors->isNotEmpty()) {
            $groups[] = [
                'type'  => 'vendor',
                'items' => $vendors->map(fn ($v) => [
                    'label'    => $v->name,
                    'sublabel' => VendorCategories::label($v->category) ?? $v->category,
                    'url'      => route('dashboard.vendor.index', ['focus' => $v->id]),
                ])->all(),
            ];
        }

        // ── Undangan ──────────────────────────────────────────────────────
        $invitations = $user->invitations()
            ->where('title', 'like', $like)
            ->limit(5)->get();
        if ($invitations->isNotEmpty()) {
            $groups[] = [
                'type'  => 'invitation',
                'items' => $invitations->map(fn ($inv) => [
                    'label'    => $inv->title,
                    'sublabel' => $inv->slug,
                    'url'      => route('dashboard.invitations.customize-v2', $inv->id),
                ])->all(),
            ];
        }

        // ── Tugas (checklist) ─────────────────────────────────────────────
        $plan = WeddingPlan::where('user_id', $user->id)->first();
        if ($plan) {
            $tasks = ChecklistTask::where('wedding_plan_id', $plan->id)
                ->where('title', 'like', $like)
                ->limit(5)->get();
            if ($tasks->isNotEmpty()) {
                $groups[] = [
                    'type'  => 'task',
                    'items' => $tasks->map(fn ($t) => [
                        'label'    => $t->title,
                        'sublabel' => $t->due_date?->translatedFormat('d M Y') ?? '',
                        'url'      => route('dashboard.checklist.index', ['focus' => $t->id]),
                    ])->all(),
                ];
            }
        }

        return response()->json(['groups' => $groups]);
    }
}
