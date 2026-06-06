<?php

// app/Http/Controllers/Dashboard/WeddingDocumentController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\WeddingDocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\WeddingPlan;
use App\Services\WeddingDocumentService;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WeddingDocumentController extends Controller
{
    public function __construct(private readonly WeddingDocumentService $service) {}

    public function data(): JsonResponse
    {
        $plan = $this->plan();

        return response()->json([
            'path'      => $plan->document_path,
            'flags'     => $plan->document_flags ?? (object) [],
            'documents' => $this->service->buildView($plan),
        ]);
    }

    public function context(Request $request): JsonResponse
    {
        $flagKeys = config('wedding_documents.flags');

        $data = $request->validate([
            'path'              => ['nullable', 'in:kua,sipil'],
            'flags'             => ['sometimes', 'array'],
            'flags.*'           => ['boolean'],
        ]);

        $plan = $this->plan();
        $flags = collect($data['flags'] ?? [])
            ->only($flagKeys)
            ->map(fn ($v) => (bool) $v)
            ->all();

        $plan->update([
            'document_path'  => $data['path'] ?? $plan->document_path,
            'document_flags' => array_merge($plan->document_flags ?? [], $flags),
        ]);

        return response()->json(['ok' => true]);
    }

    public function status(Request $request, string $key): JsonResponse
    {
        abort_unless($this->service->isValidKey($key), 404);

        $data = $request->validate([
            'status' => ['required', 'in:belum,proses,beres'],
        ]);

        $plan = $this->plan();
        $row  = $this->service->rowFor($plan, $key);
        $row->update([
            'status'       => $data['status'],
            'completed_at' => $data['status'] === WeddingDocumentStatus::Beres->value ? now() : null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function storeFile(Request $request, string $key): JsonResponse
    {
        abort_unless($this->service->isValidKey($key), 404);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $plan = $this->plan();
        $row  = $this->service->rowFor($plan, $key);

        // Replace any previous file for this key.
        if ($row->file_path) {
            Storage::disk('local')->delete($row->file_path);
        }

        $ext  = $request->file('file')->extension();
        $path = $request->file('file')->storeAs(
            "wedding-documents/{$plan->id}", "{$key}.{$ext}", 'local'
        );

        $row->update(['file_path' => $path]);

        return response()->json(['ok' => true, 'has_file' => true]);
    }

    public function destroyFile(string $key): JsonResponse
    {
        abort_unless($this->service->isValidKey($key), 404);

        $plan = $this->plan();
        $row  = $this->service->rowFor($plan, $key);

        if ($row->file_path) {
            Storage::disk('local')->delete($row->file_path);
            $row->update(['file_path' => null]);
        }

        return response()->json(['ok' => true, 'has_file' => false]);
    }

    public function showFile(Request $request, string $key, string $plan): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($this->service->isValidKey($key), 404);

        // Owner check: signed plan id must belong to the acting user.
        $resolved = $this->plan();
        abort_unless($resolved->id === $plan, 403);

        $row = $resolved->weddingDocuments()->where('key', $key)->firstOrFail();
        abort_if($row->file_path === null || ! Storage::disk('local')->exists($row->file_path), 404);

        return Storage::disk('local')->response($row->file_path);
    }

    private function plan(): WeddingPlan
    {
        return WeddingPlan::firstOrCreate(['user_id' => EffectiveUser::resolve()->id]);
    }
}
