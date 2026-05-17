<?php

// app/Http/Middleware/HandleInertiaRequests.php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ChecklistTask;
use App\Models\WeddingPlan;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $locale = $request->header('X-Locale')
            ?? $request->cookie('locale')
            ?? config('app.locale');

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }
        app()->setLocale($locale);

        $translationsPath = lang_path("{$locale}.json");
        static $translationsCache = [];
        $translations = $translationsCache[$locale] ??= (function () use ($translationsPath) {
            if (! file_exists($translationsPath)) return [];
            return json_decode(file_get_contents($translationsPath), true) ?? [];
        })();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user instanceof \App\Models\User ? [
                    'id'                      => $user->id,
                    'name'                    => $user->name,
                    'email'                   => $user->email,
                    'avatar_url'              => $user->avatar_url,
                    'onboarding_completed'    => $user->hasCompletedOnboarding(),
                ] : null,
                'subscription' => ($user instanceof \App\Models\User) ? (function () use ($user) {
                    $sub = $user->activeSubscription;
                    if (! $sub) return null;
                    return [
                        'plan_name'           => $sub->plan->name,
                        'plan_slug'           => $sub->plan->slug,
                        'max_invitations'     => $sub->plan->max_invitations,
                        'status'              => $sub->status,
                        'remove_watermark'    => $sub->plan->remove_watermark,
                        'analytics_access'    => $sub->plan->analytics_access,
                        'custom_music'        => $sub->plan->custom_music,
                        'expires_at'          => $sub->expires_at?->format('d M Y'),
                        'days_remaining'      => $sub->daysRemaining(),
                        'in_grace_period'     => $sub->isInGracePeriod(),
                        'grace_days_remaining' => $sub->graceDaysRemaining(),
                    ];
                })() : null,
                'isGuest' => ! $user,
            ],
            'can_create_invitation' => fn () => ($user instanceof \App\Models\User) ? (function () use ($user) {
                $base   = $user->currentPlan()?->max_invitations
                    ?? \App\Models\Plan::where('slug', 'free')->value('max_invitations')
                    ?? 1;
                $addons = $user->invitationAddons()
                    ->where('expires_at', '>', now())
                    ->sum('quantity');
                return $user->invitations()->count() < ($base + $addons);
            })() : true,
            'checklist_todo' => fn () => ($user instanceof \App\Models\User)
                ? ChecklistTask::whereHas('weddingPlan', fn ($q) => $q->where('user_id', $user->id))
                    ->todo()
                    ->count()
                : 0,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'locale' => $locale,
            'translations' => $translations,
        ];
    }
}
