<?php

// app/Http/Controllers/Api/InvitationSectionController.php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationSectionController extends Controller
{
    // ─── GET /api/invitations/{invitation}/sections ───────────────

    public function index(Invitation $invitation): JsonResponse
    {
        $this->authorizeOwner($invitation);

        // Initialize sections if none exist yet (backwards compat for old invitations)
        if ($invitation->sections()->count() === 0) {
            InvitationSection::initializeForInvitation($invitation->id);
        }

        $sections = $invitation->sections()
            ->get()
            ->map(fn($s) => [
                'section_key'       => $s->section_key,
                'step_key'          => $s->step_key,
                'is_enabled'        => $s->is_enabled,
                'is_required'       => $s->is_required,
                'completion_status' => $s->completion_status,
                'data_json'         => $s->data_json ?? [],
                'sort_order'        => $s->sort_order,
            ]);

        return response()->json(['sections' => $sections]);
    }

    // ─── PATCH /api/invitations/{invitation}/sections/{sectionKey}/toggle ──

    public function toggle(Invitation $invitation, string $sectionKey): JsonResponse
    {
        $this->authorizeOwner($invitation);

        // Ensure sections exist (idempotent upsert for backwards compat)
        if ($invitation->sections()->where('section_key', $sectionKey)->doesntExist()) {
            \App\Models\InvitationSection::initializeForInvitation($invitation->id);
        }

        // Default-renderer templates (e.g. bunga-abadi) have no TemplateSection
        // rows, so sync creates nothing — create the row on demand. It starts
        // enabled (matching the renderer's "show by default") then gets toggled.
        $section = $invitation->sections()->firstOrCreate(
            ['section_key' => $sectionKey],
            [
                'section_type'      => $sectionKey,
                'label'             => \Illuminate\Support\Str::headline($sectionKey),
                'is_enabled'        => true,
                'is_required'       => false,
                'completion_status' => 'empty',
                'data_json'         => [],
            ],
        );

        if ($section->is_required) {
            return response()->json(['message' => 'Bagian wajib tidak dapat dinonaktifkan.'], 422);
        }

        $newEnabled = ! $section->is_enabled;

        $section->update([
            'is_enabled'        => $newEnabled,
            'completion_status' => $newEnabled ? 'empty' : 'disabled',
        ]);

        return response()->json([
            'is_enabled'        => $section->is_enabled,
            'completion_status' => $section->completion_status,
        ]);
    }

    // ─── PATCH /api/invitations/{invitation}/sections/{sectionKey} ──

    public function updateData(Request $request, Invitation $invitation, string $sectionKey): JsonResponse
    {
        $this->authorizeOwner($invitation);

        $data = $request->validate([
            'data'       => 'present|array',
            'status'     => 'sometimes|string|in:empty,incomplete,complete,warning,disabled,error',
            'is_enabled' => 'sometimes|boolean',
        ]);

        $section = $invitation->sections()->firstOrCreate(
            ['section_key' => $sectionKey],
            [
                'section_type'      => $sectionKey,
                'label'             => $sectionKey,
                'is_enabled'        => true,
                'is_required'       => false,
                'sort_order'        => 0,
                'completion_status' => 'empty',
                'data_json'         => [],
            ]
        );

        $updates = ['data_json' => $data['data']];
        if (isset($data['status'])) {
            $updates['completion_status'] = $data['status'];
        }
        // Required sections stay enabled regardless of the payload
        if (isset($data['is_enabled']) && ! $section->is_required) {
            $updates['is_enabled'] = $data['is_enabled'];
        }

        $section->update($updates);

        return response()->json(['success' => true]);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function authorizeOwner(Invitation $invitation): void
    {
        if ($invitation->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
