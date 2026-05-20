<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_note_authored_by_current_user(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $res = $this->actingAs($user)->postJson('/dashboard/budget-planner/notes', [
            'body' => 'Naikkan budget catering ke 60jt ya',
        ])->assertCreated();

        $res->assertJsonPath('body', 'Naikkan budget catering ke 60jt ya');
        $res->assertJsonPath('is_mine', true);
        $this->assertDatabaseHas('wedding_budget_notes', [
            'user_id' => $user->id,
            'body'    => 'Naikkan budget catering ke 60jt ya',
        ]);
    }

    public function test_index_payload_includes_budget_notes(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $this->actingAs($user)->postJson('/dashboard/budget-planner/notes', ['body' => 'Halo']);

        $this->actingAs($user)->get('/dashboard/budget-planner')
            ->assertInertia(fn ($page) => $page->has('budgetNotes', 1));
    }

    public function test_only_author_can_delete(): void
    {
        $author = User::factory()->create(['onboarding_completed_at' => now()]);
        $other  = User::factory()->create(['onboarding_completed_at' => now()]);
        $id = $this->actingAs($author)->postJson('/dashboard/budget-planner/notes', ['body' => 'X'])->json('id');

        $this->actingAs($other)->deleteJson("/dashboard/budget-planner/notes/{$id}")->assertForbidden();
        $this->actingAs($author)->deleteJson("/dashboard/budget-planner/notes/{$id}")->assertNoContent();
    }
}
