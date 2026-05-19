<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\EffectiveUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        EffectiveUser::clearCache();
    }

    protected function tearDown(): void
    {
        EffectiveUser::clearCache();
        parent::tearDown();
    }

    public function test_partner_in_app_feed_shows_owner_notifications(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        UserNotification::factory()->for($owner)->create(['title' => 'RSVP baru penting']);

        $this->actingAs($partner)
            ->getJson('/api/notifications/recent')
            ->assertOk()
            ->assertJsonFragment(['title' => 'RSVP baru penting']);
    }
}
