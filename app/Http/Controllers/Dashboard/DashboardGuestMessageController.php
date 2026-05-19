<?php

// app/Http/Controllers/Dashboard/DashboardGuestMessageController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Exports\GuestMessageExport;
use App\Http\Controllers\Controller;
use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardGuestMessageController extends Controller
{
    // ─── Inertia Page ─────────────────────────────────────────────

    public function index(Invitation $invitation): Response
    {
        $this->authorizeOwner($invitation);

        return Inertia::render('Dashboard/Invitations/BukuTamu', [
            'invitation' => [
                'id'    => $invitation->id,
                'title' => $invitation->title,
                'slug'  => $invitation->slug,
                'status' => $invitation->status->value,
            ],
        ]);
    }

    // ─── API: List messages ────────────────────────────────────────

    public function messages(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorizeOwner($invitation);

        $query = GuestMessage::where('invitation_id', $invitation->id);

        // Filter
        $filter = $request->get('filter', 'all');
        match ($filter) {
            'visible'  => $query->visible(),
            'hidden'   => $query->hidden(),
            'pinned'   => $query->pinned(),
            default    => null,
        };

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'pinned' => $query->orderByDesc('is_pinned')->orderByDesc('created_at'),
            default  => $query->latest(),
        };

        $messages = $query->paginate($request->integer('per_page', 30));

        return response()->json([
            'data' => collect($messages->items())->map(fn ($m) => $this->messageResource($m)),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'total'        => $messages->total(),
                'per_page'     => $messages->perPage(),
            ],
        ]);
    }

    // ─── API: Summary stats ────────────────────────────────────────

    public function summary(Invitation $invitation): JsonResponse
    {
        $this->authorizeOwner($invitation);

        $base = GuestMessage::where('invitation_id', $invitation->id);

        return response()->json([
            'total'  => (clone $base)->count(),
            'visible' => (clone $base)->visible()->count(),
            'hidden' => (clone $base)->hidden()->count(),
            'pinned' => (clone $base)->pinned()->count(),
        ]);
    }

    // ─── API: Update message (pin / hide) ─────────────────────────

    public function update(Request $request, Invitation $invitation, GuestMessage $message): JsonResponse
    {
        $this->authorizeOwner($invitation);
        $this->authorizeMessage($invitation, $message);

        $data = $request->validate([
            'is_hidden' => 'sometimes|boolean',
            'is_pinned' => 'sometimes|boolean',
        ]);

        if (array_key_exists('is_pinned', $data)) {
            $data['pinned_at'] = $data['is_pinned'] ? now() : null;
        }

        if (array_key_exists('is_hidden', $data)) {
            $data['hidden_at'] = $data['is_hidden'] ? now() : null;
        }

        $message->update($data);

        return response()->json($this->messageResource($message->fresh()));
    }

    // ─── API: Delete message ───────────────────────────────────────

    public function destroy(Invitation $invitation, GuestMessage $message): JsonResponse
    {
        $this->authorizeOwner($invitation);
        $this->authorizeMessage($invitation, $message);

        $message->delete();

        return response()->json(['ok' => true]);
    }

    // ─── API: Bulk actions ─────────────────────────────────────────

    public function bulk(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorizeOwner($invitation);

        $data = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'string',
            'action' => 'required|in:hide,show,unpin,delete',
        ]);

        $query = GuestMessage::where('invitation_id', $invitation->id)
            ->whereIn('id', $data['ids']);

        match ($data['action']) {
            'hide'   => $query->update(['is_hidden' => true,  'hidden_at' => now()]),
            'show'   => $query->update(['is_hidden' => false, 'hidden_at' => null]),
            'unpin'  => $query->update(['is_pinned' => false, 'pinned_at' => null]),
            'delete' => $query->delete(),
        };

        return response()->json(['ok' => true]);
    }

    // ─── API: Export XLSX ─────────────────────────────────────────

    public function export(Request $request, Invitation $invitation): BinaryFileResponse
    {
        $this->authorizeOwner($invitation);

        $query = GuestMessage::where('invitation_id', $invitation->id);

        $scope = $request->get('scope', 'all');
        match ($scope) {
            'visible' => $query->visible(),
            'pinned'  => $query->pinned(),
            default   => null,
        };

        $messages  = $query->latest()->get();
        $filename  = 'buku-tamu-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new GuestMessageExport($messages), $filename);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function authorizeOwner(Invitation $invitation): void
    {
        if ($invitation->user_id !== EffectiveUser::resolve()->id) {
            abort(403);
        }
    }

    private function authorizeMessage(Invitation $invitation, GuestMessage $message): void
    {
        if ($message->invitation_id !== $invitation->id) {
            abort(403);
        }
    }

    private function messageResource(GuestMessage $m): array
    {
        return [
            'id'           => $m->id,
            'name'         => $m->name,
            'display_name' => $m->displayName(),
            'message'      => $m->message,
            'is_anonymous' => $m->is_anonymous,
            'is_hidden'    => $m->is_hidden,
            'is_pinned'    => $m->is_pinned,
            'pinned_at'    => $m->pinned_at?->toISOString(),
            'hidden_at'    => $m->hidden_at?->toISOString(),
            'created_at'   => $m->created_at->toISOString(),
        ];
    }
}
