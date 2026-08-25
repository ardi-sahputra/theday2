<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Invitation;
use App\Models\InvitationSection;
use App\Support\InvitationSlug;
use Illuminate\Support\Facades\DB;

final class DuplicateInvitationAction
{
    public function execute(Invitation $source): Invitation
    {
        return DB::transaction(function () use ($source) {
            $source->load(['details', 'events', 'galleries', 'music', 'sections']);

            $newSlug  = InvitationSlug::unique(($source->slug ?? 'undangan') . '-salinan');
            $newTitle = $this->generateUniqueTitle($source->title ?? '', $source->user_id);

            /** @var Invitation $clone */
            $clone = Invitation::create([
                'user_id'               => $source->user_id,
                'template_id'           => $source->template_id,
                'title'                 => $newTitle,
                'event_type'            => $source->event_type->value,
                'slug'                  => $newSlug,
                'custom_config'         => $source->custom_config,
                'status'                => 'draft',
                'published_at'          => null,
                'is_password_protected' => false, // never carry over — clone starts clean
                'view_count'            => 0,
                'current_step'          => $source->current_step ?? 0,
            ]);

            // Clone details
            if ($source->details) {
                $clone->details()->create([
                    'groom_name'          => $source->details->groom_name,
                    'groom_nickname'      => $source->details->groom_nickname,
                    'bride_name'          => $source->details->bride_name,
                    'bride_nickname'      => $source->details->bride_nickname,
                    'groom_parent_names'  => $source->details->groom_parent_names,
                    'bride_parent_names'  => $source->details->bride_parent_names,
                    'groom_photo_url'     => $source->details->groom_photo_url,
                    'bride_photo_url'     => $source->details->bride_photo_url,
                    'opening_text'        => $source->details->opening_text,
                    'closing_text'        => $source->details->closing_text,
                    'cover_photo_url'     => $source->details->cover_photo_url,
                ]);
            } else {
                $clone->details()->create([]);
            }

            // Clone events
            foreach ($source->events as $event) {
                $clone->events()->create([
                    'event_name'    => $event->event_name,
                    'event_date'    => $event->event_date,
                    'start_time'    => $event->start_time,
                    'end_time'      => $event->end_time,
                    'venue_name'    => $event->venue_name,
                    'venue_address' => $event->venue_address,
                    'maps_url'      => $event->maps_url,
                    'sort_order'    => $event->sort_order,
                ]);
            }

            // Clone galleries — reuse URLs, no re-upload
            foreach ($source->galleries as $gallery) {
                $clone->galleries()->create([
                    'image_url'  => $gallery->image_url,
                    'caption'    => $gallery->caption,
                    'sort_order' => $gallery->sort_order,
                ]);
            }

            // Clone music — reuse URLs
            foreach ($source->music as $music) {
                $clone->music()->create([
                    'title'      => $music->title,
                    'file_url'   => $music->file_url,
                    'is_default' => $music->is_default,
                    'sort_order' => $music->sort_order,
                ]);
            }

            // Clone sections (content + enabled state)
            if ($source->sections->isNotEmpty()) {
                foreach ($source->sections as $section) {
                    $clone->sections()->create([
                        'section_key'            => $section->section_key,
                        'step_key'               => $section->step_key,
                        'section_type'           => $section->section_type,
                        'is_enabled'             => $section->is_enabled,
                        'is_required'            => $section->is_required,
                        'is_visible_in_template' => $section->is_visible_in_template,
                        'completion_status'      => $section->completion_status,
                        'data_json'              => $section->data_json,
                        'sort_order'             => $section->sort_order,
                    ]);
                }
            } else {
                InvitationSection::initializeForInvitation($clone->id);
            }

            return $clone;
        });
    }

    private function generateUniqueTitle(string $sourceTitle, string $userId): string
    {
        if (empty($sourceTitle)) {
            return '(Salinan)';
        }

        $base  = "{$sourceTitle} (Salinan)";
        $title = $base;
        $count = 2;

        while (
            Invitation::withTrashed()
                ->where('user_id', $userId)
                ->where('title', $title)
                ->exists()
        ) {
            $title = "{$sourceTitle} (Salinan {$count})";
            $count++;
        }

        return $title;
    }
}
