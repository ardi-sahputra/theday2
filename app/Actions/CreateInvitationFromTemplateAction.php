<?php

// app/Actions/CreateInvitationFromTemplateAction.php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use App\Support\InvitationSlug;

class CreateInvitationFromTemplateAction
{
    public function execute(User $user, string $templateId): ?Invitation
    {
        $template = Template::find($templateId);

        if (! $template) {
            return null;
        }

        // Reuse existing draft if one exists (max 1 draft per user)
        $invitation = Invitation::where('user_id', $user->id)
            ->where('status', 'draft')
            ->first();

        if ($invitation) {
            $invitation->update([
                'template_id' => $template->id,
                'title'       => '',
                'event_type'  => 'pernikahan',
            ]);
            $invitation->details()->updateOrCreate(
                ['invitation_id' => $invitation->id],
                []
            );
        } else {
            // Derive a clean slug from the couple's names (session stash or the
            // durable CoupleProfile); fall back to random only if no names exist.
            $pending = session('pending_couple_data');
            $cp      = $user->coupleProfile;
            $groom   = $pending['groom_nickname'] ?? $pending['groom_name'] ?? $cp?->groom_nickname ?? $cp?->groom_name;
            $bride   = $pending['bride_nickname'] ?? $pending['bride_name'] ?? $cp?->bride_nickname ?? $cp?->bride_name;

            $invitation = Invitation::create([
                'user_id'     => $user->id,
                'template_id' => $template->id,
                'title'       => '',
                'event_type'  => 'pernikahan',
                'slug'        => InvitationSlug::forCouple($groom, $bride),
                'status'      => 'draft',
            ]);
            $invitation->details()->create(['invitation_id' => $invitation->id]);
        }

        $this->applyPendingCoupleData($invitation);

        return $invitation;
    }

    /**
     * Pre-fill the invitation with couple data stashed during onboarding
     * (template-first flow). Falls back to the durable CoupleProfile when the
     * onboarding session is gone (e.g. invitation created days later), so names
     * are never lost. Clears the session afterwards.
     */
    private function applyPendingCoupleData(Invitation $invitation): void
    {
        $pending = session('pending_couple_data');

        // Fallback: no session data → pull from the couple's durable profile.
        if (! is_array($pending)) {
            $cp = $invitation->user?->coupleProfile;
            if (! $cp || (! $cp->groom_name && ! $cp->bride_name)) {
                return;
            }
            $pending = [
                'groom_name'     => $cp->groom_name,
                'groom_nickname' => $cp->groom_nickname,
                'bride_name'     => $cp->bride_name,
                'bride_nickname' => $cp->bride_nickname,
                'wedding_date'   => optional($cp->wedding_date)->format('Y-m-d'),
            ];
        }

        $invitation->update([
            'title'          => trim(($pending['groom_name'] ?? '') . ' & ' . ($pending['bride_name'] ?? '')),
            'marital_status' => $pending['marital_status'] ?? $invitation->marital_status,
            'wedding_type'   => $pending['wedding_type']   ?? $invitation->wedding_type,
            'city'           => $pending['city']           ?? $invitation->city,
            'intended_plan'  => $pending['intended_plan']  ?? $invitation->intended_plan,
        ]);

        $invitation->details()->update([
            'groom_name'     => $pending['groom_name'] ?? null,
            'groom_nickname' => $pending['groom_nickname'] ?? null,
            'bride_name'     => $pending['bride_name'] ?? null,
            'bride_nickname' => $pending['bride_nickname'] ?? null,
        ]);

        if (! empty($pending['wedding_date'])) {
            $invitation->events()->updateOrCreate(
                ['invitation_id' => $invitation->id, 'sort_order' => 0],
                [
                    'event_name'    => 'Akad & Resepsi',
                    'event_date'    => $pending['wedding_date'],
                    'start_time'    => $pending['start_time'] ?? null,
                    'venue_name'    => $pending['venue_name'] ?? '',
                    'venue_address' => $pending['venue_address'] ?? null,
                ],
            );
        }

        session()->forget('pending_couple_data');
    }
}
