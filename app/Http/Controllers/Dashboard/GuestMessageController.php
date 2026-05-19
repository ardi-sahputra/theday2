<?php

// app/Http/Controllers/Dashboard/GuestMessageController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\GuestMessageLogStatus;
use App\Http\Controllers\Controller;
use App\Models\GuestList;
use App\Models\GuestMessageLog;
use App\Models\WhatsAppMessageTemplate;
use App\Services\PersonalInvitationUrlBuilder;
use App\Services\WhatsAppTemplateRenderer;
use App\Support\EffectiveUser;
use App\Support\SectionAccess;
use Illuminate\Http\JsonResponse;

class GuestMessageController extends Controller
{
    public function __construct(
        private readonly WhatsAppTemplateRenderer    $renderer,
        private readonly PersonalInvitationUrlBuilder $urlBuilder,
        private readonly GuestListController          $guestListController,
    ) {}

    // ─── Generate message ─────────────────────────────────────────

    public function generate(GuestList $guest): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $this->authorizeGuest($guest);

        $template = WhatsAppMessageTemplate::where('user_id', EffectiveUser::resolve()->id)
            ->where('is_default', true)
            ->first();

        if (! $template) {
            return response()->json([
                'error' => 'Template WhatsApp belum dibuat. Silakan buat template terlebih dahulu.',
            ], 422);
        }

        $guest->load('invitation.details', 'invitation.events');
        $invitation = $guest->invitation;

        $message    = $this->renderer->render($template->content, $guest, $invitation);
        $personalUrl = $invitation
            ? $this->urlBuilder->buildForGuest($invitation, $guest)
            : '';

        $phone       = $guest->normalized_phone ?? $guest->phone_number;
        $waUrl       = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);

        return response()->json([
            'message'     => $message,
            'url'         => $personalUrl,
            'whatsapp_url'=> $waUrl,
            'phone'       => $phone,
            'guest_name'  => $guest->name,
            'guest_id'    => $guest->id,
        ]);
    }

    // ─── Mark as sent ─────────────────────────────────────────────

    public function markSent(GuestList $guest): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $this->authorizeGuest($guest);

        $guest->update([
            'send_status' => 'sent',
            'last_sent_at'=> now(),
            'sent_count'  => $guest->sent_count + 1,
        ]);

        GuestMessageLog::create([
            'guest_id'      => $guest->id,
            'invitation_id' => $guest->invitation_id,
            'status'        => GuestMessageLogStatus::ConfirmedSent,
            'meta'          => ['marked_at' => now()->toISOString()],
        ]);

        $guest->load('invitation:id,slug,title');

        return response()->json(
            $this->guestListController->guestResource($guest->fresh(['invitation:id,slug,title']))
        );
    }

    // ─── Log copy ─────────────────────────────────────────────────

    public function storeCopyLog(GuestList $guest): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $this->authorizeGuest($guest);

        GuestMessageLog::create([
            'guest_id'      => $guest->id,
            'invitation_id' => $guest->invitation_id,
            'status'        => GuestMessageLogStatus::Copied,
            'meta'          => ['action' => 'copy', 'at' => now()->toISOString()],
        ]);

        return response()->json(['ok' => true]);
    }

    // ─── Helper ───────────────────────────────────────────────────

    private function authorizeGuest(GuestList $guest): void
    {
        if ($guest->user_id !== EffectiveUser::resolve()->id) {
            abort(403);
        }
    }
}
