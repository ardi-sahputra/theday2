<?php

// app/Http/Controllers/PublicInvitationController.php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Models\InvitationView;
use App\Models\Rsvp;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class PublicInvitationController extends Controller
{
    // ─── GET /{slug} ──────────────────────────────────────────────

    public function show(Request $request, string $slug): Response|RedirectResponse
    {
        $invitation = Invitation::where('slug', $slug)
            ->with([
                'details',
                'events'    => fn ($q) => $q->orderBy('sort_order')->orderBy('event_date'),
                'galleries' => fn ($q) => $q->orderBy('sort_order'),
                'music'     => fn ($q) => $q->where('is_default', true)->limit(1),
                'sections',
                'template:id,name,slug,default_config',
                'user.activeSubscription.plan',
            ])
            ->first();

        // Slug renamed? An old (aliased) link 301-redirects to the current slug.
        if (! $invitation) {
            $alias = \App\Models\InvitationSlugAlias::where('slug', $slug)->first();
            if ($alias && ($current = Invitation::find($alias->invitation_id))) {
                return redirect('/' . $current->slug, 301);
            }
            abort(404);
        }

        // ── Gate checks ───────────────────────────────────────────
        if (! $invitation->isPublished()) {
            abort(404);
        }

        // ── Password gate ─────────────────────────────────────────
        $sessionKey   = "inv_unlocked_{$invitation->id}";
        $needPassword = $invitation->is_password_protected
            && ! $request->session()->get($sessionKey);

        // ── Track view (once per session) ─────────────────────────
        if (! $needPassword) {
            $this->trackView($request, $invitation);
        }

        // ── Build merged config ───────────────────────────────────
        $config = array_merge(
            $invitation->template->default_config ?? [],
            $invitation->custom_config             ?? []
        );

        // ── Locked: serve ONLY the gate, never the protected content ──
        // (Inertia embeds all props in the page HTML, so the gate must NOT
        //  receive details/events/galleries/music/messages until unlocked.)
        if ($needPassword) {
            return Inertia::render('Invitation/Show', [
                'invitation' => [
                    'id'         => $invitation->id,
                    'title'      => $invitation->title,
                    'slug'       => $invitation->slug,
                    'event_type' => $invitation->event_type->value,
                    'details'    => $invitation->details ? [
                        'cover_photo_url' => $invitation->details->cover_photo_url,
                    ] : null,
                    'config'     => [
                        'primary_color' => $config['primary_color'] ?? null,
                        'font_title'    => $config['font_title']    ?? null,
                        'font'          => $config['font']          ?? null,
                    ],
                ],
                'messages'      => [],
                'needPassword'  => true,
                'showWatermark' => ! ($invitation->user->activeSubscription?->plan?->remove_watermark ?? false),
            ]);
        }

        // ── Load visible messages (pinned first) ──────────────────
        $messages = $invitation->guestMessages()
            ->visible()
            ->orderByDesc('is_pinned')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'name'       => $m->displayName(),
                'message'    => $m->message,
                'is_pinned'  => $m->is_pinned,
                'created_at' => $m->created_at->diffForHumans(),
            ]);

        return Inertia::render('Invitation/Show', [
            'invitation' => [
                'id'        => $invitation->id,
                'title'     => $invitation->title,
                'slug'      => $invitation->slug,
                'event_type' => $invitation->event_type->value,
                'details'    => $invitation->details ? [
                    'groom_name'           => $invitation->details->groom_name,
                    'groom_nickname'       => $invitation->details->groom_nickname,
                    'groom_instagram'      => $invitation->details->groom_instagram,
                    'bride_name'           => $invitation->details->bride_name,
                    'bride_nickname'       => $invitation->details->bride_nickname,
                    'bride_instagram'      => $invitation->details->bride_instagram,
                    'groom_parent_names'   => $invitation->details->groom_parent_names,
                    'bride_parent_names'   => $invitation->details->bride_parent_names,
                    'groom_photo_url'      => $invitation->details->groom_photo_url,
                    'bride_photo_url'      => $invitation->details->bride_photo_url,
                    'opening_text'         => $invitation->details->opening_text,
                    'closing_text'         => $invitation->details->closing_text,
                    'cover_photo_url'      => $invitation->details->cover_photo_url,
                ] : null,
                'events'    => $invitation->events->map(fn ($e) => [
                    'id'            => $e->id,
                    'event_name'    => $e->event_name,
                    'event_date'    => $e->event_date?->format('Y-m-d'),
                    'event_date_formatted' => $e->event_date
                        ? Carbon::parse($e->event_date)
                            ->locale('id')
                            ->translatedFormat('l, d F Y')
                        : null,
                    'start_time'    => $e->start_time ? substr($e->start_time, 0, 5) : null,
                    'end_time'      => $e->end_time   ? substr($e->end_time, 0, 5)   : null,
                    'venue_name'    => $e->venue_name,
                    'venue_address' => $e->venue_address,
                    'maps_url'      => $e->maps_url,
                ])->values(),
                'galleries' => $invitation->galleries->map(fn ($g) => [
                    'id'        => $g->id,
                    'image_url' => $g->image_url,
                    'caption'   => $g->caption,
                ])->values(),
                'music' => $invitation->music->first() ? [
                    'title'    => $invitation->music->first()->title,
                    'file_url' => $invitation->music->first()->file_url,
                ] : null,
                'config'        => $config,
                'template_slug' => $invitation->template?->slug,
                'expires_at'    => $invitation->expires_at?->toIso8601String(),
                // section_key → { enabled, data } map (null = no sections yet → show all)
                'sections'      => $invitation->sections->isNotEmpty()
                    ? $invitation->sections
                        ->mapWithKeys(fn ($s) => [
                            $s->section_key => [
                                'enabled' => $s->is_required ? true : (bool) $s->is_enabled,
                                'data'    => $s->data_json ?? [],
                            ],
                        ])
                        ->toArray()
                    : null,
            ],
            'messages'      => $messages,
            'needPassword'  => $needPassword,
            'showWatermark' => ! ($invitation->user->activeSubscription?->plan?->remove_watermark ?? false),
        ]);
    }

    // ─── POST /{slug}/unlock ──────────────────────────────────────

    public function unlock(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();

        if (! $invitation->is_password_protected) {
            return response()->json(['success' => true]);
        }

        // ── Brute-force guard: max 5 wrong tries per IP per invitation / minute ──
        $throttleKey = "unlock.{$invitation->id}." . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $password = $request->input('password', '');

        if (! Hash::check($password, $invitation->password)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json(['message' => 'Password salah.'], 422);
        }

        // Correct password — reset the counter.
        RateLimiter::clear($throttleKey);

        $request->session()->put("inv_unlocked_{$invitation->id}", true);

        // Track view now that they've unlocked
        $this->trackView($request, $invitation);

        return response()->json(['success' => true]);
    }

    // ─── POST /{slug}/rsvp ────────────────────────────────────────

    public function rsvp(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();

        if (! $invitation->isPublished()) {
            return response()->json(['message' => 'Undangan tidak tersedia.'], 404);
        }

        // ── Spam guard: max 8 RSVPs per IP per invitation / hour ──────────
        $rateLimitKey = "rsvp.{$invitation->id}." . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 8)) {
            return response()->json([
                'message' => 'Terlalu banyak kiriman RSVP. Coba lagi nanti.',
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 3600);

        $data = $request->validate([
            'guest_name'  => 'required|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'attendance'  => 'required|in:hadir,tidak_hadir,ragu',
            'guest_count' => 'nullable|integer|min:1|max:20',
            'notes'       => 'nullable|string|max:500',
        ], [
            'guest_name.required' => 'Nama tamu wajib diisi.',
            'attendance.required' => 'Kehadiran wajib dipilih.',
            'guest_count.max'     => 'Jumlah tamu maksimal 20 orang.',
        ]);

        $rsvp = Rsvp::create([
            'invitation_id' => $invitation->id,
            'guest_name'    => $data['guest_name'],
            'phone'         => $data['phone'] ?? null,
            'attendance'    => $data['attendance'],
            'guest_count'   => $data['guest_count'] ?? 1,
            'notes'         => $data['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'RSVP berhasil dikirim. Terima kasih!',
            'data'    => [
                'id'          => $rsvp->id,
                'guest_name'  => $rsvp->guest_name,
                'attendance'  => $rsvp->attendance->value,
                'guest_count' => $rsvp->guest_count,
            ],
        ], 201);
    }

    // ─── POST /{slug}/messages ────────────────────────────────────

    public function storeMessage(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();

        if (! $invitation->isPublished()) {
            return response()->json(['message' => 'Undangan tidak tersedia.'], 404);
        }

        // ── Rate limiting: max 3 per IP per invitation per hour ───
        $rateLimitKey = 'msg.' . $invitation->id . '.' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return response()->json([
                'message' => 'Kamu sudah mengirim ucapan. Terima kasih! 🤍',
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 3600);

        $data = $request->validate([
            'name'         => 'required|string|min:2|max:100',
            'message'      => 'required|string|min:5|max:500',
            'is_anonymous' => 'sometimes|boolean',
        ], [
            'name.required'    => 'Nama wajib diisi.',
            'name.min'         => 'Nama minimal 2 karakter.',
            'message.required' => 'Ucapan wajib diisi.',
            'message.min'      => 'Ucapan minimal 5 karakter.',
            'message.max'      => 'Ucapan maksimal 500 karakter.',
        ]);

        // Strip HTML — store plain text only
        $data['name']    = strip_tags($data['name']);
        $data['message'] = strip_tags($data['message']);

        $msg = GuestMessage::create([
            'invitation_id' => $invitation->id,
            'name'          => $data['name'],
            'message'       => $data['message'],
            'is_anonymous'  => $data['is_anonymous'] ?? false,
            'is_approved'   => true,
            'ip_address'    => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Ucapanmu sudah terkirim 🤍',
            'data'    => [
                'id'         => $msg->id,
                'name'       => $msg->displayName(),
                'message'    => $msg->message,
                'is_pinned'  => false,
                'created_at' => $msg->created_at->diffForHumans(),
            ],
        ], 201);
    }

    // ─── GET /{slug}/messages ─────────────────────────────────────

    public function messages(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();

        if (! $invitation->isPublished()) {
            return response()->json(['message' => 'Undangan tidak tersedia.'], 404);
        }

        // Respect the password gate — don't leak messages on a locked invitation.
        if ($invitation->is_password_protected
            && ! $request->session()->get("inv_unlocked_{$invitation->id}")) {
            return response()->json(['data' => []]);
        }

        $messages = $invitation->guestMessages()
            ->visible()
            ->orderByDesc('is_pinned')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'name'       => $m->displayName(),
                'message'    => $m->message,
                'is_pinned'  => $m->is_pinned,
                'created_at' => $m->created_at->diffForHumans(),
            ]);

        return response()->json(['data' => $messages]);
    }

    // ─── Private helpers ──────────────────────────────────────────

    private function trackView(Request $request, Invitation $invitation): void
    {
        $sessionViewKey = "inv_viewed_{$invitation->id}";

        // Only track once per session
        if ($request->session()->get($sessionViewKey)) {
            return;
        }

        $request->session()->put($sessionViewKey, true);

        // Persist the view AFTER the response is flushed — the visitor never
        // waits on these two writes (insert + counter bump).
        $invitationId = $invitation->id;
        $ip           = $request->ip();
        $userAgent    = $request->userAgent();
        $referrer     = $request->header('referer');

        defer(function () use ($invitationId, $ip, $userAgent, $referrer) {
            InvitationView::create([
                'invitation_id' => $invitationId,
                'ip_address'    => $ip,
                'user_agent'    => $userAgent,
                'referrer'      => $referrer,
                'viewed_at'     => now(),
            ]);

            Invitation::whereKey($invitationId)->increment('view_count');
        });
    }
}
