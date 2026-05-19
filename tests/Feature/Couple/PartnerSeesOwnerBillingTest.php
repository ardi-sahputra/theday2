<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\Transaction;
use App\Models\User;
use App\Support\EffectiveUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerBillingTest extends TestCase
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

    public function test_partner_sees_owner_transactions(): void
    {
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        Transaction::factory()->for($owner)->create(['amount' => 99000]);

        $this->actingAs($partner)
            ->get('/dashboard/transactions')
            ->assertOk()
            ->assertSee('99.000'); // matches "Rp 99.000" rendered by number_format(..., 0, ',', '.')
    }

    public function test_owner_sees_own_transactions(): void
    {
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        Transaction::factory()->for($owner)->create(['amount' => 77000]);

        $this->actingAs($owner)
            ->get('/dashboard/transactions')
            ->assertOk()
            ->assertSee('77.000');
    }
}
