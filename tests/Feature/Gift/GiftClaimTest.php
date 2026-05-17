<?php

declare(strict_types=1);

namespace Tests\Feature\Gift;

use App\Mail\GiftClaimedNotificationMail;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GiftClaimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();
    }

    public function test_nonexistent_code_returns_404(): void
    {
        $this->get('/gift/claim/NOPE')->assertNotFound();
    }

    public function test_guest_sees_claimable_guest_state(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create(['plan_id' => $plan->id, 'status' => 'pending', 'expires_at' => now()->addDay()]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Gift/Claim')
                ->where('state', 'claimable_guest')
            );
    }

    public function test_logged_in_user_sees_claimable_authed_state(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create(['plan_id' => $plan->id, 'status' => 'pending', 'expires_at' => now()->addDay()]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'claimable_authed'));
    }

    public function test_claimed_gift_shows_already_claimed(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->claimed()->create(['plan_id' => $plan->id]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'already_claimed'));
    }

    public function test_expired_gift_shows_expired(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->expired()->create(['plan_id' => $plan->id]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'expired'));
    }

    public function test_awaiting_payment_gift_shows_awaiting(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->awaitingPayment()->create(['plan_id' => $plan->id]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'awaiting_payment'));
    }

    public function test_pending_gift_past_expires_at_shows_expired(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create([
            'plan_id'    => $plan->id,
            'status'     => 'pending',
            'expires_at' => now()->subMinute(),
        ]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'expired'));
    }

    public function test_guest_cannot_post_claim(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create(['plan_id' => $plan->id, 'status' => 'pending', 'expires_at' => now()->addDay()]);

        $this->post("/gift/claim/{$gift->code}")->assertRedirect('/login');
    }

    public function test_authed_user_claims_gift_successfully(): void
    {
        Mail::fake();
        $plan = Plan::where('slug', 'premium')->first();
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
            'status'         => 'pending',
            'duration_days'  => 90,
            'expires_at'     => now()->addDay(),
        ]);

        $this->actingAs($recipient)
            ->post("/gift/claim/{$gift->code}")
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('gifts', [
            'id'                 => $gift->id,
            'status'             => 'claimed',
            'claimed_by_user_id' => $recipient->id,
        ]);
        $this->assertSame(1, Subscription::where('user_id', $recipient->id)->count());
        Mail::assertQueued(GiftClaimedNotificationMail::class);
    }

    public function test_double_claim_returns_already_claimed_state(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->create([
            'plan_id'    => $plan->id,
            'status'     => 'pending',
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($recipient)->post("/gift/claim/{$gift->code}")->assertRedirect('/dashboard');
        // Second claim
        $this->actingAs($recipient)->get("/gift/claim/{$gift->code}")
            ->assertInertia(fn ($p) => $p->where('state', 'already_claimed'));
    }
}
