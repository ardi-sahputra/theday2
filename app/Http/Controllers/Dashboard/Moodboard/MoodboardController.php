<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\Moodboard;

use App\Actions\Moodboard\InitializeMoodboardAction;
use App\Http\Controllers\Controller;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MoodboardController extends Controller
{
    public function __construct(
        private readonly InitializeMoodboardAction $initialize,
    ) {}

    public function update(Request $request): JsonResponse
    {
        $moodboard = $this->initialize->execute(EffectiveUser::resolve());

        $data = $request->validate([
            'name'           => ['nullable', 'string', 'max:80'],
            'concept_note'   => ['nullable', 'string', 'max:500'],
            'palette'        => ['nullable', 'array', 'max:6'],
            'palette.*.hex'  => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'palette.*.label' => ['nullable', 'string', 'max:30'],
        ]);

        $update = [];

        if (array_key_exists('name', $data)) {
            $update['name'] = $data['name'] ?: 'Moodboard Pernikahan';
        }
        if (array_key_exists('concept_note', $data)) {
            $update['concept_note'] = $data['concept_note'];
        }
        if (array_key_exists('palette', $data)) {
            $update['palette'] = $data['palette'] ?? [];
        }

        $moodboard->update($update);

        return response()->json([
            'moodboard' => [
                'id'           => $moodboard->id,
                'name'         => $moodboard->name,
                'concept_note' => $moodboard->concept_note,
                'palette'      => $moodboard->palette ?? [],
            ],
        ]);
    }
}
