<?php

// tests/Feature/Dashboard/WeddingDocumentFileTest.php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class WeddingDocumentFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_stores_file_privately_and_marks_row(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua']);

        $this->actingAs($user)
            ->postJson('/dashboard/documents/n1/file', [
                'file' => UploadedFile::fake()->create('n1.pdf', 200, 'application/pdf'),
            ])
            ->assertSuccessful();

        $row = $plan->weddingDocuments()->where('key', 'n1')->first();
        $this->assertNotNull($row->file_path);
        Storage::disk('local')->assertExists($row->file_path);
    }

    public function test_oversized_or_wrong_type_is_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->update(['document_path' => 'kua']);

        $this->actingAs($user)
            ->postJson('/dashboard/documents/n1/file', [
                'file' => UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream'),
            ])
            ->assertStatus(422);
    }

    public function test_serve_route_rejects_non_owner(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan  = WeddingPlan::firstOrCreate(['user_id' => $owner->id]);
        $plan->update(['document_path' => 'kua']);
        $this->actingAs($owner)->postJson('/dashboard/documents/n1/file', [
            'file' => UploadedFile::fake()->create('n1.pdf', 100, 'application/pdf'),
        ])->assertSuccessful();

        $signed = URL::signedRoute('dashboard.documents.file.show', ['key' => 'n1', 'plan' => $plan->id]);

        $intruder = User::factory()->create(['onboarding_completed_at' => now()]);
        $this->actingAs($intruder)->get($signed)->assertForbidden();
    }
}
