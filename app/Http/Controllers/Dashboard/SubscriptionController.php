<?php

// app/Http/Controllers/Dashboard/SubscriptionController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Transaction;
use App\Services\MayarService;
use App\Services\PaymentActivationService;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly MayarService $mayarService,
        private readonly PaymentActivationService $activationService,
    ) {}

    public function index(Request $request): Response
    {
        $effectiveUser = EffectiveUser::resolve();

        Transaction::with('plan', 'user')
            ->where('user_id', $effectiveUser->id)
            ->where('status', PaymentStatus::Pending)
            ->whereNotNull('payment_gateway_id')
            ->where('created_at', '>=', now()->subHours(24))
            ->each(fn ($t) => $this->activationService->verifyAndActivate($t));

        $sub  = $effectiveUser->fresh()->activeSubscription;
        $plan = $sub?->plan;

        $premiumPlan = Plan::where('slug', 'premium')->first();

        return Inertia::render('Dashboard/Paket', [
            'currentPlan' => $plan ? [
                'name'           => $plan->name,
                'slug'           => $plan->slug,
                'is_premium'     => $plan->slug === 'premium',
                'is_lifetime'    => $sub !== null && $sub->expires_at === null,
                'expires_at'     => $sub?->expires_at?->format('d M Y'),
                'days_remaining' => ($sub && $sub->expires_at !== null) ? $sub->daysRemaining() : null,
            ] : [
                'name'           => 'Gratis',
                'slug'           => 'free',
                'is_premium'     => false,
                'is_lifetime'    => false,
                'expires_at'     => null,
                'days_remaining' => null,
            ],
            'premiumPlan' => $premiumPlan ? [
                'price'            => $premiumPlan->effectivePrice(),
                'original_price'   => $premiumPlan->hasVisibleDiscount() ? (int) $premiumPlan->original_price : null,
                'duration_days'    => $premiumPlan->duration_days,
                'has_discount'     => $premiumPlan->hasVisibleDiscount(),
                'discount_percent' => $premiumPlan->discountPercentOff(),
            ] : null,
            // Set when arriving from onboarding with a Premium choice → auto-start checkout.
            'autoCheckout' => $request->query('checkout') === 'premium',
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();
        $plan = Plan::where('slug', 'premium')->firstOrFail();

        $sub = $user->activeSubscription;
        if ($sub && $sub->plan->slug === 'premium' && $sub->isActive()) {
            // Lifetime (expires_at null) never needs renewal — block outright.
            if ($sub->expires_at === null) {
                return response()->json(['error' => 'Paket Premium kamu sudah aktif selamanya.'], 422);
            }
            // A term-based sub can still renew within its last 14 days.
            if ($sub->daysRemaining() > 14) {
                return response()->json(['error' => 'Paket kamu masih aktif lebih dari 14 hari.'], 422);
            }
        }

        $existing = Transaction::where('user_id', $user->id)
            ->where('status', PaymentStatus::Pending)
            ->where('plan_id', $plan->id)
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if ($existing) {
            $transaction = $existing;
        } else {
            $transaction = Transaction::create([
                'user_id'        => $user->id,
                'plan_id'        => $plan->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'amount'         => $plan->effectivePrice(),
                'payment_method' => PaymentMethod::Mayar,
                'status'         => PaymentStatus::Pending,
            ]);
        }

        try {
            $discountSuffix = $plan->hasActiveDiscount()
                ? " - Diskon {$plan->currentDiscount()->percent}%"
                : '';
            $durationLabel = $plan->duration_days > 0 ? "{$plan->duration_days} hari" : 'selamanya';
            $itemName = "Paket {$plan->name} TheDay ({$durationLabel}){$discountSuffix}";
            $result = $this->mayarService->createInvoice($transaction, $user, $itemName);
            $transaction->update(['payment_gateway_id' => $result['mayar_invoice_id']]);

            return response()->json(['payment_url' => $result['payment_url']]);
        } catch (\Exception $e) {
            Log::error('Mayar checkout failed', [
                'error'          => $e->getMessage(),
                'transaction_id' => $transaction->id,
                'user_id'        => $user->id,
            ]);

            return response()->json(['error' => 'Gagal memproses pembayaran. Silakan coba lagi.'], 500);
        }
    }

    public function invoice(Transaction $transaction): \Illuminate\Http\Response
    {
        if ($transaction->user_id !== EffectiveUser::resolve()->id) {
            abort(403);
        }

        return response()->view('invoices.show', [
            'transaction' => $transaction->load('plan', 'user'),
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $today = now()->format('Ymd');
        $count = Transaction::whereDate('created_at', today())->count() + 1;
        return 'INV-' . $today . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }
}
