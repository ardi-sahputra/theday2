<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\Gift\GiftAlreadyClaimedException;
use App\Exceptions\Gift\GiftAwaitingPaymentException;
use App\Exceptions\Gift\GiftExpiredException;
use App\Mail\GiftClaimedNotificationMail;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\GiftClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GiftClaimServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_grants_premium_to_new_recipient_and_marks_gift_claimed(): void
    {
        Mail::fake();
        $plan = Plan::factory()->premium()->create(['duration_days' => 90]);
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
            'duration_days'  => 90,
            'status'         => 'pending',
            'expires_at'     => now()->addDay(),
        ]);

        $service = app(GiftClaimService::class);
        $sub = $service->claim($gift, $recipient);

        $gift->refresh();
        $this->assertSame('claimed', $gift->status);
        $this->assertSame($recipient->id, $gift->claimed_by_user_id);
        $this->assertNotNull($gift->claimed_at);

        $this->assertSame($recipient->id, $sub->user_id);
        $this->assertEqualsWithDelta(now()->addDays(90)->timestamp, $sub->expires_at->timestamp, 5);

        Mail::assertQueued(GiftClaimedNotificationMail::class);
    }

    public function test_claim_extends_existing_subscription(): void
    {
        Mail::fake();
        $plan = Plan::factory()->premium()->create(['duration_days' => 90]);
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        Subscription::factory()->create([
            'user_id'    => $recipient->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'expires_at' => now()->addDays(10),
        ]);
        $gift = Gift::factory()->create([
            'plan_id'       => $plan->id,
            'duration_days' => 90,
            'status'        => 'pending',
            'expires_at'    => now()->addDay(),
        ]);

        $service = app(GiftClaimService::class);
        $sub = $service->claim($gift, $recipient);

        $this->assertEqualsWithDelta(now()->addDays(100)->timestamp, $sub->expires_at->timestamp, 5);
    }

    public function test_claim_throws_when_already_claimed(): void
    {
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->claimed()->create();

        $this->expectException(GiftAlreadyClaimedException::class);
        app(GiftClaimService::class)->claim($gift, $recipient);
    }

    public function test_claim_throws_when_expired(): void
    {
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->expired()->create();

        $this->expectException(GiftExpiredException::class);
        app(GiftClaimService::class)->claim($gift, $recipient);
    }

    public function test_claim_throws_when_awaiting_payment(): void
    {
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->awaitingPayment()->create();

        $this->expectException(GiftAwaitingPaymentException::class);
        app(GiftClaimService::class)->claim($gift, $recipient);
    }

    public function test_claim_throws_when_pending_but_past_expires_at(): void
    {
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->create([
            'status'     => 'pending',
            'expires_at' => now()->subMinute(),
        ]);

        $this->expectException(GiftExpiredException::class);
        app(GiftClaimService::class)->claim($gift, $recipient);
    }

    public function test_claim_does_not_send_notification_for_admin_source(): void
    {
        Mail::fake();
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->admin()->create([
            'status'     => 'pending',
            'expires_at' => now()->addDay(),
        ]);

        app(GiftClaimService::class)->claim($gift, $recipient);

        Mail::assertNothingQueued();
    }
}
