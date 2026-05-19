<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Authed probe — uses /dashboard prefix which is excluded from the
        // public-invitation wildcard, so it resolves to our test route.
        Route::middleware(['web', 'auth', 'couple'])
            ->get('/dashboard/_couple-probe', function (\Illuminate\Http\Request $request) {
                return [
                    'auth_id'         => auth()->id(),
                    'effective_id'    => $request->attributes->get('effective_user_id'),
                    'is_partner_mode' => $request->attributes->get('is_partner_mode'),
                ];
            });

        // Guest probe — same prefix, no auth required, tests the unauthenticated branch.
        Route::middleware(['web', 'couple'])
            ->get('/dashboard/_couple-probe-guest', function (\Illuminate\Http\Request $request) {
                return [
                    'auth_id'         => auth()->id(),
                    'effective_id'    => $request->attributes->get('effective_user_id'),
                    'is_partner_mode' => $request->attributes->get('is_partner_mode'),
                ];
            });
    }

    public function test_owner_with_no_link_sees_own_id(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->getJson('/dashboard/_couple-probe')
            ->assertOk()
            ->assertJson([
                'auth_id'         => $owner->id,
                'effective_id'    => $owner->id,
                'is_partner_mode' => false,
            ]);
    }

    public function test_partner_sees_owner_id_as_effective(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();
        $this->actingAs($partner);

        $this->getJson('/dashboard/_couple-probe')
            ->assertOk()
            ->assertJson([
                'auth_id'         => $partner->id,
                'effective_id'    => $owner->id,
                'is_partner_mode' => true,
            ]);
    }

    public function test_unauthenticated_request_has_false_partner_mode_and_no_effective_id(): void
    {
        $this->getJson('/dashboard/_couple-probe-guest')
            ->assertOk()
            ->assertJson([
                'auth_id'         => null,
                'effective_id'    => null,
                'is_partner_mode' => false,
            ]);
    }
}
