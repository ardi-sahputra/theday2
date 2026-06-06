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
            'tasks'              => ['required', 'array', 'min:1', 'max:25'],
            'tasks.*.title'      => ['required', 'string', 'max:200'],
            'tasks.*.category'   => ['nullable', 'string', 'max:40'],
            'tasks.*.priority'   => ['nullable', 'string', 'max:20'],
            'tasks.*.due_date'   => ['nullable', 'date'],
        ]);

        $plan = WeddingPlan::firstOrCreate(['user_id' => EffectiveUser::resolve()->id]);

        $existing = $plan->checklistTasks()
            ->where('status', '!=', ChecklistTaskStatus::Archived->value)
            ->pluck('title')
            ->map(fn ($t) => mb_strtolower(trim($t)))
            ->all();

        $validCat = array_map(fn ($c) => $c->value, ChecklistTaskCategory::cases());
        $validPri = array_map(fn ($p) => $p->value, ChecklistTaskPriority::cases());

        $created = 0;
        foreach ($data['tasks'] as $t) {
            $title = trim($t['title']);
            if ($title === '' || in_array(mb_strtolower($title), $existing, true)) {
                continue;
            }

            $this->service->createTask($plan, [
                'title'    => $title,
                'category' => in_array($t['category'] ?? '', $validCat, true) ? $t['category'] : 'lainnya',
                'priority' => in_array($t['priority'] ?? '', $validPri, true) ? $t['priority'] : 'medium',
                'due_date' => $t['due_date'] ?? null,
            ]);
            $existing[] = mb_strtolower($title);
            $created++;
        }

        return response()->json(['created' => $created]);
    }
}
