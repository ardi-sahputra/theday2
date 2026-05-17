<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\NotificationType;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GiftObserverTest extends TestCase
{
    use RefreshDatabase;

    private function premiumPlan(): Plan
    {
        return Plan::where('slug', 'premium')->first()
            ?? Plan::factory()->premium()->create();
    }

    public function test_gift_received_notification_when_recipient_user_exists(): void
    {
        $sender    = User::factory()->create();
        $recipient = User::factory()->create(['email' => 'rcpt@example.test']);

        Gift::create([
            'code'            => 'GIFT-' . Str::upper(Str::random(10)),
            'sender_user_id'  => $sender->id,
            'plan_id'         => $this->premiumPlan()->id,
            'recipient_email' => 'rcpt@example.test',
            'delivery_mode'   => 'email',
            'source'          => 'user',
            'duration_days'   => 365,
            'amount'          => 99000,
            'status'          => 'pending',
            'expires_at'      => now()->addDays(30),
        ]);

        $notif = UserNotification::where('user_id', $recipient->id)->first();
        $this->assertNotNull($notif);
        $this->assertSame(NotificationType::GiftReceived->value, $notif->type->value);
    }

    public function test_gift_claimed_notification_to_sender(): void
    {
        $sender    = User::factory()->create();
        $claimer   = User::factory()->create();

        $gift = Gift::create([
            'code'            => 'GIFT-' . Str::upper(Str::random(10)),
            'sender_user_id'  => $sender->id,
            'plan_id'         => $this->premiumPlan()->id,
            'recipient_email' => null,
            'delivery_mode'   => 'link',
            'source'          => 'user',
            'duration_days'   => 365,
            'amount'          => 99000,
            'status'          => 'pending',
            'expires_at'      => now()->addDays(30),
        ]);

        $gift->update(['status' => 'claimed', 'claimed_by_user_id' => $claimer->id, 'claimed_at' => now()]);

        $notif = UserNotification::where('user_id', $sender->id)
            ->where('type', NotificationType::GiftClaimed->value)
            ->first();
        $this->assertNotNull($notif);
    }

    public function test_gift_expired_notification_to_sender(): void
    {
        $sender = User::factory()->create();

        $gift = Gift::create([
            'code'            => 'GIFT-' . Str::upper(Str::random(10)),
            'sender_user_id'  => $sender->id,
            'plan_id'         => $this->premiumPlan()->id,
            'recipient_email' => 'someone@example.test',
            'delivery_mode'   => 'link',
            'source'          => 'user',
            'duration_days'   => 30,
            'amount'          => 99000,
            'status'          => 'pending',
            'expires_at'      => now()->addDays(30),
        ]);

        $gift->update(['status' => 'expired']);

        $notif = UserNotification::where('user_id', $sender->id)
            ->where('type', NotificationType::GiftExpired->value)
            ->first();
        $this->assertNotNull($notif);
    }
}
