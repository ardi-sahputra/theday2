<?php

// app/Http/Controllers/Dashboard/GuestListController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Exports\GuestListExport;
use App\Http\Controllers\Controller;
use App\Models\GuestList;
use App\Models\Invitation;
use App\Models\WhatsAppMessageTemplate;
use App\Services\GuestSlugGenerator;
use App\Services\PhoneNumberNormalizer;
use App\Support\EffectiveUser;
use App\Support\SectionAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuestListController extends Controller
{
    public function __construct(
        private readonly GuestSlugGenerator  $slugGenerator,
        private readonly PhoneNumberNormalizer $normalizer,
    ) {}

    private function requirePremium(): ?RedirectResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) {
            return redirect()->route('dashboard.paket')
                ->with('error', 'Fitur Guest List Manager hanya tersedia di paket Premium.');
        }
        return null;
    }

    // ─── Inertia Page ─────────────────────────────────────────────

    public function index(): Response|RedirectResponse
    {
        if ($redirect = $this->requirePremium()) return $redirect;
        $userId = EffectiveUser::resolve()->id;

        $invitations = Invitation::where('user_id', $userId)
            ->select('id', 'slug', 'title', 'status')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($i) => [
                'id'    => $i->id,
                'slug'  => $i->slug,
                'title' => $i->title,
                'status' => $i->status->value,
            ]);

        $hasTemplate = WhatsAppMessageTemplate::where('user_id', $userId)->exists();

        $defaultTemplate = WhatsAppMessageTemplate::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        return Inertia::render('Dashboard/GuestList/Index', [
            'invitations'     => $invitations,
            'hasTemplate'     => $hasTemplate,
            'defaultTemplate' => $defaultTemplate ? [
                'id'      => $defaultTemplate->id,
                'name'    => $defaultTemplate->name,
                'content' => $defaultTemplate->content,
            ] : null,
        ]);
    }

    // ─── API: Guest list with filters ─────────────────────────────

    public function guests(Request $request): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $userId = EffectiveUser::resolve()->id;

        $query = GuestList::with('invitation:id,slug,title')
            ->where('user_id', $userId);

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($status = $request->get('send_status')) {
            $query->filterSendStatus($status);
        }
        if ($rsvp = $request->get('rsvp_status')) {
            $query->filterRsvpStatus($rsvp);
        }
        if ($category = $request->get('category')) {
            $query->filterCategory($category);
        }
        if ($invId = $request->get('invitation_id')) {
            $query->filterInvitation($invId);
        }

        $query->sortBy($request->get('sort', 'newest'));

        $guests = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => collect($guests->items())->map(fn ($g) => $this->guestResource($g)),
            'meta' => [
                'current_page' => $guests->currentPage(),
                'last_page'    => $guests->lastPage(),
                'total'        => $guests->total(),
                'per_page'     => $guests->perPage(),
            ],
        ]);
    }

    // ─── API: Summary ─────────────────────────────────────────────

    public function summary(Request $request): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $userId = EffectiveUser::resolve()->id;

        $base = GuestList::where('user_id', $userId);

        if ($invId = $request->get('invitation_id')) {
            $base->where('invitation_id', $invId);
        }

        // One pass — conditional aggregation instead of 7 round-trips.
        $row = $base->selectRaw(<<<'SQL'
            COUNT(*) AS total,
            SUM(CASE WHEN send_status = 'not_sent'      THEN 1 ELSE 0 END) AS not_sent,
            SUM(CASE WHEN send_status = 'sent'          THEN 1 ELSE 0 END) AS sent,
            SUM(CASE WHEN send_status = 'opened'        THEN 1 ELSE 0 END) AS opened,
            SUM(CASE WHEN rsvp_status = 'attending'     THEN 1 ELSE 0 END) AS attending,
            SUM(CASE WHEN rsvp_status = 'not_attending' THEN 1 ELSE 0 END) AS not_attending,
            SUM(CASE WHEN rsvp_status = 'pending'       THEN 1 ELSE 0 END) AS pending_rsvp
        SQL)->first();

        return response()->json([
            'total'         => (int) $row->total,
            'not_sent'      => (int) $row->not_sent,
            'sent'          => (int) $row->sent,
            'opened'        => (int) $row->opened,
            'attending'     => (int) $row->attending,
            'not_attending' => (int) $row->not_attending,
            'pending_rsvp'  => (int) $row->pending_rsvp,
        ]);
    }

    // ─── API: Categories ──────────────────────────────────────────

    public function categories(): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $categories = GuestList::where('user_id', EffectiveUser::resolve()->id)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return response()->json(['categories' => $categories]);
    }

    // ─── API: Store ───────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $data = $request->validate([
            'invitation_id' => ['nullable', Rule::exists('invitations', 'id')->where('user_id', EffectiveUser::resolve()->id)],
            'name'          => 'required|string|max:150',
            'phone_number'  => 'required|string|max:30',
            'category'      => 'nullable|string|max:50',
            'greeting'      => 'nullable|string|max:50',
            'note'          => 'nullable|string|max:500',
        ]);

        $userId = EffectiveUser::resolve()->id;

        // Normalize phone
        if (! $this->normalizer->isValid($data['phone_number'])) {
            return response()->json([
                'errors' => ['phone_number' => ['Nomor WhatsApp tidak valid.']],
            ], 422);
        }

        $normalized = $this->normalizer->normalize($data['phone_number']);

        // Generate slug
        $slug = $this->slugGenerator->generate(
            $data['name'],
            $data['invitation_id'] ?? null,
            $userId
        );

        $guest = GuestList::create([
            ...$data,
            'user_id'          => $userId,
            'normalized_phone' => $normalized,
            'guest_slug'       => $slug,
        ]);

        $guest->load('invitation:id,slug,title');

        return response()->json($this->guestResource($guest), 201);
    }

    // ─── API: Update ──────────────────────────────────────────────

    public function update(Request $request, GuestList $guest): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $this->authorizeGuest($guest);

        $data = $request->validate([
            'invitation_id' => ['nullable', Rule::exists('invitations', 'id')->where('user_id', EffectiveUser::resolve()->id)],
            'name'          => 'sometimes|string|max:150',
            'phone_number'  => 'sometimes|string|max:30',
            'category'      => 'nullable|string|max:50',
            'greeting'      => 'nullable|string|max:50',
            'note'          => 'nullable|string|max:500',
        ]);

        if (isset($data['phone_number'])) {
            if (! $this->normalizer->isValid($data['phone_number'])) {
                return response()->json([
                    'errors' => ['phone_number' => ['Nomor WhatsApp tidak valid.']],
                ], 422);
            }
            $data['normalized_phone'] = $this->normalizer->normalize($data['phone_number']);
        }

        $guest->update($data);
        $guest->load('invitation:id,slug,title');

        return response()->json($this->guestResource($guest->fresh()));
    }

    // ─── API: Destroy ─────────────────────────────────────────────

    public function destroy(GuestList $guest): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $this->authorizeGuest($guest);
        $guest->delete();

        return response()->json(['ok' => true]);
    }

    // ─── API: Bulk destroy ────────────────────────────────────────

    public function bulkDestroy(Request $request): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $userId = EffectiveUser::resolve()->id;

        $deleted = GuestList::whereIn('id', $data['ids'])
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['deleted' => $deleted]);
    }

    // ─── API: Bulk update category ────────────────────────────────

    public function bulkUpdateCategory(Request $request): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $data = $request->validate([
            'ids'      => 'required|array|min:1',
            'ids.*'    => 'integer',
            'category' => 'required|string|max:50',
        ]);

        $userId = EffectiveUser::resolve()->id;

        $updated = GuestList::whereIn('id', $data['ids'])
            ->where('user_id', $userId)
            ->update(['category' => $data['category']]);

        return response()->json(['updated' => $updated]);
    }

    // ─── API: Export XLSX ─────────────────────────────────────────

    public function export(Request $request): BinaryFileResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $userId = EffectiveUser::resolve()->id;

        $query = GuestList::with('invitation:id,title')
            ->where('user_id', $userId);

        if ($ids = $request->get('ids')) {
            $query->whereIn('id', explode(',', $ids));
        }
        if ($status = $request->get('send_status')) {
            $query->filterSendStatus($status);
        }
        if ($rsvp = $request->get('rsvp_status')) {
            $query->filterRsvpStatus($rsvp);
        }
        if ($category = $request->get('category')) {
            $query->filterCategory($category);
        }

        $guests   = $query->orderBy('name')->get();
        $filename = 'guest-list-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new GuestListExport($guests), $filename);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function authorizeGuest(GuestList $guest): void
    {
        if ($guest->user_id !== EffectiveUser::resolve()->id) {
            abort(403);
        }
    }

    public function guestResource(GuestList $guest): array
    {
        return [
            'id'             => $guest->id,
            'name'           => $guest->name,
            'phone_number'   => $guest->phone_number,
            'normalized_phone' => $guest->normalized_phone,
            'category'       => $guest->category,
            'greeting'       => $guest->greeting,
            'note'           => $guest->note,
            'guest_slug'     => $guest->guest_slug,
            'send_status'    => $guest->send_status->value,
            'rsvp_status'    => $guest->rsvp_status->value,
            'sent_count'     => $guest->sent_count,
            'first_opened_at'=> $guest->first_opened_at?->toISOString(),
            'last_opened_at' => $guest->last_opened_at?->toISOString(),
            'last_sent_at'   => $guest->last_sent_at?->toISOString(),
            'invitation'     => $guest->invitation ? [
                'id'    => $guest->invitation->id,
                'slug'  => $guest->invitation->slug,
                'title' => $guest->invitation->title,
            ] : null,
        ];
    }
}
