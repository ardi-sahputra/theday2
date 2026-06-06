<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Resolves the single most important "next action" for a preparing couple's
 * dashboard, as a state cascade (first match wins). Pure function — every input
 * is a scalar computed by the caller, so it is trivially unit-testable.
 *
 * Returns null when no hero should show: already-married couples are out of
 * scope here (their Phase 3 home is handled elsewhere).
 *
 * `title`/`body`/`cta` are i18n KEYS resolved on the frontend with
 * `t(key, params)`; `params` carries the interpolation values.
 */
final class NextActionResolver
{
    private const BASE = 'dashboard.index.nextAction.';

    /**
     * @param  array{
     *     is_married?: bool,
     *     has_invitation?: bool,
     *     invitation_id?: int|null,
     *     invitation_status?: string|null,
     *     published_count?: int,
     *     has_wedding_date?: bool,
     *     days_until?: int|null,
     *     overdue_task_title?: string|null,
     *     new_rsvp_count?: int,
     *     primary_view_count?: int,
     *     due_soon_count?: int,
     *     checklist_initialized?: bool,
     *     checklist_progress?: int,
     *  }  $ctx
     * @return array<string,mixed>|null
     */
    public static function resolve(array $ctx): ?array
    {
        // Already married → lifecycle nudge out of scope. Hide the hero.
        if ($ctx['is_married'] ?? false) {
            return null;
        }

        // 1. No invitation yet → the only sensible entry: pick a design.
        if (! ($ctx['has_invitation'] ?? false)) {
            return self::make('no_invitation', 'start', 'invite',
                cta: ['kind' => 'route', 'route' => 'dashboard.templates']);
        }

        $id        = $ctx['invitation_id'] ?? null;
        $daysUntil = $ctx['days_until'] ?? null;
        $published = (int) ($ctx['published_count'] ?? 0);

        // 2. Overdue checklist task → most time-sensitive, can't be recovered.
        if (! empty($ctx['overdue_task_title'])) {
            return self::make('overdue_task', 'urgent', 'flag',
                params: ['task' => $ctx['overdue_task_title']],
                cta: ['kind' => 'route', 'route' => 'dashboard.checklist.index']);
        }

        // 3. Wedding within a week but nothing published → urgent publish.
        if ($daysUntil !== null && $daysUntil >= 0 && $daysUntil <= 7 && $published === 0) {
            return self::make('publish_soon', 'urgent', 'bell',
                params: ['days' => $daysUntil],
                cta: ['kind' => 'route', 'route' => 'dashboard.invitations.customize-v2', 'param' => $id]);
        }

        // 4. New RSVPs since yesterday → "what changed", momentum.
        if ((int) ($ctx['new_rsvp_count'] ?? 0) > 0) {
            return self::make('new_rsvp', 'info', 'guest',
                params: ['count' => (int) $ctx['new_rsvp_count']],
                cta: ['kind' => 'route', 'route' => 'dashboard.rsvp.index']);
        }

        // 5. No wedding date → set it (reuses the on-page date modal).
        if (! ($ctx['has_wedding_date'] ?? false)) {
            return self::make('set_date', 'progress', 'cal',
                cta: ['kind' => 'action', 'action' => 'set-date']);
        }

        // 6. Still a draft → review & publish.
        if ($published === 0 && ($ctx['invitation_status'] ?? null) === 'draft') {
            return self::make('publish_draft', 'progress', 'sparkle',
                cta: ['kind' => 'route', 'route' => 'dashboard.invitations.customize-v2', 'param' => $id]);
        }

        // 7. Published but never viewed → share it.
        if ($published >= 1 && (int) ($ctx['primary_view_count'] ?? 0) === 0) {
            return self::make('share_invite', 'info', 'share',
                cta: ['kind' => 'action', 'action' => 'share']);
        }

        // 8. Checklist tasks due this week → keep the prep moving.
        if ((int) ($ctx['due_soon_count'] ?? 0) > 0) {
            return self::make('checklist_due', 'progress', 'check',
                params: ['count' => (int) $ctx['due_soon_count']],
                cta: ['kind' => 'route', 'route' => 'dashboard.checklist.index']);
        }

        // 9. Checklist not set up yet → initialize it.
        if (! ($ctx['checklist_initialized'] ?? false)) {
            return self::make('checklist_init', 'progress', 'check',
                cta: ['kind' => 'route', 'route' => 'dashboard.checklist.index']);
        }

        // 10. Steady state → gentle progress nudge.
        return self::make('steady', 'progress', 'heart',
            params: ['progress' => (int) ($ctx['checklist_progress'] ?? 0)],
            cta: ['kind' => 'route', 'route' => 'dashboard.checklist.index']);
    }

    /**
     * @param  array<string,mixed>  $params
     * @param  array<string,mixed>  $cta
     * @return array<string,mixed>
     */
    private static function make(string $key, string $level, string $icon, array $params = [], array $cta = []): array
    {
        $node = self::BASE.Str::camel($key).'.';

        return [
            'key'    => $key,
            'level'  => $level,
            'icon'   => $icon,
            'title'  => $node.'title',
            'body'   => $node.'body',
            'cta'    => $node.'cta',
            'params' => (object) $params,
            'action' => $cta,
        ];
    }
}
