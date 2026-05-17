<?php

// app/Http/Controllers/PaymentReturnController.php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PaymentActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentReturnController extends Controller
{
    public function __construct(private readonly PaymentActivationService $activationService) {}

    public function show(Request $request): Response
    {
        $transaction = Transaction::with('plan', 'user', 'gift')->find($request->query('txn'));

        if (! $transaction || $transaction->user_id !== auth()->id()) {
            abort(403);
        }

        if ($transaction->isPending()) {
            $this->activationService->verifyAndActivate($transaction);
            $transaction->refresh();
        }

        if ($transaction->gift_id) {
            $gift = $transaction->gift;
            return Inertia::render('PaymentReturn/Gift', [
                'gift' => [
                    'id'             => $gift->id,
                    'code'           => $gift->code,
                    'plan_name'      => $gift->plan->name,
                    'duration_days'  => $gift->duration_days,
                    'delivery_mode'  => $gift->delivery_mode,
                    'recipient_email'=> $gift->recipient_email,
                    'message'        => $gift->message,
                    'claim_url'      => route('gift.claim.show', $gift->code),
                    'expires_at'     => $gift->expires_at->toIso8601String(),
                ],
                'status' => $transaction->status->value,
            ]);
        }

        return Inertia::render('PaymentReturn', [
            'transactionId' => $transaction->id,
            'status'        => $transaction->status->value,
        ]);
    }

    public function status(Transaction $transaction): JsonResponse
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        return response()->json(['status' => $transaction->status->value]);
    }
}
