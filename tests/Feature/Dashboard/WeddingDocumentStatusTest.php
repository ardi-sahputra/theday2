<?php

// tests/Feature/Dashboard/WeddingDocumentStatusTest.php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingDocumentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_update_persists_and_sets_completed_at(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua']);

        $this->actingAs($user)
            ->patchJson('/dashboard/documents/n1/status', ['status' => 'beres'])
            ->assertSuccessful();

        $row = $plan->weddingDocuments()->where('key', 'n1')->first();
        $this->assertSame('beres', $row->status->value);
        $this->assertNotNull($row->completed_at);
    }

    public function test_unknown_key_is_rejected(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua']);

        $this->actingAs($user)
            ->patchJson('/dashboard/documents/not_a_real_key/status', ['status' => 'beres'])
            ->assertStatus(404);
    }
}
