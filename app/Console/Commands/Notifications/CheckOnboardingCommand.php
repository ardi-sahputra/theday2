<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\Invitation;
use App\Models\User;
use App\Models\WeddingPlan;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckOnboardingCommand extends Command
{
    protected $signature = 'notifications:check-onboarding';

    protected $description = 'Onboarding & warning notifications (weekly, cooldown 7 days)';

    public function handle(NotificationPublisher $publisher): int
    {
        $cooldown = (int) config('notifications.cooldown.onboarding_days', 7);

        // 1. Couple profile incomplete (no row OR missing groom/bride name).
        User::query()
            ->whereDoesntHave('coupleProfile')
            ->orWhereHas('coupleProfile', function ($q) {
                $q->whereNull('groom_name')->orWhereNull('bride_name');
            })
            ->chunkById(200, function ($users) use ($publisher, $cooldown) {
                foreach ($users as $user) {
                    $publisher->publish(
                        user: $user,
                        type: NotificationType::ProfileIncomplete,
                        payload: [],
                        groupKey: 'onboarding:profile_incomplete',
                        actionUrl: '/dashboard/profile',
                        cooldownDays: $cooldown,
                    );
                }
            });

        // 2. Invitation near D-day but unpublished.
        //    WeddingPlan holds event_date and is linked 1-to-1 to user; Invitation belongs to user
        //    via user_id. Use WeddingPlan as the d-day source and look up the user's invitations.
        WeddingPlan::query()
            ->whereNotNull('event_date')
            ->where('event_date', '>=', Carbon::today())
            ->where('event_date', '<=', Carbon::today()->addDays(14))
            ->with('user')
            ->chunkById(200, function ($plans) use ($publisher, $cooldown) {
                foreach ($plans as $plan) {
                    if (! $plan->user) {
                        continue;
                    }
                    $invitation = Invitation::query()
                        ->where('user_id', $plan->user->id)
                        ->whereNull('published_at')
                        ->first();
                    if ($invitation === null) {
                        continue;
                    }
                    $days = max(0, (int) Carbon::today()->diffInDays(Carbon::parse($plan->event_date), false));
                    $publisher->publish(
                        user: $plan->user,
                        type: NotificationType::InvitationUnpublishedNearDday,
                        payload: ['days' => $days],
                        groupKey: 'onboarding:unpublished:' . $invitation->id,
                        actionUrl: '/dashboard/invitations/' . $invitation->id,
                        cooldownDays: $cooldown,
                    );
                }
            });

        return self::SUCCESS;
    }
}
