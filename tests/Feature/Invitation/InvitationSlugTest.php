<?php
// tests/Feature/Invitation/InvitationSlugTest.php
declare(strict_types=1);

namespace Tests\Feature\Invitation;

use App\Models\Invitation;
use App\Models\InvitationSlugAlias;
use App\Models\User;
use App\Support\InvitationSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_couple_names_become_bride_groom_slug(): void
    {
        $this->assertSame('rahma-andi', InvitationSlug::forCouple('Andi Pratama', 'Rahma Putri'));
    }

    public function test_only_one_name_still_yields_a_readable_slug(): void
    {
        $this->assertSame('rahma', InvitationSlug::forCouple(null, 'Rahma'));
        $this->assertSame('andi', InvitationSlug::forCouple('Andi', ''));
    }

    public function test_no_names_falls_back_to_lowercase_random(): void
    {
        $slug = InvitationSlug::forCouple(null, null);

        $this->assertMatchesRegularExpression('/^[a-z0-9]{8}$/', $slug);
    }

    public function test_collision_gets_year_suffix_then_increments(): void
    {
        $user = User::factory()->create();
        Invitation::factory()->for($user)->create(['slug' => 'rahma-andi']);

        $this->assertSame('rahma-andi-' . date('Y'), InvitationSlug::forCouple('Andi', 'Rahma'));

        Invitation::factory()->for($user)->create(['slug' => 'rahma-andi-' . date('Y')]);

        $this->assertSame('rahma-andi-2', InvitationSlug::forCouple('Andi', 'Rahma'));
    }

    public function test_reserved_words_are_never_handed_out(): void
    {
        $this->assertTrue(InvitationSlug::isTaken('dashboard'));
        $this->assertTrue(InvitationSlug::isTaken('admin'));
        $this->assertNotSame('admin', InvitationSlug::unique('admin'));
    }

    public function test_alias_of_a_published_invitation_blocks_reuse(): void
    {
        $user = User::factory()->create();
        $inv  = Invitation::factory()->for($user)->create(['slug' => 'rahma-andi-baru']);
        InvitationSlugAlias::create(['invitation_id' => $inv->id, 'slug' => 'rahma-andi']);

        $this->assertTrue(InvitationSlug::isTaken('rahma-andi'));
        $this->assertNotSame('rahma-andi', InvitationSlug::forCouple('Andi', 'Rahma'));
    }

    public function test_soft_deleted_slug_stays_blocked(): void
    {
        $user = User::factory()->create();
        $inv  = Invitation::factory()->for($user)->create(['slug' => 'rahma-andi']);
        $inv->delete();

        $this->assertTrue(InvitationSlug::isTaken('rahma-andi'));
    }

    public function test_exclude_id_lets_an_invitation_keep_its_own_slug(): void
    {
        $user = User::factory()->create();
        $inv  = Invitation::factory()->for($user)->create(['slug' => 'rahma-andi']);

        $this->assertFalse(InvitationSlug::isTaken('rahma-andi', $inv->id));
    }
}
