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
