<?php
// tests/Feature/Invitation/CreateFromTemplateSlugTest.php
declare(strict_types=1);

namespace Tests\Feature\Invitation;

use App\Models\CoupleProfile;
use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CreateFromTemplateSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_template_pick_uses_couple_names_not_random(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleProfile::create([
            'user_id'        => $user->id,
            'groom_name'     => 'Andi Pratama',
            'groom_nickname' => 'Andi',
            'bride_name'     => 'Rahma Putri',
            'bride_nickname' => 'Rahma',
        ]);
        $template = Template::factory()->create(['tier' => 'free']);

        $this->actingAs($user)
            ->post(route('dashboard.invitations.from-template'), ['template_id' => $template->id])
            ->assertRedirect();

        $this->assertSame('rahma-andi', Invitation::where('user_id', $user->id)->firstOrFail()->slug);
    }

    public function test_slug_starting_with_a_reserved_prefix_still_routes_publicly(): void
    {
        // "i" is reserved, but only as a whole segment — "intan-budi" must resolve.
        $route = Route::getRoutes()->match(Request::create('/intan-budi', 'GET'));

        $this->assertSame('invitation.show', $route->getName());
    }

    public function test_reserved_segment_never_resolves_to_an_invitation(): void
    {
        $route = Route::getRoutes()->match(Request::create('/dashboard', 'GET'));

        $this->assertNotSame('invitation.show', $route->getName());
    }
}
