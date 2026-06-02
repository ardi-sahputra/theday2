<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\NextActionResolver;
use Tests\TestCase;

class NextActionResolverTest extends TestCase
{
    /**
     * A "steady" couple: invitation published, viewed, dated, checklist on
     * track. Every override below peels one layer to assert the cascade order.
     *
     * @param  array<string,mixed>  $overrides
     * @return array<string,mixed>
     */
    private function ctx(array $overrides = []): array
    {
        return array_merge([
            'is_married'            => false,
            'has_invitation'        => true,
            'invitation_id'         => 42,
            'invitation_status'     => 'published',
            'published_count'       => 1,
            'has_wedding_date'      => true,
            'days_until'            => 120,
            'overdue_task_title'    => null,
            'new_rsvp_count'        => 0,
            'primary_view_count'    => 50,
            'due_soon_count'        => 0,
            'checklist_initialized' => true,
            'checklist_progress'    => 60,
        ], $overrides);
    }

    public function test_married_couple_gets_no_hero(): void
    {
        $this->assertNull(NextActionResolver::resolve($this->ctx(['is_married' => true])));
    }

    public function test_no_invitation_is_the_entry_state(): void
    {
        $result = NextActionResolver::resolve($this->ctx(['has_invitation' => false]));

        $this->assertSame('no_invitation', $result['key']);
        $this->assertSame('start', $result['level']);
        $this->assertSame('route', $result['action']['kind']);
        $this->assertSame('dashboard.templates', $result['action']['route']);
    }

    public function test_overdue_task_outranks_everything_actionable(): void
    {
        // Even with a near date, unpublished invite, and fresh RSVPs, overdue wins.
        $result = NextActionResolver::resolve($this->ctx([
            'overdue_task_title' => 'Sewa pelaminan',
            'days_until'         => 3,
            'published_count'    => 0,
            'invitation_status'  => 'draft',
            'new_rsvp_count'     => 5,
        ]));

        $this->assertSame('overdue_task', $result['key']);
        $this->assertSame('urgent', $result['level']);
        $this->assertSame('Sewa pelaminan', $result['params']->task);
        $this->assertSame('dashboard.checklist.index', $result['action']['route']);
    }

    public function test_publish_soon_when_wedding_within_a_week_and_unpublished(): void
    {
        $result = NextActionResolver::resolve($this->ctx([
            'days_until'        => 5,
            'published_count'   => 0,
            'invitation_status' => 'draft',
            'new_rsvp_count'    => 3, // outranked by the urgent publish window
        ]));

        $this->assertSame('publish_soon', $result['key']);
        $this->assertSame('urgent', $result['level']);
        $this->assertSame(5, $result['params']->days);
        $this->assertSame('dashboard.invitations.edit', $result['action']['route']);
        $this->assertSame(42, $result['action']['param']);
    }

    public function test_new_rsvp_surfaces_when_nothing_more_urgent(): void
    {
        $result = NextActionResolver::resolve($this->ctx(['new_rsvp_count' => 4]));

        $this->assertSame('new_rsvp', $result['key']);
        $this->assertSame('info', $result['level']);
        $this->assertSame(4, $result['params']->count);
        $this->assertSame('dashboard.rsvp.index', $result['action']['route']);
    }

    public function test_set_date_uses_local_modal_action(): void
    {
        $result = NextActionResolver::resolve($this->ctx([
            'has_wedding_date'  => false,
            'days_until'        => null,
            'published_count'   => 0,
            'invitation_status' => 'draft',
        ]));

        $this->assertSame('set_date', $result['key']);
        $this->assertSame('action', $result['action']['kind']);
        $this->assertSame('set-date', $result['action']['action']);
    }

    public function test_publish_draft_when_dated_but_unpublished(): void
    {
        $result = NextActionResolver::resolve($this->ctx([
            'published_count'   => 0,
            'invitation_status' => 'draft',
        ]));

        $this->assertSame('publish_draft', $result['key']);
        $this->assertSame('dashboard.invitations.edit', $result['action']['route']);
    }

    public function test_share_invite_when_published_but_never_viewed(): void
    {
        $result = NextActionResolver::resolve($this->ctx(['primary_view_count' => 0]));

        $this->assertSame('share_invite', $result['key']);
        $this->assertSame('action', $result['action']['kind']);
        $this->assertSame('share', $result['action']['action']);
    }

    public function test_checklist_due_when_tasks_due_soon(): void
    {
        $result = NextActionResolver::resolve($this->ctx(['due_soon_count' => 3]));

        $this->assertSame('checklist_due', $result['key']);
        $this->assertSame(3, $result['params']->count);
    }

    public function test_checklist_init_when_not_initialized(): void
    {
        $result = NextActionResolver::resolve($this->ctx(['checklist_initialized' => false]));

        $this->assertSame('checklist_init', $result['key']);
    }

    public function test_steady_state_is_the_default(): void
    {
        $result = NextActionResolver::resolve($this->ctx());

        $this->assertSame('steady', $result['key']);
        $this->assertSame('progress', $result['level']);
        $this->assertSame(60, $result['params']->progress);
    }

    public function test_title_body_cta_are_namespaced_i18n_keys(): void
    {
        $result = NextActionResolver::resolve($this->ctx(['has_invitation' => false]));

        $this->assertSame('dashboard.index.nextAction.noInvitation.title', $result['title']);
        $this->assertSame('dashboard.index.nextAction.noInvitation.body', $result['body']);
        $this->assertSame('dashboard.index.nextAction.noInvitation.cta', $result['cta']);
    }
}
