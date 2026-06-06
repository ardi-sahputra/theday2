<?php

// app/Http/Controllers/Api/InvitationController.php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\PublishInvitationRequest;
use App\Http\Requests\Invitation\ReorderGalleryRequest;
use App\Http\Requests\Invitation\StoreDetailRequest;
use App\Http\Requests\Invitation\StoreEventRequest;
use App\Http\Requests\Invitation\StoreGalleryRequest;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Http\Requests\Invitation\StoreMusicRequest;
use App\Http\Requests\Invitation\UpdateEventRequest;
use App\Http\Requests\Invitation\UpdateInvitationRequest;
use App\Models\Invitation;
use App\Models\InvitationEvent;
use App\Models\InvitationGallery;
use App\Models\InvitationSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    /** Slugs that may never be used by an invitation (collide with app routes). */
    private const RESERVED_SLUGS = [
        'login', 'register', 'logout', 'dashboard', 'admin', 'templates',
        'editor', 'use-template', 'onboarding', 'profile', 'up', 'i',
        'verify-email', 'confirm-password', 'forgot-password',
        'reset-password', 'email', 'sitemap', 'api', 'storage', 'blog', 'upgrade',
    ];

    // ─── GET /api/invitations/check-slug ─────────────────────────

    public function checkSlug(Request $request): JsonResponse
    {
        $request->validate([
            'slug'       => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'exclude_id' => ['nullable', 'uuid'],
        ]);

        $slug      = $request->slug;
        $excludeId = $request->exclude_id;

        $taken = in_array($slug, self::RESERVED_SLUGS, true)
            || \App\Models\Invitation::withTrashed()->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
            || \App\Models\InvitationSlugAlias::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('invitation_id', '!=', $excludeId))
                ->exists();

        $suggestion = null;
        if ($taken) {
            // Try with year suffix first, then increment
            $year = now()->year;
            $candidates = ["{$slug}-{$year}", "{$slug}-2", "{$slug}-3", "{$slug}-4"];
            foreach ($candidates as $candidate) {
                $exists = \App\Models\Invitation::where('slug', $candidate)
                    ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                    ->exists();
                if (!$exists) {
                    $suggestion = $candidate;
                    break;
                }
            }
        }

        return response()->json([
            'available'  => !$taken,
            'suggestion' => $suggestion,
        ]);
    }

    // ─── PUT /api/invitations/{invitation}/slug ──────────────────
    // Change the public slug. The old slug is kept as an alias so links that
    // were already shared keep working (public route redirects alias → current).
    public function updateSlug(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->validate([
            'slug' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ]);
        $slug = $data['slug'];
        $old  = $invitation->slug;

        if ($slug === $old) {
            return response()->json(['slug' => $old]);
        }

        $taken = in_array($slug, self::RESERVED_SLUGS, true)
            || Invitation::withTrashed()->where('slug', $slug)->where('id', '!=', $invitation->id)->exists()
            || \App\Models\InvitationSlugAlias::where('slug', $slug)->where('invitation_id', '!=', $invitation->id)->exists();

        if ($taken) {
            return response()->json(['message' => 'Slug ini sudah dipakai. Coba yang lain.'], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($invitation, $slug, $old) {
                // Keep the old slug as a redirect alias (skip random system slugs is fine — harmless).
                \App\Models\InvitationSlugAlias::updateOrCreate(['slug' => $old], ['invitation_id' => $invitation->id]);
                // The new slug becomes live — drop any alias that pointed here.
                \App\Models\InvitationSlugAlias::where('slug', $slug)->delete();
                $invitation->update(['slug' => $slug]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint race — someone grabbed it a moment ago.
            return response()->json(['message' => 'Slug baru saja diambil orang lain. Coba yang lain.'], 422);
        }

        return response()->json(['slug' => $invitation->slug]);
    }

    // ─── POST /api/invitations ────────────────────────────────────

    public function store(StoreInvitationRequest $request): JsonResponse
    {
        $this->authorize('create', Invitation::class);

        $invitation = Invitation::create([
            'user_id'     => Auth::id(),
            'template_id' => $request->template_id,
            'title'       => $request->title,
            'event_type'  => $request->event_type,
            'slug'        => $this->generateUniqueSlug($request->title),
            'status'      => 'draft',
        ]);

        // Bootstrap empty details row and sections
        $invitation->details()->create(['invitation_id' => $invitation->id]);
        InvitationSection::initializeForInvitation($invitation->id);

        return response()->json([
            'data' => $this->formatInvitation($invitation),
        ], 201);
    }

    // ─── PUT /api/invitations/{invitation} ────────────────────────

    public function update(UpdateInvitationRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->validated();

        if (isset($data['custom_config'])) {
            $data['custom_config'] = array_merge(
                $invitation->custom_config ?? [],
                $data['custom_config']
            );
        }

        if (isset($data['password']) && $data['password']) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $invitation->update($data);

        return response()->json([
            'data' => $this->formatInvitation($invitation->fresh()),
        ]);
    }

    // ─── DELETE /api/invitations/{invitation}/details/photos/{field} ─

    public function deleteDetailPhoto(Invitation $invitation, string $photoField): JsonResponse
    {
        $this->authorize('update', $invitation);

        $allowed = ['groom_photo', 'bride_photo', 'cover_photo'];
        if (! in_array($photoField, $allowed, true)) {
            return response()->json(['message' => 'Field tidak valid.'], 422);
        }

        $urlField = "{$photoField}_url";
        $details  = $invitation->details;

        if ($details && $details->{$urlField}) {
            $this->deleteStorageFile($details->{$urlField});
            $details->update([$urlField => null]);
        }

        return response()->json(['success' => true]);
    }

    // ─── POST /api/invitations/{invitation}/details ───────────────

    public function storeDetails(StoreDetailRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $validated = $request->validated();
        $payload   = [];

        // Handle file uploads via Storage
        $disk = config('filesystems.uploads');
        foreach (['groom_photo', 'bride_photo', 'cover_photo'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store(
                    "invitations/{$invitation->id}/photos",
                    $disk
                );
                $payload["{$field}_url"] = Storage::disk($disk)->url($path);
                unset($validated[$field]);
            }
        }

        // Merge remaining text fields
        foreach ($validated as $key => $value) {
            // Skip the file fields already handled above
            if (in_array($key, ['groom_photo', 'bride_photo', 'cover_photo'])) {
                continue;
            }
            $payload[$key] = $value;
        }

        $details = $invitation->details()->updateOrCreate(
            ['invitation_id' => $invitation->id],
            $payload
        );

        $coupleFields = array_filter([
            'groom_name'         => $validated['groom_name']         ?? null,
            'groom_nickname'     => $validated['groom_nickname']     ?? null,
            'groom_instagram'    => $validated['groom_instagram']    ?? null,
            'groom_parent_names' => $validated['groom_parent_names'] ?? null,
            'bride_name'         => $validated['bride_name']         ?? null,
            'bride_nickname'     => $validated['bride_nickname']     ?? null,
            'bride_instagram'    => $validated['bride_instagram']    ?? null,
            'bride_parent_names' => $validated['bride_parent_names'] ?? null,
        ], fn ($v) => $v !== null);

        if (!empty($coupleFields)) {
            Auth::user()->coupleProfile()->updateOrCreate(
                ['user_id' => Auth::id()],
                $coupleFields
            );
        }

        return response()->json(['data' => $details]);
    }

    // ─── POST /api/invitations/{invitation}/events ────────────────

    public function storeEvent(StoreEventRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $maxOrder = $invitation->events()->max('sort_order') ?? -1;

        $event = $invitation->events()->create([
            ...$request->validated(),
            'sort_order' => $request->input('sort_order', $maxOrder + 1),
        ]);

        return response()->json(['data' => $this->formatEvent($event)], 201);
    }

    // ─── PUT /api/invitations/{invitation}/events/{event} ─────────

    public function updateEvent(
        UpdateEventRequest $request,
        Invitation $invitation,
        InvitationEvent $event
    ): JsonResponse {
        $this->authorize('update', $invitation);
        $this->ensureEventBelongsToInvitation($event, $invitation);

        $event->update($request->validated());

        return response()->json(['data' => $this->formatEvent($event->fresh())]);
    }

    // ─── DELETE /api/invitations/{invitation}/events/{event} ──────

    public function deleteEvent(Invitation $invitation, InvitationEvent $event): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->ensureEventBelongsToInvitation($event, $invitation);

        $event->delete();

        // Re-sequence sort_order
        $invitation->events()->orderBy('sort_order')->get()
            ->each(fn ($e, $i) => $e->update(['sort_order' => $i]));

        return response()->json(['message' => 'Event dihapus.']);
    }

    // ─── POST /api/invitations/{invitation}/galleries ─────────────

    public function storeGallery(StoreGalleryRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $disk = config('filesystems.uploads');
        $path = $request->file('image')->store(
            "invitations/{$invitation->id}/gallery",
            $disk
        );

        $maxOrder = $invitation->galleries()->max('sort_order') ?? -1;

        $gallery = $invitation->galleries()->create([
            'image_url'  => Storage::disk($disk)->url($path),
            'caption'    => $request->input('caption'),
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json(['data' => $gallery], 201);
    }

    // ─── DELETE /api/invitations/{invitation}/galleries/{gallery} ─

    public function deleteGallery(Invitation $invitation, InvitationGallery $gallery): JsonResponse
    {
        $this->authorize('update', $invitation);
        $this->ensureGalleryBelongsToInvitation($gallery, $invitation);

        // Delete the file from storage
        $this->deleteStorageFile($gallery->image_url);

        $gallery->delete();

        // Re-sequence sort_order
        $invitation->galleries()->orderBy('sort_order')->get()
            ->each(fn ($g, $i) => $g->update(['sort_order' => $i]));

        return response()->json(['message' => 'Foto dihapus.']);
    }

    // ─── PUT /api/invitations/{invitation}/galleries/reorder ──────

    public function reorderGallery(ReorderGalleryRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $ids = $request->validated('ids');

        // Verify all IDs belong to this invitation
        $owned = $invitation->galleries()->whereIn('id', $ids)->pluck('id')->all();
        if (count($owned) !== count($ids)) {
            return response()->json(['message' => 'Beberapa foto tidak ditemukan.'], 422);
        }

        // Single UPDATE with a CASE map instead of one query per photo.
        // (ids are validated `uuid` → safe to inline; no injection surface.)
        $caseSql = collect($ids)
            ->map(fn ($id, $order) => "WHEN id = '{$id}' THEN " . (int) $order)
            ->implode(' ');

        $invitation->galleries()
            ->whereIn('id', $ids)
            ->update(['sort_order' => \Illuminate\Support\Facades\DB::raw("CASE {$caseSql} END")]);

        return response()->json([
            'data' => $invitation->galleries()->get()->map(fn ($g) => [
                'id'         => $g->id,
                'image_url'  => $g->image_url,
                'caption'    => $g->caption,
                'sort_order' => $g->sort_order,
            ])->values(),
        ]);
    }

    // ─── POST /api/invitations/{invitation}/music ─────────────────

    public function storeMusic(StoreMusicRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        // Remove any existing default music
        $invitation->music()->where('is_default', true)->delete();

        if ($request->type === 'upload') {
            $disk    = config('filesystems.uploads');
            $path    = $request->file('file')->store(
                "invitations/{$invitation->id}/music",
                $disk
            );
            $title   = $request->file('file')->getClientOriginalName();
            $fileUrl = Storage::disk($disk)->url($path);
        } else {
            // 'default' type — title is a preset label, no stored file
            $title   = $request->input('title');
            $fileUrl = $request->input('file_url', ''); // optional preset URL
        }

        $music = $invitation->music()->create([
            'title'      => $title,
            'file_url'   => $fileUrl,
            'is_default' => true,
            'sort_order' => 0,
        ]);

        return response()->json(['data' => $music], 201);
    }

    // ─── PUT /api/invitations/{invitation}/publish ────────────────

    public function publish(PublishInvitationRequest $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('publish', $invitation);

        $data = $request->validated();

        $updateData = [
            'status'       => 'published',
            'published_at' => now(),
        ];

        if (isset($data['slug'])) {
            $updateData['slug'] = $data['slug'];
        }

        if (isset($data['is_password_protected'])) {
            $updateData['is_password_protected'] = $data['is_password_protected'];
        }

        if (!empty($data['password']) && ($data['is_password_protected'] ?? false)) {
            $updateData['password'] = bcrypt($data['password']);
        } elseif (!($data['is_password_protected'] ?? $invitation->is_password_protected)) {
            // Clear password when protection is turned off
            $updateData['password']              = null;
            $updateData['is_password_protected'] = false;
        }

        if (array_key_exists('expires_at', $data)) {
            $updateData['expires_at'] = $data['expires_at'] ?: null;
        }

        $invitation->update($updateData);
        $invitation->refresh();

        return response()->json([
            'data' => $this->formatInvitation($invitation),
            'url'  => url("/i/{$invitation->slug}"),
        ]);
    }

    // ─── PUT /api/invitations/{invitation}/unpublish ──────────────

    public function unpublish(Invitation $invitation): JsonResponse
    {
        $this->authorize('unpublish', $invitation);

        $invitation->update([
            'status'       => 'draft',
            'published_at' => null,
        ]);

        return response()->json([
            'data' => $this->formatInvitation($invitation->fresh()),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function formatInvitation(Invitation $invitation): array
    {
        return [
            'id'                    => $invitation->id,
            'title'                 => $invitation->title,
            'slug'                  => $invitation->slug,
            'event_type'            => $invitation->event_type->value,
            'status'                => $invitation->status->value,
            'custom_config'         => $invitation->custom_config,
            'is_password_protected' => $invitation->is_password_protected,
            'published_at'          => $invitation->published_at?->toIso8601String(),
            'expires_at'            => $invitation->expires_at?->toIso8601String(),
        ];
    }

    private function formatEvent(InvitationEvent $event): array
    {
        return [
            'id'            => $event->id,
            'event_name'    => $event->event_name,
            'event_date'    => $event->event_date?->format('Y-m-d'),
            'start_time'    => $event->start_time,
            'end_time'      => $event->end_time,
            'venue_name'    => $event->venue_name,
            'venue_address' => $event->venue_address,
            'maps_url'      => $event->maps_url,
            'sort_order'    => $event->sort_order,
        ];
    }

    private function ensureEventBelongsToInvitation(InvitationEvent $event, Invitation $invitation): void
    {
        if ($event->invitation_id !== $invitation->id) {
            abort(404, 'Event tidak ditemukan.');
        }
    }

    private function ensureGalleryBelongsToInvitation(InvitationGallery $gallery, Invitation $invitation): void
    {
        if ($gallery->invitation_id !== $invitation->id) {
            abort(404, 'Foto tidak ditemukan.');
        }
    }

    private function deleteStorageFile(string $url): void
    {
        // Extract the relative path from the uploads disk URL
        $disk       = config('filesystems.uploads');
        $publicBase = Storage::disk($disk)->url('');
        if (str_starts_with($url, $publicBase)) {
            $relativePath = ltrim(str_replace($publicBase, '', $url), '/');
            Storage::disk($disk)->delete($relativePath);
        }
    }

    private function generateUniqueSlug(string $title): string
    {
        $base  = Str::slug($title) ?: 'undangan';
        $slug  = $base;
        $count = 1;

        while (Invitation::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
