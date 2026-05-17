<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()
            ->withCount('invitations')
            ->with(['subscriptions' => fn ($q) => $q->select('id', 'user_id', 'status', 'expires_at')
                ->where('status', 'active')
                ->latest()]);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($plan = $request->string('plan')->toString()) {
            match ($plan) {
                'free'    => $query->doesntHave('subscriptions'),
                'premium' => $query->whereHas('subscriptions', fn ($s) => $s->where('status', 'active')),
                'expired' => $query->whereHas('subscriptions', fn ($s) => $s->where('status', 'expired')),
                default   => null,
            };
        }

        $sort = $request->string('sort', 'newest')->toString();
        match ($sort) {
            'oldest'             => $query->oldest(),
            'name'               => $query->orderBy('name'),
            'most-invitations'   => $query->orderByDesc('invitations_count'),
            default              => $query->latest(),
        };

        return Inertia::render('Admin/Users/Index', [
            'users'   => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'plan', 'sort']),
        ]);
    }

    public function show(User $user): Response
    {
        $user->load([
            'subscriptions:id,user_id,plan_id,status,expires_at',
            'subscriptions.plan:id,name,slug',
            'invitations:id,user_id,title,slug,status,created_at',
            'transactions:id,user_id,amount,status,created_at',
        ]);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    public function grantPremium(
        \App\Http\Requests\Admin\GrantPremiumRequest $request,
        User $user,
        \App\Services\SubscriptionOverrideService $svc,
    ): \Illuminate\Http\RedirectResponse {
        $svc->grantPremium($user, (int) $request->input('months'), $request->input('reason'));
        return back()->with('success', 'Premium granted.');
    }

    public function revokePremium(
        User $user,
        \App\Services\SubscriptionOverrideService $svc,
    ): \Illuminate\Http\RedirectResponse {
        $svc->revokePremium($user);
        return back()->with('success', 'Premium revoked.');
    }
}
