<?php

// app/Actions/CreateInvitationFromTemplateAction.php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Str;

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
            $invitation = Invitation::create([
                'user_id'     => $user->id,
                'template_id' => $template->id,
                'title'       => '',
                'event_type'  => 'pernikahan',
                'slug'        => $this->generateUniqueSlug(),
                'status'      => 'draft',
            ]);
            $invitation->details()->create(['invitation_id' => $invitation->id]);
        }

        $this->applyPendingCoupleData($invitation);

        return $invitation;
    }

    /**
     * Pre-fill the invitation with couple data stashed during onboarding
     * (template-first flow), then clear it.
     */
    private function applyPendingCoupleData(Invitation $invitation): void
    {
        $pending = session('pending_couple_data');
        if (! is_array($pending)) {
            return;
        }

        $invitation->update([
            'title'          => trim(($pending['groom_name'] ?? '') . ' & ' . ($pending['bride_name'] ?? '')),
            'marital_status' => $pending['marital_status'] ?? null,
            'wedding_type'   => $pending['wedding_type'] ?? null,
            'city'           => $pending['city'] ?? null,
            'intended_plan'  => $pending['intended_plan'] ?? null,
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

    private function generateUniqueSlug(): string
    {
        do {
            $slug = Str::random(8);
        } while (Invitation::withTrashed()->where('slug', $slug)->exists());

        return $slug;
    }
}
