<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistVendorTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_and_update_persist_vendor(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $create = $this->actingAs($user)->postJson('/dashboard/checklist/tasks', [
            'title'    => 'Tasting catering akhir',
            'category' => 'vendor',
            'vendor'   => 'Pawon Catering',
        ])->assertCreated();

        $create->assertJsonPath('vendor', 'Pawon Catering');
        $id = $create->json('id');

        $this->actingAs($user)->patchJson("/dashboard/checklist/tasks/{$id}", [
            'vendor' => 'Bunga Senja',
        ])->assertOk()->assertJsonPath('vendor', 'Bunga Senja');
    }
}
