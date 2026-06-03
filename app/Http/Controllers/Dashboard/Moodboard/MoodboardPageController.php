<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\Moodboard;

use App\Actions\Moodboard\InitializeMoodboardAction;
use App\Http\Controllers\Controller;
use App\Models\CoupleLink;
use App\Models\Moodboard;
use App\Support\EffectiveUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MoodboardPageController extends Controller
{
    public function __construct(
        private readonly InitializeMoodboardAction $initialize,
    ) {}

    public function index(Request $request): Response
    {
        $user      = EffectiveUser::resolve();
        $moodboard = $this->initialize->execute($user);

        $items = $moodboard->items
            ->map(fn (\App\Models\MoodboardItem $item) => $this->itemResource($item))
            ->values();

        $cp = $user?->coupleProfile;

        return Inertia::render('Dashboard/Moodboard/Index', [
            'moodboard' => [
                'id'           => $moodboard->id,
                'name'         => $moodboard->name,
                'concept_note' => $moodboard->concept_note,
                'palette'      => $moodboard->palette ?? [],
            ],
            'items'  => $items,
            'couple' => [
                'groom' => $cp?->groom_nickname ?: $cp?->groom_name,
                'bride' => $cp?->bride_nickname ?: $cp?->bride_name,
            ],
            'stats' => [
                'count'        => $items->count(),
                'categories'   => $moodboard->items->whereNotNull('tag')->pluck('tag')->unique()->count(),
                'dibuatBerdua' => $this->dibuatBerdua($request),
            ],
        ]);
    }

    private function itemResource(\App\Models\MoodboardItem $item): array
    {
        return [
            'id'         => $item->id,
            'image_url'  => $item->image_url,
            'caption'    => $item->caption,
            'tag'        => $item->tag,
            'colors'     => $item->colors ?? [],
            'sort_order' => $item->sort_order,
        ];
    }

    private function dibuatBerdua(Request $request): bool
    {
        $authId = $request->user()?->id;

        if ($authId === null) {
            return false;
        }

        return CoupleLink::query()
            ->where('status', CoupleLink::STATUS_ACTIVE)
            ->where(fn ($q) => $q->where('owner_id', $authId)->orWhere('partner_id', $authId))
            ->exists();
    }
}
