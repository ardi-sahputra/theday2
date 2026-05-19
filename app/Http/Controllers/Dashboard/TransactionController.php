<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentActivationService;
use App\Support\EffectiveUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
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

        $transactions = Transaction::where('user_id', $effectiveUser->id)
            ->with('plan')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($t) => [
                'id'             => $t->id,
                'invoice_number' => $t->invoice_number,
                'type'           => $t->isAddonPurchase() ? 'addon' : 'paket',
                'type_label'     => $t->isAddonPurchase() ? 'Add-on' : 'Paket',
                'plan_name'      => $t->isAddonPurchase()
                    ? 'Tambah Undangan ×' . $t->addon_quantity
                    : ($t->plan?->name ?? '—'),
                'amount_fmt'     => 'Rp ' . number_format((int) $t->amount, 0, ',', '.'),
                'payment_method' => PaymentMethod::tryFrom($t->getRawOriginal('payment_method') ?? '')?->label()
                    ?? ucfirst($t->getRawOriginal('payment_method') ?? ''),
                'status'         => PaymentStatus::tryFrom($t->getRawOriginal('status') ?? '')?->value
                    ?? ($t->getRawOriginal('status') ?? 'pending'),
                'status_label'   => PaymentStatus::tryFrom($t->getRawOriginal('status') ?? '')?->label()
                    ?? ucfirst($t->getRawOriginal('status') ?? ''),
                'created_at'     => $t->created_at->format('d M Y'),
            ]);

        return Inertia::render('Dashboard/Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }
}
