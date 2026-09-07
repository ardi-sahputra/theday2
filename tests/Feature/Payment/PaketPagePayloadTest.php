<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Paket dashboard page must tell a lifetime subscriber apart from one
 * with a real term — days_remaining defaults to 0 for a null expires_at
 * (Subscription::daysRemaining()), which would otherwise read as "about to
 * expire" instead of "never expires". See PaymentActivationServiceLifetimeTest
 * for the activation side of this.
 */
class PaketPagePayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_lifetime_subscription_reports_null_days_remaining_and_is_lifetime_true(): void
    {
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 49000, 'duration_days' => 0]);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/dashboard/paket')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('currentPlan.is_premium', true)
                ->where('currentPlan.is_lifetime', true)
                ->where('currentPlan.days_remaining', null)
            );
    }

    public function test_term_subscription_still_reports_days_remaining(): void
    {
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard/paket')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('currentPlan.is_lifetime', false));

        // 29 or 30 depending on how much wall-clock time elapsed inside the
        // request — never null, and never the misleading "0" a lifetime sub
        // would report.
        $daysRemaining = $response->viewData('page')['props']['currentPlan']['days_remaining'];
        $this->assertContains($daysRemaining, [29, 30]);
    }

    public function test_free_user_is_not_lifetime(): void
    {
        Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 49000, 'duration_days' => 0]);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $this->actingAs($user)
            ->get('/dashboard/paket')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('currentPlan.is_premium', false)
                ->where('currentPlan.is_lifetime', false)
            );
    }
}
