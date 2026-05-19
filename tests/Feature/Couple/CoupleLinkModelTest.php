<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoupleLinkModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_owner_and_partner(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();

        $link = CoupleLink::factory()
            ->for($owner,   'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        $this->assertTrue($link->owner->is($owner));
        $this->assertTrue($link->partner->is($partner));
    }

    public function test_owner_id_is_unique(): void
    {
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->pending()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);
        CoupleLink::factory()->for($owner, 'owner')->pending()->create();
    }

    public function test_partner_id_is_unique_when_set(): void
    {
        $partner = User::factory()->create();
        CoupleLink::factory()->for($partner, 'partner')->active()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);
        CoupleLink::factory()->for($partner, 'partner')->active()->create();
    }

    public function test_factory_pending_state_has_no_partner(): void
    {
        $link = CoupleLink::factory()->pending()->create();

        $this->assertSame('pending', $link->status);
        $this->assertNull($link->partner_id);
        $this->assertNull($link->linked_at);
    }

    public function test_is_expired_returns_false_for_fresh_pending_link(): void
    {
        $link = CoupleLink::factory()->pending()->create(['invited_at' => now()]);

        $this->assertFalse($link->isExpired());
    }

    public function test_is_expired_returns_true_when_pending_older_than_ttl(): void
    {
        $link = CoupleLink::factory()->pending()->create([
            'invited_at' => now()->subDays(CoupleLink::INVITE_TTL_DAYS + 1),
        ]);

        $this->assertTrue($link->isExpired());
    }

    public function test_is_expired_returns_false_for_non_pending_status(): void
    {
        $link = CoupleLink::factory()->active()->create([
            'invited_at' => now()->subDays(CoupleLink::INVITE_TTL_DAYS + 5),
        ]);

        $this->assertFalse($link->isExpired());
    }
}
