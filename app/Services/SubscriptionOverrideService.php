<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionOverrideService
{
    /**
     * Grant premium access to a user for the given number of months.
     *
     * If the user already has an active subscription, its expires_at is
     * extended from the current expiry date. Otherwise a new subscription
     * is created starting from now.
     */
    public function grantPremium(User $user, int $months, ?string $reason = null): Subscription
    {
        $existing = $user->activeSubscription;

        if ($existing) {
            // Extend from the current expiry
            $startFrom = $existing->expires_at?->isFuture()
                ? $existing->expires_at
                : now();

            $expiresAt = Carbon::parse($startFrom)->addMonths($months);

            $existing->update([
                'status'     => 'active',
                'expires_at' => $expiresAt,
            ]);

            return $existing->fresh();
        }

        // No active subscription — create a new one
        $premiumPlan = Plan::where('slug', 'premium')->firstOrFail();

        return Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $premiumPlan->id,
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at' => now()->addMonths($months),
        ]);
    }

    /**
     * Grant premium access for an exact number of days (snapshot-friendly).
     * Behavior mirrors grantPremium but uses days for precise scheduling.
     */
    public function grantPremiumDays(User $user, int $days): Subscription
    {
        $existing = $user->activeSubscription;

        if ($existing) {
            $startFrom = $existing->expires_at?->isFuture()
                ? $existing->expires_at
                : now();

            $expiresAt = Carbon::parse($startFrom)->addDays($days);

            $existing->update([
                'status'     => 'active',
                'expires_at' => $expiresAt,
            ]);

            return $existing->fresh();
        }

        $premiumPlan = Plan::where('slug', 'premium')->firstOrFail();

        return Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $premiumPlan->id,
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at' => now()->addDays($days),
        ]);
    }

    /**
     * Revoke premium access immediately by setting expires_at to now.
     * The subscription becomes inactive on the next activeSubscription query.
     */
    public function revokePremium(User $user): void
    {
        $sub = $user->activeSubscription;

        if (! $sub) {
            return;
        }

        $sub->update(['expires_at' => now()]);
    }

    /**
     * Extend an existing subscription by the given number of months.
     * If expires_at is in the past, extension starts from now.
     */
    public function extend(Subscription $sub, int $months): Subscription
    {
        $base = $sub->expires_at?->isFuture()
            ? $sub->expires_at
            : now();

        $sub->update([
            'expires_at' => Carbon::parse($base)->addMonths($months),
            'status'     => 'active',
        ]);

        return $sub->fresh();
    }

    /**
     * Cancel a subscription immediately.
     */
    public function cancel(Subscription $sub): void
    {
        $sub->update([
            'status'     => 'cancelled',
            'expires_at' => now(),
        ]);
    }
}
