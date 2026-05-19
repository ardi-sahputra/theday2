<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Mail\PartnerInviteMail;
use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_partner(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'rizki@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status', 'partner-invited');

        $this->assertDatabaseHas('couple_links', [
            'owner_id'      => $owner->id,
            'invited_email' => 'rizki@example.com',
            'status'        => CoupleLink::STATUS_PENDING,
        ]);
        Mail::assertSent(PartnerInviteMail::class, fn ($m) => $m->hasTo('rizki@example.com'));
    }

    public function test_email_normalized_to_lowercase(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'Rizki@Example.COM']);

        $this->assertDatabaseHas('couple_links', [
            'owner_id'      => $owner->id,
            'invited_email' => 'rizki@example.com',
        ]);
    }

    public function test_owner_cannot_invite_self(): void
    {
        $owner = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'me@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_owner_with_active_partner_cannot_invite_again(): void
    {
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->active()->create();

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'someoneelse@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_owner_with_pending_invite_cannot_invite_again(): void
    {
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->pending()->create();

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'other@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_cannot_invite_user_already_linked_elsewhere(): void
    {
        $existingOwner = User::factory()->create();
        $alreadyLinked = User::factory()->create(['email' => 'taken@example.com']);
        CoupleLink::factory()
            ->for($existingOwner, 'owner')
            ->for($alreadyLinked, 'partner')
            ->active()
            ->create();

        $newOwner = User::factory()->create();

        $this->actingAs($newOwner)
            ->post('/couple/invite', ['email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');
    }
}
