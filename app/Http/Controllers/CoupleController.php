<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\InvitePartnerRequest;
use App\Mail\PartnerInviteMail;
use App\Models\CoupleLink;
use App\Support\CoupleToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class CoupleController extends Controller
{
    public function showAccept(string $token, \Illuminate\Http\Request $request): mixed
    {
        $link = \App\Models\CoupleLink::where('token_hash', \App\Support\CoupleToken::hash($token))
            ->where('status', \App\Models\CoupleLink::STATUS_PENDING)
            ->first();

        if ($link === null) {
            abort(404);
        }

        if ($link->isExpired()) {
            return response()->view('couple.expired', ['email' => $link->invited_email], 410);
        }

        return \Inertia\Inertia::render('Couple/Accept', [
            'token'     => $token,
            'ownerName' => $link->owner->name,
            'email'     => $link->invited_email,
        ]);
    }

    public function accept(string $token, \Illuminate\Http\Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $tokenHash = \App\Support\CoupleToken::hash($token);

        $result = \Illuminate\Support\Facades\DB::transaction(function () use ($tokenHash, $user) {
            $link = \App\Models\CoupleLink::where('token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if ($link === null) {
                return 'not_found';
            }
            if ($link->status !== \App\Models\CoupleLink::STATUS_PENDING) {
                return 'already_used';
            }
            if ($link->isExpired()) {
                return 'expired';
            }
            if (strcasecmp($link->invited_email, $user->email) !== 0) {
                return 'email_mismatch';
            }
            if (\App\Models\CoupleLink::where('partner_id', $user->id)
                ->where('status', \App\Models\CoupleLink::STATUS_ACTIVE)
                ->exists()) {
                return 'partner_already_linked';
            }

            $link->update([
                'partner_id' => $user->id,
                'status'     => \App\Models\CoupleLink::STATUS_ACTIVE,
                'linked_at'  => now(),
            ]);

            return $link;
        });

        match (true) {
            $result === 'not_found'              => abort(404),
            $result === 'already_used'           => abort(409),
            $result === 'expired'                => abort(410),
            $result === 'email_mismatch'         => abort(403),
            $result === 'partner_already_linked' => abort(403, 'Akun kamu sudah terhubung ke partner lain.'),
            default                              => null,
        };

        /** @var \App\Models\CoupleLink $link */
        $link = $result;
        \Illuminate\Support\Facades\Mail::to($link->owner->email)
            ->send(new \App\Mail\PartnerLinkedMail(partnerName: $user->name));

        return redirect()->route('dashboard')->with('status', 'partner-linked');
    }

    public function revoke(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $link = \App\Models\CoupleLink::where('owner_id', $request->user()->id)
            ->whereIn('status', [\App\Models\CoupleLink::STATUS_PENDING, \App\Models\CoupleLink::STATUS_ACTIVE])
            ->first();

        abort_if($link === null, 404);

        if ($link->status === \App\Models\CoupleLink::STATUS_PENDING) {
            $link->delete();
            return back()->with('status', 'partner-invite-cancelled');
        }

        // For active links: delete the row entirely (not status=revoked). This frees the
        // unique partner_id/owner_id slots for future re-linking. Audit trail is intentionally
        // not preserved (out of scope per spec).
        $partnerEmail = $link->partner?->email;
        $link->delete();

        if ($partnerEmail) {
            \Illuminate\Support\Facades\Mail::to($partnerEmail)
                ->send(new \App\Mail\PartnerRevokedMail(ownerName: $request->user()->name));
        }

        return back()->with('status', 'partner-revoked');
    }

    public function unlink(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $link = \App\Models\CoupleLink::where('partner_id', $request->user()->id)
            ->where('status', \App\Models\CoupleLink::STATUS_ACTIVE)
            ->first();

        abort_if($link === null, 404);

        // Delete the row entirely so both users can re-link freely in the future.
        $link->delete();

        return back()->with('status', 'partner-unlinked');
    }

    public function resend(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $link = \App\Models\CoupleLink::where('owner_id', $request->user()->id)
            ->where('status', \App\Models\CoupleLink::STATUS_PENDING)
            ->first();

        abort_if($link === null, 404);

        if ($link->invited_at->addMinutes(5)->isFuture()) {
            return back()->withErrors([
                'resend' => 'Tunggu 5 menit sebelum kirim ulang.',
            ]);
        }

        $token = \App\Support\CoupleToken::generate();
        $link->update([
            'token_hash' => \App\Support\CoupleToken::hash($token),
            'invited_at' => now(),
        ]);

        \Illuminate\Support\Facades\Mail::to($link->invited_email)
            ->send(new \App\Mail\PartnerInviteMail(
                ownerName: $request->user()->name,
                token: $token,
            ));

        return back()->with('status', 'partner-invite-resent');
    }

    public function invite(InvitePartnerRequest $request): RedirectResponse
    {
        $token = CoupleToken::generate();

        CoupleLink::create([
            'owner_id'      => $request->user()->id,
            'partner_id'    => null,
            'invited_email' => $request->validated('email'),
            'token_hash'    => CoupleToken::hash($token),
            'status'        => CoupleLink::STATUS_PENDING,
            'invited_at'    => now(),
        ]);

        Mail::to($request->validated('email'))->send(
            new PartnerInviteMail(
                ownerName: $request->user()->name,
                token: $token,
            )
        );

        return back()->with('status', 'partner-invited');
    }
}
