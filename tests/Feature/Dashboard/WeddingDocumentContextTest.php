<?php

// tests/Feature/Dashboard/WeddingDocumentContextTest.php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingDocumentContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_couple_can_set_path_and_flags(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patchJson('/dashboard/documents/context', [
                'path'  => 'kua',
                'flags' => ['beda_domisili' => true, 'under21' => false],
            ])
            ->assertSuccessful();

        $plan = $user->weddingPlan()->first();
        $this->assertSame('kua', $plan->document_path);
        $this->assertTrue($plan->document_flags['beda_domisili']);
    }

    public function test_data_endpoint_returns_filtered_catalog(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua', 'document_flags' => ['beda_domisili' => true]]);

        $res = $this->actingAs($user)->getJson('/dashboard/documents/data')->assertSuccessful();

        $keys = collect($res->json('documents'))->pluck('key');
        $this->assertContains('n1', $keys);
        $this->assertContains('numpang_nikah', $keys);
        $this->assertNotContains('pemberkatan', $keys);
    }

    public function test_invalid_path_is_rejected(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patchJson('/dashboard/documents/context', ['path' => 'bogus'])
            ->assertStatus(422);
    }
}
