<?php
// tests/Feature/Dashboard/InvitationCustomizeV2Test.php
declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InvitationCustomizeV2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Inertia first-load renders app.blade.php which resolves the page
        // component against the Vite manifest. CustomizeV2.vue is built by a
        // later plan task, so skip Vite to assert the Inertia props directly.
        $this->withoutVite();

        // The Vue page file (CustomizeV2.vue) ships in a later plan task; this
        // backend test only asserts the controller props, so don't require the
        // page component to exist on disk.
        config(['inertia.testing.ensure_pages_exist' => false]);
    }

    public function test_owner_sees_v2_editor_with_required_props(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $inv  = Invitation::factory()->for($user)->create();

        $this->actingAs($user)
            ->get("/dashboard/invitations/{$inv->id}/customize-v2")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Invitations/CustomizeV2')
                ->has('invitation.template_id')
                ->has('invitation.template_slug')
                ->has('templates')
                ->has('defaultMusic')
                ->where('canUsePremium', fn ($v) => is_bool($v))
            );
    }

    public function test_non_owner_is_forbidden(): void
    {
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $other   = User::factory()->create(['onboarding_completed_at' => now()]);
        $inv     = Invitation::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get("/dashboard/invitations/{$inv->id}/customize-v2")
            ->assertForbidden();
    }
}
