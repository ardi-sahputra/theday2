<?php

// app/Http/Controllers/Public/PersonalizedInvitationController.php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GuestList;
use App\Models\Invitation;
use App\Models\InvitationView;
use App\Services\GuestOpenTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PersonalizedInvitationController extends Controller
{
    public function __construct(
        private readonly GuestOpenTracker $tracker,
    ) {}

    public function show(Request $request, string $invitationSlug, string $guestSlug): Response
    {
        $invitation = Invitation::where('slug', $invitationSlug)
            ->with([
                'details',
                'events'    => fn ($q) => $q->orderBy('sort_order')->orderBy('event_date'),
                'galleries' => fn ($q) => $q->orderBy('sort_order'),
                'music'     => fn ($q) => $q->where('is_default', true)->limit(1),
                'sections',
                'template:id,name,slug,default_config',
            ])
            ->firstOrFail();

        if (! $invitation->isPublished()) {
            abort(404);
        }

        $guest = GuestList::where('invitation_id', $invitation->id)
            ->where('guest_slug', $guestSlug)
            ->first();

        if ($guest) {
            $this->tracker->track($guest);
        }

        $sessionKey   = "inv_unlocked_{$invitation->id}";
        $needPassword = $invitation->is_password_protected
            && ! $request->session()->get($sessionKey);

        if (! $needPassword) {
            $this->trackView($request, $invitation);
        }

        $config = array_merge(
            $invitation->template->default_config ?? [],
            $invitation->custom_config             ?? []
        );

        // ── Locked: serve ONLY the gate, never the protected content ──
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
                'guest' => [
                    'name'     => $guest?->name ?? \Illuminate\Support\Str::of($guestSlug)->replace('-', ' ')->title()->toString(),
                    'greeting' => $guest?->greeting,
                    'slug'     => $guestSlug,
                ],
                'messages'     => [],
                'needPassword' => true,
            ]);
        }

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
                'id'         => $invitation->id,
                'title'      => $invitation->title,
                'slug'       => $invitation->slug,
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
                    'id'                   => $e->id,
                    'event_name'           => $e->event_name,
                    'event_date'           => $e->event_date?->format('Y-m-d'),
                    'event_date_formatted' => $e->event_date
                        ? \Carbon\Carbon::parse($e->event_date)
                            ->locale('id')
                            ->translatedFormat('l, d F Y')
                        : null,
                    'start_time'           => $e->start_time ? substr($e->start_time, 0, 5) : null,
                    'end_time'             => $e->end_time   ? substr($e->end_time, 0, 5)   : null,
                    'venue_name'           => $e->venue_name,
                    'venue_address'        => $e->venue_address,
                    'maps_url'             => $e->maps_url,
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
            'guest' => [
                'name'     => $guest?->name ?? \Illuminate\Support\Str::of($guestSlug)->replace('-', ' ')->title()->toString(),
                'greeting' => $guest?->greeting,
                'slug'     => $guestSlug,
            ],
            'messages'     => $messages,
            'needPassword' => $needPassword,
        ]);
    }

    private function trackView(Request $request, Invitation $invitation): void
    {
        $sessionViewKey = "inv_viewed_{$invitation->id}";

        if ($request->session()->get($sessionViewKey)) {
            return;
        }

        $request->session()->put($sessionViewKey, true);

        InvitationView::create([
            'invitation_id' => $invitation->id,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'referrer'      => $request->header('referer'),
            'viewed_at'     => now(),
        ]);

        $invitation->increment('view_count');
    }
}
