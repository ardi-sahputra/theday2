<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Gift\GiftAlreadyClaimedException;
use App\Exceptions\Gift\GiftAwaitingPaymentException;
use App\Exceptions\Gift\GiftExpiredException;
use App\Models\Gift;
use App\Services\GiftClaimService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GiftClaimController extends Controller
{
    public function __construct(private readonly GiftClaimService $claimService) {}

    public function show(string $code): Response
    {
        $gift = Gift::with('plan', 'sender')->where('code', $code)->first();
        if (! $gift) {
            abort(404);
        }

        $state = $this->resolveState($gift);

        return Inertia::render('Gift/Claim', [
            'state' => $state,
            'gift'  => [
                'plan_name'     => $gift->plan->name,
                'duration_days' => $gift->duration_days,
                'sender_name'   => $gift->source === 'user' && $gift->sender ? $gift->sender->name : 'Tim TheDay',
                'message'       => $gift->message,
                'claimed_at'    => $gift->claimed_at?->toIso8601String(),
                'expires_at'    => $gift->expires_at->toIso8601String(),
            ],
            'code'  => $gift->code,
        ]);
    }

    public function claim(string $code): RedirectResponse
    {
        $gift = Gift::where('code', $code)->first();
        if (! $gift) {
            abort(404);
        }

        try {
            $this->claimService->claim($gift, auth()->user());
        } catch (GiftAlreadyClaimedException|GiftExpiredException|GiftAwaitingPaymentException $e) {
            return redirect()->route('gift.claim.show', $code);
        }

        return redirect('/dashboard')->with('success', 'Premium berhasil diaktivasi! Cek dashboard untuk detail.');
    }

    private function resolveState(Gift $gift): string
    {
        if ($gift->status === 'claimed')            return 'already_claimed';
        if ($gift->status === 'expired')            return 'expired';
        if ($gift->status === 'awaiting_payment')   return 'awaiting_payment';
        if ($gift->status === 'pending' && $gift->expires_at->isPast()) return 'expired';

        return auth()->check() ? 'claimable_authed' : 'claimable_guest';
    }
}
