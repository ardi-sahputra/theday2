<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_throttled_after_5_requests(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        CoupleLink::factory()->for($owner, 'owner')->pending()->create([
            'invited_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($owner);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/couple/invite/resend');
            // Age the invite each time so the 5-min cooldown doesn't block
            CoupleLink::where('owner_id', $owner->id)->update(['invited_at' => now()->subMinutes(10)]);
        }

        // 6th call should hit the throttle (429)
        $this->post('/couple/invite/resend')->assertStatus(429);
    }
}
