<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CoupleProfile;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCustomerNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_falls_back_to_account_name_without_couple_data(): void
    {
        $user = User::factory()->create(['name' => 'ardeveloper tech']);

        $this->assertSame('ardeveloper tech', $user->customerDisplayName());
    }

    public function test_prefers_couple_profile_names(): void
    {
        $user = User::factory()->create(['name' => 'ardeveloper tech']);
        CoupleProfile::create([
            'user_id'    => $user->id,
            'groom_name' => 'Rizki',
            'bride_name' => 'Ayu',
        ]);

        $this->assertSame('Rizki & Ayu', $user->fresh()->customerDisplayName());
    }

    public function test_uses_latest_invitation_details_when_no_profile(): void
    {
        $user = User::factory()->create(['name' => 'ardeveloper tech']);
        $invitation = Invitation::factory()->create(['user_id' => $user->id]);
        $invitation->details()->create([
            'groom_name' => 'Bima',
            'bride_name' => 'Sari',
        ]);

        $this->assertSame('Bima & Sari', $user->fresh()->customerDisplayName());
    }

    public function test_single_name_does_not_emit_dangling_ampersand(): void
    {
        $user = User::factory()->create(['name' => 'ardeveloper tech']);
        CoupleProfile::create([
            'user_id'    => $user->id,
            'groom_name' => 'Rizki',
            'bride_name' => null,
        ]);

        $this->assertSame('Rizki', $user->fresh()->customerDisplayName());
    }
}
