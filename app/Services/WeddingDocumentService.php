<?php

// app/Services/WeddingDocumentService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\WeddingDocument;
use App\Models\WeddingPlan;

class WeddingDocumentService
{
    /**
     * Catalog entries that apply to the given jalur + active condition flags.
     * Returns plain arrays (catalog shape), sorted by guidance.order.
     *
     * @param  array<string,bool>  $flags
     * @return array<int,array<string,mixed>>
     */
    public function resolveCatalog(?string $path, array $flags): array
    {
        if ($path === null) {
            return [];
        }

        $active = collect($flags)->filter()->keys()->all();

        return collect(config('wedding_documents.catalog'))
            ->filter(fn ($e) => in_array($path, $e['paths'], true))
            ->filter(fn ($e) => $e['condition'] === null || in_array($e['condition'], $active, true))
            ->sortBy(fn ($e) => $e['guidance']['order'])
            ->values()
            ->all();
    }

    /**
     * View-model for the Dokumen tab: each applicable catalog entry merged
     * with the couple's saved row (status/file/note).
     *
     * @return array<int,array<string,mixed>>
     */
    public function buildView(WeddingPlan $plan): array
    {
        $rows = $plan->weddingDocuments()->get()->keyBy('key');

        return collect($this->resolveCatalog($plan->document_path, $plan->document_flags ?? []))
            ->map(function ($entry) use ($rows) {
                $row = $rows->get($entry['key']);

                return [
                    'key'        => $entry['key'],
                    'label'      => $entry['label'],
                    'required'   => $entry['required'],
                    'guidance'   => $entry['guidance'],
                    'status'     => $row?->status?->value ?? 'belum',
                    'has_file'   => $row?->file_path !== null,
                    'note'       => $row?->note,
                ];
            })
            ->all();
    }

    /**
     * Get-or-create the per-couple row for a catalog key.
     */
    public function rowFor(WeddingPlan $plan, string $key): WeddingDocument
    {
        return $plan->weddingDocuments()->firstOrCreate(['key' => $key]);
    }

    /**
     * True if the key is a valid catalog key (guards arbitrary input).
     */
    public function isValidKey(string $key): bool
    {
        return collect(config('wedding_documents.catalog'))->contains('key', $key);
    }
}
