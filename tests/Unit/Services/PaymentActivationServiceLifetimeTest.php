<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\InvitationAddon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Premium ships as duration_days = 0 (lifetime, same convention as the Free
 * plan): pay once, the subscription never expires. These tests lock that
 * behavior in — see the conversation that added it for context.
 */
class PaymentActivationServiceLifetimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_activate_premium_with_zero_duration_never_expires(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 49000, 'duration_days' => 0]);
        $transaction = Transaction::create([
            'user_id'        => $user->id,
            'plan_id'        => $plan->id,
            'invoice_number' => 'INV-LIFETIME-001',
            'amount'         => 49000,
            'payment_method' => PaymentMethod::Mayar,
            'status'         => PaymentStatus::Pending,
        ]);

        app(PaymentActivationService::class)->activatePremium($transaction);

        $sub = Subscription::where('user_id', $user->id)->where('status', 'active')->first();
        $this->assertNotNull($sub);
        $this->assertNull($sub->expires_at);
        $this->assertTrue($sub->isActive());
        $this->assertSame(0, $sub->daysRemaining());
    }

    public function test_activate_premium_with_positive_duration_still_expires(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 35000, 'duration_days' => 90]);
        $transaction = Transaction::create([
            'user_id'        => $user->id,
            'plan_id'        => $plan->id,
            'invoice_number' => 'INV-TERM-001',
            'amount'         => 35000,
            'payment_method' => PaymentMethod::Mayar,
            'status'         => PaymentStatus::Pending,
        ]);

        app(PaymentActivationService::class)->activatePremium($transaction);

        $sub = Subscription::where('user_id', $user->id)->where('status', 'active')->first();
        $this->assertNotNull($sub->expires_at);
        $this->assertEqualsWithDelta(now()->addDays(90)->timestamp, $sub->expires_at->timestamp, 5);
    }

    public function test_addon_bought_on_lifetime_subscription_never_expires(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Premium', 'slug' => 'premium', 'price' => 49000, 'duration_days' => 0]);

        $premiumTxn = Transaction::create([
            'user_id'        => $user->id,
            'plan_id'        => $plan->id,
            'invoice_number' => 'INV-LIFETIME-002',
            'amount'         => 49000,
            'payment_method' => PaymentMethod::Mayar,
            'status'         => PaymentStatus::Pending,
        ]);
        $service = app(PaymentActivationService::class);
        $service->activatePremium($premiumTxn);

        $subscription = Subscription::where('user_id', $user->id)->firstOrFail();

        $addonTxn = Transaction::create([
            'user_id'         => $user->id,
            'plan_id'         => null,
            'subscription_id' => $subscription->id,
            'addon_quantity'  => 2,
            'invoice_number'  => 'INV-ADDON-001',
            'amount'          => 30000,
            'payment_method'  => PaymentMethod::Mayar,
            'status'          => PaymentStatus::Pending,
        ]);
        $service->activateAddon($addonTxn);

        $addon = InvitationAddon::where('user_id', $user->id)->firstOrFail();
        $this->assertNull($addon->expires_at);
        $this->assertTrue($addon->isActive());

        // Regression guard: quota queries filtering `expires_at > now()` must
        // not silently drop lifetime (null-expiry) addons.
        $this->assertSame(2, (int) $user->invitationAddons()->active()->sum('quantity'));
    }
}
