<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Actions\Planner\GenerateChecklistDraftAction;
use App\Enums\ChecklistTaskCategory;
use App\Enums\ChecklistTaskPriority;
use App\Enums\ChecklistTaskStatus;
use App\Http\Controllers\Controller;
use App\Models\WeddingPlan;
use App\Services\ChecklistService;
use App\Support\EffectiveUser;
use App\Support\TaskTitleMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistAiController extends Controller
{
    public function __construct(private readonly ChecklistService $service) {}

    public function draft(Request $request, GenerateChecklistDraftAction $action): JsonResponse
    {
        $data = $request->validate([
            'adat'  => ['nullable', 'string', 'max:40'],
            'skala' => ['nullable', 'string', 'max:40'],
            'gaya'  => ['nullable', 'string', 'max:40'],
        ]);

        $plan = WeddingPlan::firstOrCreate(['user_id' => EffectiveUser::resolve()->id]);

        return response()->json($action->execute($plan, $data));
    }

    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mode'               => ['nullable', 'in:merge,replace'],
            'tasks'              => ['required', 'array', 'min:1', 'max:25'],
            'tasks.*.title'      => ['required', 'string', 'max:200'],
            'tasks.*.category'   => ['nullable', 'string', 'max:40'],
            'tasks.*.priority'   => ['nullable', 'string', 'max:20'],
            'tasks.*.due_date'   => ['nullable', 'date'],
        ]);

        $plan = WeddingPlan::firstOrCreate(['user_id' => EffectiveUser::resolve()->id]);
        $mode = $data['mode'] ?? 'merge';

        // Replace: archive every active task first so the AI list becomes the
        // whole checklist. Archived (not deleted) keeps it recoverable.
        $archived = 0;
        if ($mode === 'replace') {
            $archived = $plan->checklistTasks()
                ->where('status', '!=', ChecklistTaskStatus::Archived->value)
                ->update(['status' => ChecklistTaskStatus::Archived->value]);
        }

        // Fuzzy duplicate guard against whatever remains active (empty after a
        // replace, so all tasks insert).
        $existing = $plan->checklistTasks()
            ->where('status', '!=', ChecklistTaskStatus::Archived->value)
            ->pluck('title')
            ->all();

        $validCat = array_map(fn ($c) => $c->value, ChecklistTaskCategory::cases());
        $validPri = array_map(fn ($p) => $p->value, ChecklistTaskPriority::cases());

        $created = 0;
        foreach ($data['tasks'] as $t) {
            $title = trim($t['title']);
            if ($title === '' || TaskTitleMatcher::isDuplicate($title, $existing)) {
                continue;
            }

            $this->service->createTask($plan, [
                'title'    => $title,
                'category' => in_array($t['category'] ?? '', $validCat, true) ? $t['category'] : 'lainnya',
                'priority' => in_array($t['priority'] ?? '', $validPri, true) ? $t['priority'] : 'medium',
                'due_date' => $t['due_date'] ?? null,
            ]);
            $existing[] = $title;
            $created++;
        }

        return response()->json(['created' => $created, 'archived' => $archived]);
    }
}
