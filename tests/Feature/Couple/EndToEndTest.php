<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Mail\PartnerInviteMail;
use App\Mail\PartnerLinkedMail;
use App\Models\CoupleLink;
use App\Models\Invitation;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_invite_to_shared_access_flow(): void
    {
        Mail::fake();
        Plan::create([
            'name' => 'Free', 'slug' => 'free', 'price' => 0, 'duration_days' => 0,
            'max_invitations' => 3, 'max_gallery_photos' => 10, 'custom_music' => false,
            'remove_watermark' => false, 'custom_domain' => false, 'analytics_access' => false,
            'is_active' => true, 'sort_order' => 0,
        ]);

        $owner    = User::factory()->create(['onboarding_completed_at' => now()]);
        $template = Template::factory()->create();
        Invitation::factory()->for($owner)->for($template)->create([
            'title' => 'Ardi & Rizki Wedding 99',
        ]);

        // 1. Owner invites partner
        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'rizki@example.com'])
            ->assertRedirect();

        Mail::assertSent(PartnerInviteMail::class);

        // 2. Recover the token from the dispatched mail
        $capturedToken = null;
        Mail::assertSent(PartnerInviteMail::class, function ($m) use (&$capturedToken) {
            $capturedToken = $m->token;
            return true;
        });
        $this->assertNotNull($capturedToken);

        // 3. Partner registers
        $this->post('/logout');
        $partnerData = [
            'name' => 'Rizki', 'phone' => '081234567890',
            'email' => 'rizki@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ];
        $this->post('/register', $partnerData)->assertRedirect();

        $partner = User::where('email', 'rizki@example.com')->firstOrFail();
        $partner->update(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);

        // 4. Partner accepts
        $this->actingAs($partner)
            ->post("/couple/accept/{$capturedToken}")
            ->assertRedirect();

        Mail::assertSent(PartnerLinkedMail::class);

        $this->assertDatabaseHas('couple_links', [
            'owner_id'   => $owner->id,
            'partner_id' => $partner->id,
            'status'     => CoupleLink::STATUS_ACTIVE,
        ]);

        // 5. Partner sees owner's invitation
        $this->actingAs($partner)
            ->get('/dashboard/invitations')
            ->assertOk()
            ->assertSee('Ardi & Rizki Wedding 99');
    }
}
