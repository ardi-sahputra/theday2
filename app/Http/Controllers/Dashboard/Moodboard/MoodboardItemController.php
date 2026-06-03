<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\Moodboard;

use App\Actions\Moodboard\InitializeMoodboardAction;
use App\Http\Controllers\Controller;
use App\Models\Moodboard;
use App\Models\MoodboardItem;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MoodboardItemController extends Controller
{
    private const TAGS = ['dekor', 'bunga', 'gaun', 'suasana', 'lainnya'];

    public function __construct(
        private readonly InitializeMoodboardAction $initialize,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $board = $this->board();

        $data = $request->validate([
            'image'    => ['required', 'image', 'max:8192'],
            'tag'      => ['nullable', 'in:'.implode(',', self::TAGS)],
            'caption'  => ['nullable', 'string', 'max:140'],
            'colors'   => ['nullable', 'array', 'max:6'],
            'colors.*' => ['string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $disk = config('filesystems.uploads');
        $path = $request->file('image')->store("moodboard/{$board->id}", $disk);

        $maxOrder = $board->items()->max('sort_order') ?? -1;

        $item = $board->items()->create([
            'image_path' => $path,
            'image_url'  => Storage::disk($disk)->url($path),
            'caption'    => $data['caption'] ?? null,
            'tag'        => $data['tag'] ?? null,
            'colors'     => $data['colors'] ?? [],
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json(['item' => $this->itemResource($item)], 201);
    }

    public function update(Request $request, MoodboardItem $item): JsonResponse
    {
        $this->authorizeItem($item);

        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:140'],
            'tag'     => ['nullable', 'in:'.implode(',', self::TAGS)],
        ]);

        $item->update($data);

        return response()->json(['item' => $this->itemResource($item)]);
    }

    public function destroy(MoodboardItem $item): JsonResponse
    {
        $this->authorizeItem($item);

        $disk = config('filesystems.uploads');
        if ($item->image_path) {
            Storage::disk($disk)->delete($item->image_path);
        }

        $item->delete();

        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $board = $this->board();

        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['uuid'],
        ]);

        foreach ($data['ids'] as $order => $id) {
            $board->items()->where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['ok' => true]);
    }

    private function board(): Moodboard
    {
        return $this->initialize->execute(EffectiveUser::resolve());
    }

    private function authorizeItem(MoodboardItem $item): void
    {
        if ($item->moodboard_id !== $this->board()->id) {
            abort(403);
        }
    }

    private function itemResource(MoodboardItem $item): array
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
}
