<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Mail\PartnerInviteMail;
use App\Mail\PartnerRevokedMail;
use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RevokeUnlinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_revoke_active_partner(): void
    {
        Mail::fake();
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        $link    = CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        $this->actingAs($owner)
            ->delete('/couple/revoke')
            ->assertRedirect();

        // Row is deleted entirely so unique slots free up for re-linking
        $this->assertDatabaseMissing('couple_links', ['id' => $link->id]);
        Mail::assertSent(PartnerRevokedMail::class, fn ($m) => $m->hasTo($partner->email));
    }

    public function test_owner_can_cancel_pending_invite(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $link  = CoupleLink::factory()->for($owner, 'owner')->pending()->create();

        $this->actingAs($owner)
            ->delete('/couple/revoke')
            ->assertRedirect();

        $this->assertDatabaseMissing('couple_links', ['id' => $link->id]);
    }

    public function test_partner_can_unlink_themselves(): void
    {
        Mail::fake();
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        $link    = CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        $this->actingAs($partner)
            ->delete('/couple/unlink')
            ->assertRedirect();

        $this->assertDatabaseMissing('couple_links', ['id' => $link->id]);
    }

    public function test_owner_can_resend_pending_invite_after_cooldown(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->pending()->create([
            'invited_at' => now()->subMinutes(6),
        ]);

        $this->actingAs($owner)
            ->post('/couple/invite/resend')
            ->assertRedirect()
            ->assertSessionHas('status', 'partner-invite-resent');

        Mail::assertSent(PartnerInviteMail::class);
    }

    public function test_resend_blocked_during_cooldown(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->pending()->create([
            'invited_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($owner)
            ->post('/couple/invite/resend')
            ->assertSessionHasErrors();

        Mail::assertNothingSent();
    }

    public function test_revoke_with_no_link_returns_404(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->delete('/couple/revoke')->assertNotFound();
    }

    public function test_unlink_with_no_active_link_returns_404(): void
    {
        $partner = User::factory()->create();
        $this->actingAs($partner)->delete('/couple/unlink')->assertNotFound();
    }
}
