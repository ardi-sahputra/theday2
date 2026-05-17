<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminGiftRequest;
use App\Models\Gift;
use App\Models\Plan;
use App\Services\GiftPurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GiftController extends Controller
{
    public function __construct(private readonly GiftPurchaseService $purchaseService) {}

    public function index(): Response
    {
        $gifts = Gift::with('plan', 'sender', 'claimedBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Admin/Gifts/Index', ['gifts' => $gifts]);
    }

    public function create(): Response
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Admin/Gifts/Create', ['plans' => $plans]);
    }

    public function store(StoreAdminGiftRequest $request): RedirectResponse
    {
        $gift = $this->purchaseService->createAdminGift($request->validated());

        return redirect()->route('admin.gifts.show', $gift)
            ->with('success', "Gift code {$gift->code} berhasil dibuat.");
    }

    public function show(Gift $gift): Response
    {
        return Inertia::render('Admin/Gifts/Show', [
            'gift' => [
                'id'              => $gift->id,
                'code'            => $gift->code,
                'plan_name'       => $gift->plan->name,
                'duration_days'   => $gift->duration_days,
                'source'          => $gift->source,
                'delivery_mode'   => $gift->delivery_mode,
                'recipient_email' => $gift->recipient_email,
                'message'         => $gift->message,
                'status'          => $gift->status,
                'claimed_at'      => $gift->claimed_at?->toIso8601String(),
                'claimed_by'      => $gift->claimedBy?->email,
                'claim_url'       => route('gift.claim.show', $gift->code),
                'expires_at'      => $gift->expires_at->toIso8601String(),
            ],
        ]);
    }

    public function destroy(Gift $gift): RedirectResponse
    {
        if ($gift->status !== 'pending') {
            throw ValidationException::withMessages([
                'gift' => 'Hanya gift dengan status pending yang bisa dihapus.',
            ]);
        }

        $gift->delete();

        return redirect()->route('admin.gifts.index')->with('success', 'Gift dihapus.');
    }
}
