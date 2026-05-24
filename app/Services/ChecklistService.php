<?php

// app/Services/ChecklistService.php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ChecklistTaskSource;
use App\Enums\ChecklistTaskStatus;
use App\Models\ChecklistTask;
use App\Models\ChecklistTemplate;
use App\Models\Invitation;
use App\Models\WeddingPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ChecklistService
{
    // ─── Initialization ───────────────────────────────────────────

    public function initialize(WeddingPlan $plan): void
    {
        // Idempotent on the system template set, not just the initialized flag —
        // so a couple who started blank can still apply the standard set later.
        if ($plan->checklistTasks()->where('source', ChecklistTaskSource::System)->exists()) {
            return;
        }

        DB::transaction(function () use ($plan) {
            // Resolve the couple's wedding type (from the linked invitation,
            // falling back to any invitation the user owns). When undecided
            // ('belum') or unknown, every task applies (full checklist).
            $weddingType = $plan->primaryInvitation?->wedding_type
                ?? Invitation::where('user_id', $plan->user_id)->value('wedding_type');

            $templates = ChecklistTemplate::active()->ordered()->get();

            if ($weddingType !== null && $weddingType !== 'belum') {
                $templates = $templates
                    ->filter(fn (ChecklistTemplate $t) => empty($t->wedding_types) || in_array($weddingType, $t->wedding_types, true))
                    ->values();
            }

            foreach ($templates as $i => $template) {
                $dueDate = $this->calculateDueDate($plan->event_date, $template->day_offset);

                $plan->checklistTasks()->create([
                    'source'      => ChecklistTaskSource::System,
                    'template_id' => $template->id,
                    'title'       => $template->title,
                    'description' => $template->description,
                    'category'    => $template->category,
                    'priority'    => $template->priority,
                    'status'      => ChecklistTaskStatus::Todo,
                    'due_date'    => $dueDate,
                    'sort_order'  => $i,
                ]);
            }

            $plan->update(['checklist_initialized_at' => now()]);
        });
    }

    // ─── Task CRUD ────────────────────────────────────────────────

    public function createTask(WeddingPlan $plan, array $data): ChecklistTask
    {
        $maxOrder = $plan->checklistTasks()->max('sort_order') ?? -1;

        return $plan->checklistTasks()->create([
            'source'               => ChecklistTaskSource::User,
            'title'                => $data['title'],
            'description'          => $data['description'] ?? null,
            'vendor'               => $data['vendor'] ?? null,
            'category'             => $data['category'],
            'priority'             => $data['priority'] ?? 'medium',
            'status'               => ChecklistTaskStatus::Todo,
            'due_date'             => $data['due_date'] ?? null,
            'assignee_type'        => $data['assignee_type'] ?? null,
            'assignee_label'       => $data['assignee_label'] ?? null,
            'reminder_enabled'     => $data['reminder_enabled'] ?? false,
            'reminder_offset_days' => $data['reminder_offset_days'] ?? null,
            'sort_order'           => $maxOrder + 1,
        ]);
    }

    public function updateTask(ChecklistTask $task, array $data): ChecklistTask
    {
        $isUserModified = $task->is_user_modified || $task->isSystemTask();

        $task->update([
            'title'                => $data['title']       ?? $task->title,
            'description'          => array_key_exists('description', $data) ? $data['description'] : $task->description,
            'vendor'               => array_key_exists('vendor', $data) ? $data['vendor'] : $task->vendor,
            'category'             => $data['category']    ?? $task->category,
            'priority'             => $data['priority']    ?? $task->priority,
            'due_date'             => array_key_exists('due_date', $data) ? $data['due_date'] : $task->due_date,
            'assignee_type'        => array_key_exists('assignee_type', $data) ? $data['assignee_type'] : $task->assignee_type,
            'assignee_label'       => array_key_exists('assignee_label', $data) ? $data['assignee_label'] : $task->assignee_label,
            'reminder_enabled'     => array_key_exists('reminder_enabled', $data) ? $data['reminder_enabled'] : $task->reminder_enabled,
            'reminder_offset_days' => array_key_exists('reminder_offset_days', $data) ? $data['reminder_offset_days'] : $task->reminder_offset_days,
            'is_user_modified'     => $isUserModified,
        ]);

        return $task->refresh();
    }

    // ─── State Transitions ────────────────────────────────────────

    public function toggle(ChecklistTask $task): ChecklistTask
    {
        if ($task->isArchived()) {
            abort(422, 'Archived task cannot be toggled. Restore it first.');
        }

        DB::transaction(function () use ($task) {
            if ($task->isDone()) {
                $task->update([
                    'status'       => ChecklistTaskStatus::Todo,
                    'completed_at' => null,
                ]);
            } else {
                $task->update([
                    'status'       => ChecklistTaskStatus::Done,
                    'completed_at' => now(),
                ]);
            }
        });

        return $task->refresh();
    }

    public function archive(ChecklistTask $task): ChecklistTask
    {
        if ($task->isArchived()) {
            abort(422, 'Task already archived.');
        }

        $task->update([
            'status'      => ChecklistTaskStatus::Archived,
            'archived_at' => now(),
        ]);

        return $task->refresh();
    }

    public function restore(ChecklistTask $task): ChecklistTask
    {
        if (! $task->isArchived()) {
            abort(422, 'Only archived tasks can be restored.');
        }

        $task->update([
            'status'       => ChecklistTaskStatus::Todo,
            'archived_at'  => null,
            'completed_at' => null,
        ]);

        return $task->refresh();
    }

    // ─── Summary ─────────────────────────────────────────────────

    public function getSummary(WeddingPlan $plan): array
    {
        $counts = $plan->checklistTasks()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $todo     = (int) ($counts['todo']     ?? 0);
        $done     = (int) ($counts['done']     ?? 0);
        $archived = (int) ($counts['archived'] ?? 0);
        $total    = $todo + $done;

        $today = now()->startOfDay();

        $overdue = $plan->checklistTasks()
            ->where('status', ChecklistTaskStatus::Todo)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->count();

        $upcoming7d = $plan->checklistTasks()
            ->where('status', ChecklistTaskStatus::Todo)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->count();

        return [
            'total'          => $total,
            'todo'           => $todo,
            'done'           => $done,
            'archived'       => $archived,
            'progress'       => $total > 0 ? round(($done / $total) * 100) : 0,
            'overdue'        => $overdue,
            'upcoming_7d'    => $upcoming7d,
            'has_event_date' => $plan->hasEventDate(),
            'event_date'     => $plan->event_date?->format('Y-m-d'),
        ];
    }

    // ─── Recalculation ────────────────────────────────────────────

    public function recalculateDueDates(WeddingPlan $plan): void
    {
        $tasks = $plan->checklistTasks()
            ->systemUnmodified()
            ->whereNotNull('template_id')
            ->with('template')
            ->get();

        foreach ($tasks as $task) {
            $dueDate = $this->calculateDueDate($plan->event_date, $task->template?->day_offset);
            $task->update(['due_date' => $dueDate]);
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function calculateDueDate(?Carbon $eventDate, ?int $dayOffset): ?string
    {
        if ($eventDate === null || $dayOffset === null) {
            return null;
        }

        return $eventDate->copy()->addDays($dayOffset)->format('Y-m-d');
    }

    // ─── Query Helpers ────────────────────────────────────────────

    public function getTasks(WeddingPlan $plan, array $filters = []): Collection
    {
        $query = $plan->checklistTasks()
            ->withCount([
                'subtasks',
                'subtasks as subtasks_done_count' => fn ($q) => $q->where('is_completed', true),
            ]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->orderBy('sort_order')
                     ->orderBy('due_date')
                     ->orderBy('created_at')
                     ->get();
    }
}
