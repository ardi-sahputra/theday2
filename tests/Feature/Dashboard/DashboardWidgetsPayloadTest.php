<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\CoupleProfile;
use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardWidgetsPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exposes_widget_props(): void
    {
        $user = User::factory()->create();
        CoupleProfile::create([
            'user_id'        => $user->id,
            'groom_name'     => 'Rizki Pratama',
            'groom_nickname' => 'Rizki',
            'bride_name'     => 'Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'wedding_date'   => now()->addDays(120)->toDateString(),
        ]);

        $inv = Invitation::factory()->for($user)->create(['status' => 'published']);
        Rsvp::factory()->for($inv)->create(['attendance' => 'hadir',       'guest_count' => 2]);
        Rsvp::factory()->for($inv)->create(['attendance' => 'tidak_hadir', 'guest_count' => 1]);
        GuestMessage::create(['invitation_id' => $inv->id, 'name' => 'Budi', 'message' => 'Selamat!', 'is_approved' => true]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->where('couple.bride_nickname', 'Ayu')
                ->where('couple.groom_nickname', 'Rizki')
                ->where('stats.rsvp_attending', 1)
                ->where('stats.ucapan_count', 1)
                ->has('countdown.target')
                ->has('budgetWidget.categories')
                ->has('recentRsvps', 2)
                ->has('inviteShare')
            );
    }
}
