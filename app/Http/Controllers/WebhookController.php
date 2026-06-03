<?php

// app/Http/Controllers/WebhookController.php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PaymentActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(private readonly PaymentActivationService $activationService) {}

    public function mayar(Request $request): JsonResponse
    {
        // Verify the webhook token (set in the Mayar dashboard). Fail CLOSED:
        // if the token is not configured we reject everything, so an
        // unconfigured deployment can never accept unauthenticated callbacks.
        $expectedToken = (string) config('mayar.webhook_token');
        if ($expectedToken === '') {
            Log::error('Mayar webhook: MAYAR_WEBHOOK_TOKEN not configured — rejecting.');
            return response()->json(['error' => 'Webhook not configured'], 503);
        }
        $given = (string) $request->header('X-Callback-Token', '');
        if (! hash_equals($expectedToken, $given)) {
            Log::warning('Mayar webhook: invalid callback token');
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $payload = $request->all();
        $event   = $payload['event'] ?? '';
        $data    = $payload['data']  ?? [];

        Log::info('Mayar webhook received', ['event' => $event, 'id' => $data['id'] ?? null, 'raw' => $payload]);

        if ($event !== 'payment.received') {
            Log::info('Mayar webhook ignored (unknown event)', ['event' => $event]);
            return response()->json(['status' => 'ignored']);
        }

        $mayarInvoiceId = $data['id'] ?? null;

        $transaction = Transaction::with('plan', 'user')
            ->where('payment_gateway_id', $mayarInvoiceId)
            ->first();

        if (! $transaction) {
            Log::warning('Mayar webhook: transaction not found', ['mayar_invoice_id' => $mayarInvoiceId]);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $activated = $this->activationService->verifyAndActivate($transaction);

        return response()->json(['status' => $activated ? 'OK' : 'not_paid']);
    }
}
