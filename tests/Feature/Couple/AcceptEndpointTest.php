<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Mail\PartnerLinkedMail;
use App\Models\CoupleLink;
use App\Models\User;
use App\Support\CoupleToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AcceptEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingLink(User $owner, string $email = 'partner@example.com'): array
    {
        $token = CoupleToken::generate();
        $link  = CoupleLink::factory()
            ->for($owner, 'owner')
            ->pending()
            ->create([
                'invited_email' => $email,
                'token_hash'    => CoupleToken::hash($token),
                'invited_at'    => now(),
            ]);
        return [$token, $link];
    }

    public function test_get_accept_shows_landing_page_for_unauthenticated(): void
    {
        $owner = User::factory()->create();
        [$token] = $this->makePendingLink($owner);

        $this->get("/couple/accept/{$token}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Couple/Accept')
                ->where('token', $token)
                ->where('ownerName', $owner->name)
                ->where('email', 'partner@example.com'));
    }

    public function test_get_accept_404_on_unknown_token(): void
    {
        $this->get('/couple/accept/' . CoupleToken::generate())
            ->assertNotFound();
    }

    public function test_get_accept_410_when_expired(): void
    {
        $owner = User::factory()->create();
        $token = CoupleToken::generate();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->pending()
            ->create([
                'token_hash' => CoupleToken::hash($token),
                'invited_at' => now()->subDays(8),
            ]);

        $this->get("/couple/accept/{$token}")
            ->assertStatus(410);
    }

    public function test_authenticated_partner_can_post_to_accept(): void
    {
        Mail::fake();
        $owner   = User::factory()->create();
        [$token] = $this->makePendingLink($owner, 'partner@example.com');
        $partner = User::factory()->create(['email' => 'partner@example.com']);

        $this->actingAs($partner)
            ->post("/couple/accept/{$token}")
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('couple_links', [
            'owner_id'   => $owner->id,
            'partner_id' => $partner->id,
            'status'     => CoupleLink::STATUS_ACTIVE,
        ]);
        Mail::assertSent(PartnerLinkedMail::class, fn ($m) => $m->hasTo($owner->email));
    }

    public function test_accept_rejects_mismatched_email(): void
    {
        $owner   = User::factory()->create();
        [$token] = $this->makePendingLink($owner, 'partner@example.com');
        $someoneElse = User::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($someoneElse)
            ->post("/couple/accept/{$token}")
            ->assertStatus(403);

        $this->assertDatabaseHas('couple_links', [
            'owner_id'   => $owner->id,
            'partner_id' => null,
            'status'     => CoupleLink::STATUS_PENDING,
        ]);
    }

    public function test_concurrent_accept_does_not_duplicate(): void
    {
        $owner   = User::factory()->create();
        [$token] = $this->makePendingLink($owner, 'partner@example.com');
        $partner = User::factory()->create(['email' => 'partner@example.com']);

        // Simulate race: lock-and-mutate inside a transaction first, then attempt second accept.
        DB::transaction(function () use ($token) {
            CoupleLink::where('token_hash', CoupleToken::hash($token))
                ->lockForUpdate()
                ->first()
                ->update([
                    'status'     => CoupleLink::STATUS_ACTIVE,
                    'partner_id' => \App\Models\User::factory()->create()->id,
                    'linked_at'  => now(),
                ]);
        });

        $this->actingAs($partner)
            ->post("/couple/accept/{$token}")
            ->assertStatus(409);
    }
}
