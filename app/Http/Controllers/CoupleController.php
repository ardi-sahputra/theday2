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
