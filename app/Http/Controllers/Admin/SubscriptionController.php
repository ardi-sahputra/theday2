<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionOverrideService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\ExtendSubscriptionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Subscription::with(['user:id,name,email', 'plan:id,name,slug']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $query->latest()->paginate(25)->withQueryString(),
            'filters'       => $request->only(['status']),
            'stats'         => [
                'active'  => Subscription::where('status', 'active')->count(),
                'grace'   => Subscription::where('status', 'grace')->count(),
                'expired' => Subscription::where('status', 'expired')->count(),
            ],
        ]);
    }

    public function extend(
        ExtendSubscriptionRequest $request,
        Subscription $sub,
        SubscriptionOverrideService $svc,
    ): RedirectResponse {
        $months = (int) $request->input('months');
        $svc->extend($sub, $months);
        return back()->with('success', "Extended by {$months} month(s).");
    }

    public function cancel(
        Subscription $sub,
        SubscriptionOverrideService $svc,
    ): RedirectResponse {
        $svc->cancel($sub);
        return back()->with('success', 'Subscription cancelled.');
    }
}
