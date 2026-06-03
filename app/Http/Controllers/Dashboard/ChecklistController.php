<?php

// app/Http/Controllers/Dashboard/ChecklistController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Actions\Planner\BuildPlannerFactsAction;
use App\Actions\Planner\BuildPlannerInsightAction;
use App\Http\Controllers\Controller;
use App\Models\ChecklistSubtask;
use App\Models\ChecklistTask;
use App\Models\WeddingPlan;
use App\Services\ChecklistService;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChecklistController extends Controller
{
    public function __construct(private readonly ChecklistService $service) {}

    // ─── Page ─────────────────────────────────────────────────────

    public function index(
        BuildPlannerFactsAction $facts,
        BuildPlannerInsightAction $insights,
    ): Response {
        $plan = $this->resolveOrCreatePlan();

        return Inertia::render('Dashboard/Checklist/Index', [
            'weddingPlan' => [
                'id'          => $plan->id,
                'event_date'  => $plan->event_date?->format('Y-m-d'),
                'initialized' => $plan->isChecklistInitialized(),
            ],
            'plannerPanel' => [
                'facts'    => $facts->execute($plan),
                // No AI on page load; client refreshes if `fresh:false`.
                ...$insights->execute($plan, generate: false),
            ],
        ]);
    }

    // ─── API: Initialize ──────────────────────────────────────────

    public function initialize(Request $request): JsonResponse
    {
        $plan = $this->resolveOrCreatePlan();

        // "Isi sendiri" — record that the couple made their choice (so we stop
        // prompting) without generating any template tasks. They can still apply
        // the standard set later from the rail.
        if ($request->input('mode') === 'blank') {
            if (! $plan->isChecklistInitialized()) {
                $plan->update(['checklist_initialized_at' => now()]);
            }

            return response()->json(['message' => 'Blank checklist ready.'], 201);
        }

        // Standard set. The service is idempotent on existing system tasks.
        $this->service->initialize($plan);

        return response()->json(['message' => 'Checklist initialized.'], 201);
    }

    // ─── API: Tasks list ──────────────────────────────────────────

    public function tasks(Request $request): JsonResponse
    {
        $plan = $this->resolveOrCreatePlan();

        $filters = $request->only(['status', 'category']);
        $tasks   = $this->service->getTasks($plan, $filters);

        return response()->json([
            'tasks' => $tasks->map(fn ($t) => $this->taskResource($t))->values(),
        ]);
    }

    // ─── API: Create task ─────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $plan = $this->resolveOrCreatePlan();

        $data = $request->validate([
            'title'                => 'required|string|min:3|max:200',
            'category'             => 'required|string|max:50',
            'priority'             => 'sometimes|string|in:low,medium,high',
            'due_date'             => 'nullable|date',
            'description'          => 'nullable|string|max:1000',
            'vendor'               => 'nullable|string|max:120',
            'assignee_type'        => 'nullable|string|in:bride,groom,both,parents,family,wo,custom',
            'assignee_label'       => 'nullable|string|max:100',
            'reminder_enabled'     => 'sometimes|boolean',
            'reminder_offset_days' => 'nullable|integer|in:1,7,14,30',
        ], [
            'title.required' => 'Nama task wajib diisi.',
            'title.min'      => 'Nama task minimal 3 karakter.',
            'title.max'      => 'Nama task maksimal 200 karakter.',
        ]);

        $task = $this->service->createTask($plan, $data);

        return response()->json($this->taskResource($task), 201);
    }

    // ─── API: Update task ─────────────────────────────────────────

    public function update(Request $request, string $id): JsonResponse
    {
        $task = $this->resolveTask($id);

        $data = $request->validate([
            'title'                => 'sometimes|string|min:3|max:200',
            'category'             => 'sometimes|string|max:50',
            'priority'             => 'sometimes|string|in:low,medium,high',
            'due_date'             => 'nullable|date',
            'description'          => 'nullable|string|max:1000',
            'vendor'               => 'nullable|string|max:120',
            'assignee_type'        => 'nullable|string|in:bride,groom,both,parents,family,wo,custom',
            'assignee_label'       => 'nullable|string|max:100',
            'reminder_enabled'     => 'sometimes|boolean',
            'reminder_offset_days' => 'nullable|integer|in:1,7,14,30',
        ]);

        $task = $this->service->updateTask($task, $data);

        return response()->json($this->taskResource($task));
    }

    // ─── API: Toggle ──────────────────────────────────────────────

    public function toggle(string $id): JsonResponse
    {
        $task = $this->resolveTask($id);
        $task = $this->service->toggle($task);

        return response()->json($this->taskResource($task));
    }

    // ─── API: Archive ─────────────────────────────────────────────

    public function archive(string $id): JsonResponse
    {
        $task = $this->resolveTask($id);
        $task = $this->service->archive($task);

        return response()->json($this->taskResource($task));
    }

    // ─── API: Restore ─────────────────────────────────────────────

    public function restore(string $id): JsonResponse
    {
        $task = $this->resolveTask($id);
        $task = $this->service->restore($task);

        return response()->json($this->taskResource($task));
    }

    // ─── API: Delete task ─────────────────────────────────────────

    public function destroy(string $id): \Illuminate\Http\Response
    {
        $task = $this->resolveTask($id);
        $task->delete();

        return response()->noContent();
    }

    // ─── API: Bulk action ─────────────────────────────────────────

    public function bulkAction(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'    => 'required|array|min:1|max:100',
            'ids.*'  => 'uuid',
            'action' => 'required|string|in:done,archive,delete',
        ]);

        $plan = $this->resolveOrCreatePlan();

        $tasks = ChecklistTask::whereIn('id', $data['ids'])
            ->where('wedding_plan_id', $plan->id)
            ->get();

        foreach ($tasks as $task) {
            if ($data['action'] === 'done' && ! $task->isDone() && ! $task->isArchived()) {
                $this->service->toggle($task);
            } elseif ($data['action'] === 'archive' && ! $task->isArchived()) {
                $this->service->archive($task);
            } elseif ($data['action'] === 'delete') {
                $task->delete();
            }
        }

        return response()->json(['affected' => $tasks->count()]);
    }

    // ─── API: Update event date ───────────────────────────────────

    public function updateEventDate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_date' => 'required|date|after_or_equal:today',
        ]);

        $plan = $this->resolveOrCreatePlan();
        $plan->update(['event_date' => $data['event_date']]);

        return response()->json(['event_date' => $plan->event_date->format('Y-m-d')]);
    }

    // ─── API: Summary ─────────────────────────────────────────────

    public function summary(): JsonResponse
    {
        $plan = $this->resolveOrCreatePlan();

        return response()->json($this->service->getSummary($plan));
    }

    // ─── API: Subtasks list ───────────────────────────────────────

    public function subtasks(string $taskId): JsonResponse
    {
        $task = $this->resolveTask($taskId);

        $subtasks = $task->subtasks()->get();

        return response()->json([
            'subtasks' => $subtasks->map(fn ($s) => $this->subtaskResource($s))->values(),
        ]);
    }

    // ─── API: Create subtask ──────────────────────────────────────

    public function storeSubtask(Request $request, string $taskId): JsonResponse
    {
        $task = $this->resolveTask($taskId);

        $data = $request->validate([
            'title' => 'required|string|min:1|max:200',
        ]);

        $maxOrder = $task->subtasks()->max('sort_order') ?? -1;

        $subtask = $task->subtasks()->create([
            'title'      => $data['title'],
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json($this->subtaskResource($subtask), 201);
    }

    // ─── API: Update subtask ──────────────────────────────────────

    public function updateSubtask(Request $request, string $taskId, string $subtaskId): JsonResponse
    {
        $task    = $this->resolveTask($taskId);
        $subtask = $this->resolveSubtask($task, $subtaskId);

        $data = $request->validate([
            'title'        => 'sometimes|string|min:1|max:200',
            'is_completed' => 'sometimes|boolean',
        ]);

        $subtask->update($data);

        return response()->json($this->subtaskResource($subtask->refresh()));
    }

    // ─── API: Delete subtask ──────────────────────────────────────

    public function destroySubtask(string $taskId, string $subtaskId): \Illuminate\Http\Response
    {
        $task    = $this->resolveTask($taskId);
        $subtask = $this->resolveSubtask($task, $subtaskId);
        $subtask->delete();

        return response()->noContent();
    }

    // ─── iCal Export ─────────────────────────────────────────────

    public function exportCalendar(): \Illuminate\Http\Response
    {
        $plan  = $this->resolveOrCreatePlan();
        $tasks = $plan->checklistTasks()
            ->where('status', '!=', \App\Enums\ChecklistTaskStatus::Archived->value)
            ->whereNotNull('due_date')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//TheDay//Checklist//ID', 'CALSCALE:GREGORIAN',
            'X-WR-CALNAME:TheDay — Checklist Pernikahan',
        ];

        foreach ($tasks as $task) {
            $date   = \Carbon\Carbon::parse($task->due_date)->format('Ymd');
            $stamp  = now()->utc()->format('Ymd\THis\Z');
            $catVal = $task->category instanceof \BackedEnum ? $task->category->value : (string) $task->category;
            $desc   = trim($catVal.($task->vendor ? ' · '.$task->vendor : ''));
            $esc    = fn (string $s) => addcslashes($s, ",;\\\n");
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:checklist-'.$task->id.'@theday';
            $lines[] = 'DTSTAMP:'.$stamp;
            $lines[] = 'DTSTART;VALUE=DATE:'.$date;
            $lines[] = 'SUMMARY:'.$esc($task->title);
            if ($desc !== '') {
                $lines[] = 'DESCRIPTION:'.$esc($desc);
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        $body = implode("\r\n", $lines)."\r\n";

        return response($body, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="theday-checklist.ics"',
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function resolveOrCreatePlan(): WeddingPlan
    {
        return WeddingPlan::firstOrCreate(
            ['user_id' => EffectiveUser::resolve()->id]
        );
    }

    private function resolveTask(string $id): ChecklistTask
    {
        $task = ChecklistTask::findOrFail($id);

        if ($task->weddingPlan->user_id !== EffectiveUser::resolve()->id) {
            abort(403);
        }

        return $task;
    }

    private function resolveSubtask(ChecklistTask $task, string $subtaskId): ChecklistSubtask
    {
        $subtask = ChecklistSubtask::findOrFail($subtaskId);

        if ($subtask->task_id !== $task->id) {
            abort(403);
        }

        return $subtask;
    }

    private function taskResource(ChecklistTask $task): array
    {
        return [
            'id'                   => $task->id,
            'source'               => $task->source->value,
            'title'                => $task->title,
            'description'          => $task->description,
            'vendor'               => $task->vendor,
            'category'             => $task->category->value,
            'priority'             => $task->priority->value,
            'status'               => $task->status->value,
            'due_date'             => $task->due_date?->format('Y-m-d'),
            'assignee_type'        => $task->assignee_type,
            'assignee_label'       => $task->assignee_label,
            'reminder_enabled'     => $task->reminder_enabled,
            'reminder_offset_days' => $task->reminder_offset_days,
            'subtasks_count'       => $task->subtasks_count ?? 0,
            'subtasks_done_count'  => $task->subtasks_done_count ?? 0,
            'sort_order'           => $task->sort_order,
            'is_user_modified'     => $task->is_user_modified,
            'completed_at'         => $task->completed_at?->toISOString(),
            'archived_at'          => $task->archived_at?->toISOString(),
            'created_at'           => $task->created_at->toISOString(),
        ];
    }

    private function subtaskResource(ChecklistSubtask $subtask): array
    {
        return [
            'id'           => $subtask->id,
            'title'        => $subtask->title,
            'is_completed' => $subtask->is_completed,
            'sort_order'   => $subtask->sort_order,
        ];
    }
}
