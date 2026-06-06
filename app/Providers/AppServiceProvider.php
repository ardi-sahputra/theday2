<?php

namespace App\Providers;

use App\Models\Gift;
use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Models\InvitationView;
use App\Models\Rsvp;
use App\Models\Subscription;
use App\Observers\GiftNotificationObserver;
use App\Observers\GuestMessageNotificationObserver;
use App\Observers\InvitationViewNotificationObserver;
use App\Observers\RsvpNotificationObserver;
use App\Observers\SubscriptionObserver;
use App\Policies\InvitationPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * Note: Sanctum v4 uses publishesMigrations() (not loadMigrationsFrom), so its
     * bigint migration is never auto-loaded. No ignoreMigrations() call needed.
     * We ship our own 2026_06_05_000001_create_personal_access_tokens_table.php
     * with uuidMorphs because User uses HasUuids.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(Invitation::class, InvitationPolicy::class);

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        Subscription::observe(SubscriptionObserver::class);
        GuestMessage::observe(GuestMessageNotificationObserver::class);
        Rsvp::observe(RsvpNotificationObserver::class);
        Gift::observe(GiftNotificationObserver::class);
        InvitationView::observe(InvitationViewNotificationObserver::class);
    }
}
