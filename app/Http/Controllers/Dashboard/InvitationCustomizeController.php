<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Template;
use App\Support\EffectiveUser;
use App\Support\SectionAccess;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InvitationCustomizeController extends Controller
{
    public function show(Request $request, Invitation $invitation): Response
    {
        abort_unless($invitation->user_id === EffectiveUser::resolve()->id, 403);

        return Inertia::render('Dashboard/Invitations/Customize', $this->buildEditorProps($request, $invitation));
    }

    public function showV2(Request $request, Invitation $invitation): Response
    {
        abort_unless($invitation->user_id === EffectiveUser::resolve()->id, 403);

        // Heal drafts created before names had a durable source: if this
        // invitation has no couple names but the user's CoupleProfile does,
        // copy them in so the editor isn't blank.
        $cp = $invitation->user?->coupleProfile;
        if ($cp && ($cp->groom_name || $cp->bride_name)) {
            $details = $invitation->details()->firstOrCreate(['invitation_id' => $invitation->id]);
            if (! $details->groom_name && ! $details->bride_name) {
                $details->update([
                    'groom_name'     => $cp->groom_name,
                    'groom_nickname' => $cp->groom_nickname,
                    'bride_name'     => $cp->bride_name,
                    'bride_nickname' => $cp->bride_nickname,
                ]);
                if (! $invitation->title) {
                    $invitation->update(['title' => trim(($cp->groom_name ?? '') . ' & ' . ($cp->bride_name ?? ''))]);
                }
                $invitation->refresh();
            }
        }

        $props = $this->buildEditorProps($request, $invitation);

        $props['templates'] = Template::active()
            ->with('category:id,name,slug')
            ->ordered()
            ->get()
            ->map(fn ($t) => [
                'id'            => $t->id,
                'name'          => $t->name,
                'slug'          => $t->slug,
                'thumbnail_url' => $t->thumbnail_url,
                'tier'          => $t->tier->value,
                'category'      => $t->category ? ['name' => $t->category->name, 'slug' => $t->category->slug] : null,
            ])->toArray();

        // Display fields for the Desain template card (reuse catalog data; no extra query).
        $current = collect($props['templates'])->firstWhere('id', $invitation->template?->id);
        $props['invitation']['template_name']          = $current['name'] ?? $invitation->template?->name;
        $props['invitation']['template_category']      = $current['category']['name'] ?? null;
        $props['invitation']['template_thumbnail_url'] = $current['thumbnail_url'] ?? null;

        // Counts via direct queries (avoids guessing relation names for loadCount).
        $props['stats'] = [
            'view_count'   => $invitation->view_count ?? 0,
            'rsvps_count'  => $invitation->rsvps()->count(),
            'ucapan_count' => \App\Models\GuestMessage::where('invitation_id', $invitation->id)->count(),
            'status'       => $invitation->status,
        ];

        return Inertia::render('Dashboard/Invitations/CustomizeV2', $props);
    }

    private function buildEditorProps(Request $request, Invitation $invitation): array
    {
        $invitation->load([
            'details',
            'events'    => fn ($q) => $q->orderBy('sort_order')->orderBy('event_date'),
            'galleries' => fn ($q) => $q->orderBy('sort_order'),
            'music'     => fn ($q) => $q->where('is_default', true)->limit(1),
            'template:id,name,slug,default_config,category_id',
            'template.category:id,slug',
        ]);

        $isStorybook = $invitation->template?->category?->slug === 'storybook';

        if ($isStorybook) {
            foreach (['love_story', 'gift', 'rsvp'] as $key) {
                $invitation->sections()->firstOrCreate(
                    ['section_key' => $key],
                    [
                        'section_type'      => $key,
                        'label'             => $key,
                        'is_enabled'        => true,
                        'is_required'       => false,
                        'sort_order'        => 0,
                        'completion_status' => 'empty',
                        'data_json'         => [],
                    ]
                );
            }
            $invitation->load([
                'sections' => fn ($q) => $q->whereIn('section_key', ['love_story', 'gift', 'rsvp']),
            ]);
        }

        $config = array_merge(
            $invitation->template->default_config ?? [],
            $invitation->custom_config             ?? []
        );

        return [
            'invitation'    => [
                'id'                      => $invitation->id,
                'slug'                    => $invitation->slug,
                'event_type'              => $invitation->event_type,
                'template_id'             => $invitation->template?->id,
                'template_slug'           => $invitation->template?->slug,
                'template_category_slug'  => $invitation->template?->category?->slug,
                'config'                  => $config,
                'details'       => $invitation->details ? [
                    'groom_name'         => $invitation->details->groom_name,
                    'groom_nickname'     => $invitation->details->groom_nickname,
                    'groom_instagram'    => $invitation->details->groom_instagram,
                    'groom_parent_names' => $invitation->details->groom_parent_names,
                    'groom_photo_url'    => $invitation->details->groom_photo_url,
                    'bride_name'         => $invitation->details->bride_name,
                    'bride_nickname'     => $invitation->details->bride_nickname,
                    'bride_instagram'    => $invitation->details->bride_instagram,
                    'bride_parent_names' => $invitation->details->bride_parent_names,
                    'bride_photo_url'    => $invitation->details->bride_photo_url,
                    'opening_text'       => $invitation->details->opening_text,
                    'closing_text'       => $invitation->details->closing_text,
                    'cover_photo_url'    => $invitation->details->cover_photo_url,
                ] : null,
                'events'    => $invitation->events->map(fn ($e) => [
                    'id'                   => $e->id,
                    'event_name'           => $e->event_name,
                    'event_date'           => $e->event_date?->format('Y-m-d'),
                    'event_date_formatted' => $e->event_date
                        ? Carbon::parse($e->event_date)->locale('id')->translatedFormat('l, d F Y')
                        : null,
                    'start_time'  => $e->start_time ? substr($e->start_time, 0, 5) : null,
                    'end_time'    => $e->end_time   ? substr($e->end_time, 0, 5)   : null,
                    'venue_name'    => $e->venue_name,
                    'venue_address' => $e->venue_address,
                    'maps_url'      => $e->maps_url,
                ])->values(),
                'galleries' => $invitation->galleries->map(fn ($g) => [
                    'id'        => $g->id,
                    'image_url' => $g->image_url,
                ])->values(),
                'sections' => $invitation->sections->keyBy('section_key')->map(fn ($s) => [
                    'data'       => $s->data_json ?? [],
                    'is_enabled' => $s->is_enabled,
                ]),
                'music' => $invitation->music->first() ? [
                    'title'    => $invitation->music->first()->title,
                    'file_url' => $invitation->music->first()->file_url,
                ] : null,
            ],
            'canUsePremium' => SectionAccess::isPremium($request->user()),
            'defaultMusic'  => [
                ['id' => 'canon-d',           'title' => 'Canon in D — Pachelbel',             'file_url' => '/music/Canon-in-D-Pachelbels-Canon-Cell.mp3'],
                ['id' => 'thousand-years',    'title' => 'A Thousand Years — Christina Perri', 'file_url' => '/music/Brooklyn-Duo-A-Thousand-Years-WE.mp3'],
                ['id' => 'perfect',           'title' => 'Perfect — Ed Sheeran',               'file_url' => '/music/Perfect-Ed-Sheeran-Wedding-Versi.mp3'],
                ['id' => 'cant-help',         'title' => "Can't Help Falling in Love — Elvis", 'file_url' => '/music/Elvis-Presley-Cant-Help-Falling.mp3'],
                ['id' => 'marry-you',         'title' => 'Marry You — Bruno Mars',             'file_url' => '/music/Bruno-Mars-Marry-You-Official-Ly.mp3'],
                ['id' => 'beautiful-in-white','title' => 'Beautiful In White — Westlife',      'file_url' => '/music/Westlife-Beautiful-in-white-Lyri.mp3'],
            ],
        ];
    }

    public function update(Request $request, Invitation $invitation): JsonResponse
    {
        abort_unless($invitation->user_id === EffectiveUser::resolve()->id, 403);

        if (! SectionAccess::isPremium($request->user())) {
            return response()->json(['error' => 'Fitur ini tersedia di paket Premium.'], 403);
        }

        $request->validate([
            'section_backgrounds'           => 'nullable|array',
            'section_backgrounds.*.type'    => 'nullable|in:image,video,color',
            'section_backgrounds.*.value'   => 'nullable|string|max:1000',
            'section_backgrounds.*.opacity' => 'nullable|numeric|min:0|max:1',
            'gallery_layout'                => 'nullable|string|in:polaroid,masonry,grid,scroll',
        ]);

        $config = $invitation->custom_config ?? [];

        if ($request->has('section_backgrounds')) {
            $config['section_backgrounds'] = $request->input('section_backgrounds');
        }
        if ($request->has('gallery_layout')) {
            $config['gallery_layout'] = $request->input('gallery_layout');
        }

        $invitation->update(['custom_config' => $config]);

        return response()->json(['ok' => true]);
    }

    public function uploadBackground(Request $request, Invitation $invitation, string $key): JsonResponse
    {
        $allowedKeys = ['cover', 'opening', 'events', 'gallery', 'closing'];
        abort_unless(in_array($key, $allowedKeys, true), 422);

        abort_unless($invitation->user_id === EffectiveUser::resolve()->id, 403);

        if (! SectionAccess::isPremium($request->user())) {
            return response()->json(['error' => 'Fitur ini tersedia di paket Premium.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $disk = config('filesystems.uploads');
        $path = $request->file('file')->store(
            "invitations/{$invitation->id}/sections/{$key}",
            $disk
        );

        return response()->json([
            'url' => Storage::disk($disk)->url($path),
        ]);
    }
}
