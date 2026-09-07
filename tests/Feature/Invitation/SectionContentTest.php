<?php
// tests/Feature/Invitation/SectionContentTest.php
declare(strict_types=1);

namespace Tests\Feature\Invitation;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SectionContentTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): array
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $inv  = Invitation::factory()->for($user)->create();

        return [$user, $inv];
    }

    public function test_love_story_is_saved_under_the_key_templates_read(): void
    {
        [$user, $inv] = $this->owner();
        $stories = [['date' => '2026-01-02', 'title' => 'Lamaran', 'description' => 'Di rumah', 'photo_url' => '']];

        $this->actingAs($user)
            ->patchJson("/api/invitations/{$inv->id}/sections/love_story", [
                'data'   => ['stories' => $stories],
                'status' => 'complete',
            ])
            ->assertOk();

        $section = $inv->sections()->where('section_key', 'love_story')->firstOrFail();
        $saved   = $section->data_json['stories'][0];

        $this->assertSame('Lamaran', $saved['title']);
        $this->assertSame('2026-01-02', $saved['date']);
        $this->assertSame('Di rumah', $saved['description']);
        // ConvertEmptyStringsToNull turns the blank photo into null; templates
        // test photo_url for truthiness, so both spellings render the same.
        $this->assertNull($saved['photo_url']);
        $this->assertSame('complete', $section->completion_status);
    }

    public function test_section_media_upload_returns_a_url_without_touching_the_gallery(): void
    {
        Storage::fake(config('filesystems.uploads'));
        [$user, $inv] = $this->owner();

        $res = $this->actingAs($user)
            ->postJson("/api/invitations/{$inv->id}/sections/media", [
                'image' => UploadedFile::fake()->image('moment.jpg'),
            ])
            ->assertCreated();

        $this->assertNotEmpty($res->json('url'));
        $this->assertSame(0, $inv->galleries()->count());
        $this->assertCount(1, Storage::disk(config('filesystems.uploads'))->files("invitations/{$inv->id}/sections"));
    }

    public function test_section_media_rejects_non_images(): void
    {
        Storage::fake(config('filesystems.uploads'));
        [$user, $inv] = $this->owner();

        $this->actingAs($user)
            ->postJson("/api/invitations/{$inv->id}/sections/media", [
                'image' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
            ])
            ->assertStatus(422);
    }

    public function test_section_media_is_owner_only(): void
    {
        Storage::fake(config('filesystems.uploads'));
        [, $inv] = $this->owner();
        $stranger = User::factory()->create(['onboarding_completed_at' => now()]);

        $this->actingAs($stranger)
            ->postJson("/api/invitations/{$inv->id}/sections/media", [
                'image' => UploadedFile::fake()->image('moment.jpg'),
            ])
            ->assertForbidden();
    }

    public function test_editor_receives_every_section_not_just_the_storybook_trio(): void
    {
        [$user, $inv] = $this->owner();
        $this->withoutVite();
        config(['inertia.testing.ensure_pages_exist' => false]);

        foreach ([['love_story', ['stories' => [['title' => 'A']]]], ['video', ['url' => 'https://y.tv/1']]] as [$key, $data]) {
            $this->actingAs($user)
                ->patchJson("/api/invitations/{$inv->id}/sections/{$key}", ['data' => $data])
                ->assertOk();
        }

        $this->actingAs($user)
            ->get("/dashboard/invitations/{$inv->id}/customize-v2")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('invitation.sections.love_story')
                ->has('invitation.sections.video')
                ->where('invitation.sections.video.data.url', 'https://y.tv/1')
            );
    }
}
