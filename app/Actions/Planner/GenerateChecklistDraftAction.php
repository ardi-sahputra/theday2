<?php

declare(strict_types=1);

namespace App\Actions\Planner;

use App\Enums\ChecklistTaskCategory;
use App\Enums\ChecklistTaskPriority;
use App\Enums\ChecklistTaskStatus;
use App\Models\Invitation;
use App\Models\WeddingPlan;
use App\Services\DeepSeekClient;
use App\Support\TaskTitleMatcher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Generate a personalized checklist DRAFT (not persisted) for preview. The couple
 * picks which tasks to apply afterwards. Anti-halu: categories/priorities are
 * coerced to valid enums, offsets clamped, and tasks they already have are removed.
 */
final class GenerateChecklistDraftAction
{
    private const DAILY_CAP = 20;
    private const MAX_TASKS = 25;
    private const MIN_OFFSET = -540;

    public function __construct(private readonly DeepSeekClient $deepseek) {}

    /**
     * @param  array<string, mixed>  $inputs  adat, skala, gaya
     * @return array{enabled:bool, tasks:array<int,array<string,mixed>>, limited?:bool}
     */
    public function execute(WeddingPlan $plan, array $inputs): array
    {
        if (! $this->deepseek->configured()) {
            return ['enabled' => false, 'tasks' => []];
        }
        if (! $this->withinDailyQuota($plan->user_id)) {
            return ['enabled' => true, 'tasks' => [], 'limited' => true];
        }

        $existing = $plan->checklistTasks()
            ->where('status', '!=', ChecklistTaskStatus::Archived->value)
            ->pluck('title')
            ->all();

        $weddingType = $plan->primaryInvitation?->wedding_type
            ?? Invitation::where('user_id', $plan->user_id)->value('wedding_type');

        $daysToGo = $plan->event_date !== null
            ? (int) round(Carbon::today()->diffInDays($plan->event_date, false))
            : null;

        $context = [
            'adat'               => $inputs['adat']  ?? 'Umum',
            'skala_tamu'         => $inputs['skala'] ?? null,
            'gaya'               => $inputs['gaya']  ?? null,
            'wedding_type'       => $weddingType,
            'hari_menuju_hari_h' => $daysToGo,
            'task_sudah_ada'     => $existing,
            'kategori_valid'     => array_map(fn ($c) => $c->value, ChecklistTaskCategory::cases()),
        ];

        $result = $this->deepseek->jsonCompletion(
            $this->systemPrompt(),
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            1200,
        );

        return ['enabled' => true, 'tasks' => $this->normalize($result, $existing, $plan->event_date)];
    }

    private function withinDailyQuota(?string $userId): bool
    {
        if ($userId === null) {
            return false;
        }
        $key = 'checklist_draft_quota:'.$userId.':'.now()->format('Y-m-d');
        Cache::add($key, 0, now()->addDay());

        return Cache::increment($key) <= self::DAILY_CAP;
    }

    /**
     * @param  array<string, mixed>|null  $result
     * @param  array<int, string>  $existing
     * @return array<int, array<string, mixed>>
     */
    private function normalize(?array $result, array $existing, ?Carbon $eventDate): array
    {
        $items = $result['tasks'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $validCat = array_map(fn ($c) => $c->value, ChecklistTaskCategory::cases());
        $validPri = array_map(fn ($p) => $p->value, ChecklistTaskPriority::cases());

        // Keep duplicates but FLAG them. "Merge" hides flagged tasks (they
        // already exist); "replace" archives the old set and re-adds the full
        // list, so flagged tasks must survive or the checklist would shrink.
        return collect($items)
            ->filter(fn ($i) => is_array($i) && ! empty(trim((string) ($i['title'] ?? ''))))
            ->take(self::MAX_TASKS)
            ->map(function (array $i) use ($validCat, $validPri, $eventDate, $existing): array {
                $title = mb_substr(trim((string) $i['title']), 0, 200);
                $offset = (int) ($i['day_offset'] ?? 0);
                $offset = max(self::MIN_OFFSET, min(0, $offset));
                $dueDate = $eventDate !== null
                    ? $eventDate->copy()->addDays($offset)->format('Y-m-d')
                    : null;

                return [
                    'title'        => $title,
                    'category'     => in_array($i['category'] ?? '', $validCat, true) ? $i['category'] : 'lainnya',
                    'priority'     => in_array($i['priority'] ?? '', $validPri, true) ? $i['priority'] : 'medium',
                    'day_offset'   => $offset,
                    'due_date'     => $dueDate,
                    'is_duplicate' => TaskTitleMatcher::isDuplicate($title, $existing),
                ];
            })
            ->values()
            ->all();
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Kamu perencana pernikahan untuk aplikasi TheDay. Buatkan checklist persiapan yang
DIPERSONALISASI dari data JSON pasangan (Bahasa Indonesia).

ATURAN:
- Hasilkan 12-20 task konkret, relevan dengan adat, skala tamu, gaya, dan wedding_type.
- "category" WAJIB salah satu dari daftar "kategori_valid". Jangan buat kategori lain.
- "priority" hanya: low, medium, high.
- "day_offset" = jumlah hari relatif ke hari H (negatif = sebelum, 0 = hari H). Contoh: -180 = H-180.
- JANGAN ulang task yang sudah ada di "task_sudah_ada".
- Anti-halusinasi: jangan mengarang nama vendor, harga, atau tanggal spesifik. Task = aksi umum.
- Sesuaikan urutan/timing dengan "hari_menuju_hari_h" jika ada.

Balas HANYA JSON:
{
  "tasks": [
    { "title": "judul task", "category": "<kategori_valid>", "priority": "low|medium|high", "day_offset": -180 }
  ]
}
PROMPT;
    }
}
