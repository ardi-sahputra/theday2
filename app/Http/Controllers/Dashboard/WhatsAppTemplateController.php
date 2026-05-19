<?php

// app/Http/Controllers/Dashboard/WhatsAppTemplateController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\GuestList;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsAppTemplateRenderer;
use App\Support\EffectiveUser;
use App\Support\SectionAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppTemplateController extends Controller
{
    public function __construct(
        private readonly WhatsAppTemplateRenderer $renderer,
    ) {}

    // ─── GET template ──────────────────────────────────────────────

    public function show(): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $template = $this->resolveDefaultTemplate();

        return response()->json([
            'template' => $template ? [
                'id'      => $template->id,
                'name'    => $template->name,
                'content' => $template->content,
            ] : null,
        ]);
    }

    // ─── Save / update template ───────────────────────────────────

    public function update(Request $request): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'content' => 'required|string|max:5000',
        ]);

        $userId   = EffectiveUser::resolve()->id;
        $template = WhatsAppMessageTemplate::updateOrCreate(
            ['user_id' => $userId, 'is_default' => true],
            [
                'name'    => $data['name'],
                'content' => $data['content'],
            ]
        );

        return response()->json([
            'template' => [
                'id'      => $template->id,
                'name'    => $template->name,
                'content' => $template->content,
            ],
            'message' => 'Template WhatsApp berhasil disimpan.',
        ]);
    }

    // ─── Reset to default ─────────────────────────────────────────

    public function reset(): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $userId   = EffectiveUser::resolve()->id;
        $template = WhatsAppMessageTemplate::updateOrCreate(
            ['user_id' => $userId, 'is_default' => true],
            [
                'name'    => 'Template Default',
                'content' => WhatsAppMessageTemplate::defaultContent(),
            ]
        );

        return response()->json([
            'template' => [
                'id'      => $template->id,
                'name'    => $template->name,
                'content' => $template->content,
            ],
            'message' => 'Template dikembalikan ke default.',
        ]);
    }

    // ─── Preview ──────────────────────────────────────────────────

    public function previewRender(Request $request): JsonResponse
    {
        if (! SectionAccess::isPremium(EffectiveUser::resolve())) abort(403);
        $data = $request->validate([
            'content'  => 'required|string|max:5000',
            'guest_id' => 'nullable|exists:guest_lists,id',
        ]);

        $guestId = $data['guest_id'] ?? null;
        $guest   = $guestId
            ? GuestList::with('invitation.events', 'invitation.details')
                ->where('user_id', EffectiveUser::resolve()->id)
                ->findOrFail($guestId)
            : null;

        $invitation = $guest?->invitation;

        $result = $this->renderer->preview($data['content'], $guest, $invitation);

        return response()->json($result);
    }

    // ─── Helper ───────────────────────────────────────────────────

    private function resolveDefaultTemplate(): ?WhatsAppMessageTemplate
    {
        return WhatsAppMessageTemplate::where('user_id', EffectiveUser::resolve()->id)
            ->where('is_default', true)
            ->first();
    }
}
