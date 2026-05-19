<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreGiftRequest;
use App\Models\Gift;
use App\Models\Plan;
use App\Services\GiftPurchaseService;
use App\Support\EffectiveUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class GiftController extends Controller
{
    public function __construct(private readonly GiftPurchaseService $purchaseService) {}

    public function index(): Response
    {
        $gifts = Gift::with('plan', 'claimedBy')
            ->where('sender_user_id', EffectiveUser::resolve()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Dashboard/Gifts/Index', ['gifts' => $gifts]);
    }

    public function create(): Response
    {
        $plan = Plan::where('slug', 'premium')->firstOrFail();
        $discount = $plan->currentDiscount();

        return Inertia::render('Dashboard/Gifts/Create', [
            'plan' => [
                'id'               => $plan->id,
                'name'             => $plan->name,
                'duration_days'    => $plan->duration_days,
                'price'            => (int) $plan->price,
                'effective_price'  => $plan->effectivePrice(),
                'has_discount'     => $discount !== null,
                'discount_percent' => $discount?->percent,
                'discount_label'   => $discount?->label,
            ],
        ]);
    }

    public function store(StoreGiftRequest $request): RedirectResponse
    {
        try {
            $result = $this->purchaseService->createUserGift($request->user(), $request->validated());
        } catch (\Throwable $e) {
            Log::error('Gift purchase failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', __('gift.flash.purchase_error'));
        }

        return redirect()->away($result['payment_url']);
    }

    public function show(Gift $gift): Response
    {
        if ($gift->sender_user_id !== EffectiveUser::resolve()->id) {
            abort(403);
        }

        return Inertia::render('Dashboard/Gifts/Show', [
            'gift' => [
                'id'              => $gift->id,
                'code'            => $gift->code,
                'plan_name'       => $gift->plan->name,
                'duration_days'   => $gift->duration_days,
                'amount'          => $gift->amount,
                'delivery_mode'   => $gift->delivery_mode,
                'recipient_email' => $gift->recipient_email,
                'message'         => $gift->message,
                'status'          => $gift->status,
                'claimed_at'      => $gift->claimed_at?->toIso8601String(),
                'claim_url'       => route('gift.claim.show', $gift->code),
                'expires_at'      => $gift->expires_at->toIso8601String(),
            ],
        ]);
    }
}
