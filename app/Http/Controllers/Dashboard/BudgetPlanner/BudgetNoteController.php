<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Models\WeddingBudgetNote;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetNoteController extends Controller
{
    public function __construct(private readonly InitializeWeddingBudgetAction $initialize) {}

    public function store(Request $request): JsonResponse
    {
        $data   = $request->validate(['body' => 'required|string|max:1000']);
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        $note = $budget->notes()->create([
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ])->load('author');

        return response()->json($this->resource($note, $request->user()->id), 201);
    }

    public function destroy(Request $request, WeddingBudgetNote $note): \Illuminate\Http\Response
    {
        if ($note->user_id !== $request->user()->id) {
            abort(403);
        }
        $note->delete();

        return response()->noContent();
    }

    public static function resource(WeddingBudgetNote $note, int|string $currentUserId): array
    {
        $name = $note->author?->name ?? 'Anonim';

        return [
            'id'               => $note->id,
            'body'             => $note->body,
            'author_name'      => $name,
            'author_initial'   => mb_strtoupper(mb_substr($name, 0, 1)),
            'created_at_human' => $note->created_at?->diffForHumans(),
            'is_mine'          => $note->user_id === $currentUserId,
        ];
    }
}
