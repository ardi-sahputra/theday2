<?php

namespace App\Providers;

use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Models\Subscription;
use App\Observers\GuestMessageNotificationObserver;
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
    }
}
