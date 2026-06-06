# AI Checklist Generator — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:subagent-driven-development. Steps use `- [ ]` checkboxes.

**Goal:** Replace the 3 fake preset cards in the Wedding Planner's "Template Theday" rail with a real AI Checklist Generator (input → AI draft → preview/select → apply), anti-duplicate and preview-gated.

**Architecture:** A draft action calls DeepSeek with the couple's inputs + wedding_type + days-to-go + existing task titles, normalizes/dedupes the result (no persistence — it's a preview draft). Two endpoints: `ai-draft` (generate) and `ai-apply` (create selected tasks via the existing `ChecklistService`). Frontend modal drives the flow.

**Tech Stack:** Laravel 11, Inertia/Vue 3, DeepSeek (`App\Services\DeepSeekClient`), PHPUnit, Vite.

**Spec:** `docs/superpowers/specs/2026-06-04-ai-checklist-generator-design.md`

**Reuse:** `DeepSeekClient`, `ChecklistService::createTask`, the quota-counter pattern from `BuildPlannerInsightAction`, enums `ChecklistTaskCategory` (administrasi/venue/vendor/undangan/keuangan/busana/dekorasi/dokumentasi/tamu/acara/lainnya) and `ChecklistTaskPriority` (low/medium/high).

---

## File Structure

**Create:**
- `app/Actions/Planner/GenerateChecklistDraftAction.php`
- `app/Http/Controllers/Dashboard/ChecklistAiController.php`
- `resources/js/Components/dashboard/checklist/AiChecklistModal.vue`
- `tests/Feature/Dashboard/ChecklistGeneratorTest.php`

**Modify:**
- `routes/web.php` — two routes in the checklist group
- `resources/js/Components/dashboard/checklist/rail/TemplatePresetsRail.vue` — drop dummies, add AI button
- `resources/js/Pages/Dashboard/Checklist/Index.vue` — handle the AI flow
- `lang/id.json`, `lang/en.json`

---

## Task 1: GenerateChecklistDraftAction + test

**Files:**
- Create: `app/Actions/Planner/GenerateChecklistDraftAction.php`
- Test: `tests/Feature/Dashboard/ChecklistGeneratorTest.php`

- [ ] **Step 1: Write the failing test** (`tests/Feature/Dashboard/ChecklistGeneratorTest.php`):

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\Planner\GenerateChecklistDraftAction;
use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChecklistGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private function fakeDraft(array $tasks): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode(['tasks' => $tasks])]]],
            ], 200),
        ]);
    }

    public function test_disabled_when_no_api_key(): void
    {
        config(['services.deepseek.key' => null]);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::create(['user_id' => $user->id]);

        $out = app(GenerateChecklistDraftAction::class)->execute($plan, ['adat' => 'Jawa']);

        $this->assertFalse($out['enabled']);
        $this->assertSame([], $out['tasks']);
        Http::assertNothingSent();
    }

    public function test_normalizes_and_dedupes(): void
    {
        config(['services.deepseek.key' => 'k']);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::create([
            'user_id' => $user->id,
            'event_date' => now()->addDays(200)->format('Y-m-d'),
        ]);
        // Existing task that must be deduped out of the draft.
        $plan->checklistTasks()->create([
            'source' => 'system', 'title' => 'Booking Venue', 'category' => 'venue',
            'priority' => 'high', 'status' => 'todo',
        ]);

        $this->fakeDraft([
            ['title' => 'Booking Venue',  'category' => 'venue',   'priority' => 'high',   'day_offset' => -300], // dup → dropped
            ['title' => 'Fitting Busana', 'category' => 'busana',  'priority' => 'medium', 'day_offset' => -60],  // ok
            ['title' => 'Tugas Aneh',     'category' => 'nonsense','priority' => 'urgent', 'day_offset' => -9999],// invalid → coerced
            ['title' => '',               'category' => 'acara',   'priority' => 'low',    'day_offset' => -10],  // empty → dropped
        ]);

        $out = app(GenerateChecklistDraftAction::class)->execute($plan, ['adat' => 'Jawa', 'skala' => 'sedang']);

        $this->assertTrue($out['enabled']);
        $titles = array_column($out['tasks'], 'title');
        $this->assertContains('Fitting Busana', $titles);
        $this->assertNotContains('Booking Venue', $titles); // deduped
        $this->assertNotContains('', $titles);              // empty dropped

        $aneh = collect($out['tasks'])->firstWhere('title', 'Tugas Aneh');
        $this->assertSame('lainnya', $aneh['category']);   // invalid category coerced
        $this->assertSame('medium', $aneh['priority']);    // invalid priority coerced
        $this->assertGreaterThanOrEqual(-540, $aneh['day_offset']); // clamped

        // due_date computed from event_date + offset for a kept task
        $fitting = collect($out['tasks'])->firstWhere('title', 'Fitting Busana');
        $this->assertNotNull($fitting['due_date']);
    }
}
```

- [ ] **Step 2: Run** `php artisan test --filter=ChecklistGeneratorTest` → FAIL (class not found).

- [ ] **Step 3: Create the action** (`app/Actions/Planner/GenerateChecklistDraftAction.php`):

```php
<?php

declare(strict_types=1);

namespace App\Actions\Planner;

use App\Enums\ChecklistTaskCategory;
use App\Enums\ChecklistTaskPriority;
use App\Enums\ChecklistTaskStatus;
use App\Models\Invitation;
use App\Models\WeddingPlan;
use App\Services\DeepSeekClient;
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
        $existingLower = array_map(fn ($t) => mb_strtolower(trim($t)), $existing);

        return collect($items)
            ->filter(fn ($i) => is_array($i) && ! empty(trim((string) ($i['title'] ?? ''))))
            ->reject(fn ($i) => in_array(mb_strtolower(trim((string) $i['title'])), $existingLower, true))
            ->take(self::MAX_TASKS)
            ->map(function (array $i) use ($validCat, $validPri, $eventDate): array {
                $offset = (int) ($i['day_offset'] ?? 0);
                $offset = max(self::MIN_OFFSET, min(0, $offset));
                $dueDate = $eventDate !== null
                    ? $eventDate->copy()->addDays($offset)->format('Y-m-d')
                    : null;

                return [
                    'title'      => mb_substr(trim((string) $i['title']), 0, 200),
                    'category'   => in_array($i['category'] ?? '', $validCat, true) ? $i['category'] : 'lainnya',
                    'priority'   => in_array($i['priority'] ?? '', $validPri, true) ? $i['priority'] : 'medium',
                    'day_offset' => $offset,
                    'due_date'   => $dueDate,
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
```

- [ ] **Step 4: Run** `php artisan test --filter=ChecklistGeneratorTest` → PASS (2 tests). Debug without changing assertions.

- [ ] **Step 5: Commit**

```bash
git add app/Actions/Planner/GenerateChecklistDraftAction.php tests/Feature/Dashboard/ChecklistGeneratorTest.php
git commit -m "feat(checklist): AI checklist draft action (DeepSeek, normalize, dedupe)"
```

---

## Task 2: ChecklistAiController (draft + apply) + routes + test

**Files:**
- Create: `app/Http/Controllers/Dashboard/ChecklistAiController.php`
- Modify: `routes/web.php`
- Test: append to `tests/Feature/Dashboard/ChecklistGeneratorTest.php`

- [ ] **Step 1: Append failing endpoint tests** to `tests/Feature/Dashboard/ChecklistGeneratorTest.php` (inside the class):

```php
    public function test_draft_endpoint_returns_tasks(): void
    {
        config(['services.deepseek.key' => 'k']);
        $this->fakeDraft([
            ['title' => 'Fitting Busana', 'category' => 'busana', 'priority' => 'medium', 'day_offset' => -60],
        ]);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $res = $this->actingAs($user)->postJson(route('dashboard.checklist.ai-draft'), [
            'adat' => 'Jawa', 'skala' => 'sedang', 'gaya' => 'formal',
        ]);

        $res->assertOk()->assertJsonPath('enabled', true)->assertJsonCount(1, 'tasks');
    }

    public function test_apply_endpoint_creates_selected_tasks(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $res = $this->actingAs($user)->postJson(route('dashboard.checklist.ai-apply'), [
            'tasks' => [
                ['title' => 'Fitting Busana', 'category' => 'busana', 'priority' => 'medium', 'due_date' => now()->addDays(140)->format('Y-m-d')],
                ['title' => 'Bad Cat',        'category' => 'nonsense','priority' => 'low',   'due_date' => null],
            ],
        ]);

        $res->assertOk()->assertJsonPath('created', 2);
        $this->assertDatabaseHas('checklist_tasks', ['title' => 'Fitting Busana', 'category' => 'busana']);
        // invalid category coerced to lainnya
        $this->assertDatabaseHas('checklist_tasks', ['title' => 'Bad Cat', 'category' => 'lainnya']);
    }

    public function test_apply_skips_duplicates(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = \App\Models\WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->checklistTasks()->create([
            'source' => 'system', 'title' => 'Fitting Busana', 'category' => 'busana',
            'priority' => 'high', 'status' => 'todo',
        ]);

        $res = $this->actingAs($user)->postJson(route('dashboard.checklist.ai-apply'), [
            'tasks' => [['title' => 'Fitting Busana', 'category' => 'busana', 'priority' => 'medium', 'due_date' => null]],
        ]);

        $res->assertOk()->assertJsonPath('created', 0); // duplicate skipped
    }
```

- [ ] **Step 2: Run** `php artisan test --filter=ChecklistGeneratorTest` → the 3 new tests FAIL (route not defined).

- [ ] **Step 3: Create the controller** (`app/Http/Controllers/Dashboard/ChecklistAiController.php`):

```php
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
                continue; // skip duplicates
            }

            $this->service->createTask($plan, [
                'title'    => $title,
                'category' => in_array($t['category'] ?? '', $validCat, true) ? $t['category'] : 'lainnya',
                'priority' => in_array($t['priority'] ?? '', $validPri, true) ? $t['priority'] : 'medium',
                'due_date' => $t['due_date'] ?? null,
            ]);
            $existing[] = mb_strtolower($title); // guard against in-batch dupes
            $created++;
        }

        return response()->json(['created' => $created]);
    }
}
```

- [ ] **Step 4: Add routes.** In `routes/web.php`, inside the checklist group (after the `checklist.event-date` line), add:

```php
    Route::post( '/checklist/ai-draft', [\App\Http\Controllers\Dashboard\ChecklistAiController::class, 'draft'])->middleware('throttle:10,1')->name('checklist.ai-draft');
    Route::post( '/checklist/ai-apply', [\App\Http\Controllers\Dashboard\ChecklistAiController::class, 'apply'])->middleware('throttle:20,1')->name('checklist.ai-apply');
```

- [ ] **Step 5: Run** `php artisan test --filter=ChecklistGeneratorTest` → all 5 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Dashboard/ChecklistAiController.php routes/web.php tests/Feature/Dashboard/ChecklistGeneratorTest.php
git commit -m "feat(checklist): AI draft/apply endpoints"
```

---

## Task 3: Frontend — rail button, modal, wiring, i18n

**Files:**
- Modify: `resources/js/Components/dashboard/checklist/rail/TemplatePresetsRail.vue`
- Create: `resources/js/Components/dashboard/checklist/AiChecklistModal.vue`
- Modify: `resources/js/Pages/Dashboard/Checklist/Index.vue`
- Modify: `lang/id.json`, `lang/en.json`

- [ ] **Step 1: Replace the dummies in `TemplatePresetsRail.vue`.** Remove the `dummies` array from `<script setup>` and the `DemoBadge` import. Replace the entire `<div v-for="d in dummies" ...>` block in the template with a single AI button:

```vue
      <button type="button" @click="emit('ai-generate')"
              class="flex items-center justify-between rounded-[10px] px-3 py-2.5 text-left"
              style="background: linear-gradient(135deg, #2B3A33, #1F2A2E);">
        <div>
          <div class="text-[13px] font-semibold flex items-center gap-2" style="color:#FBFCF9;">✨ {{ t('dashboard.checklist.rail.templates.aiTitle') }}</div>
          <div class="text-[11px]" style="color:rgba(251,252,249,0.6);">{{ t('dashboard.checklist.rail.templates.aiSub') }}</div>
        </div>
      </button>
```

Add `'ai-generate'` to the component's `defineEmits([...])` list (it currently emits `'apply'`).

- [ ] **Step 2: Create `AiChecklistModal.vue`** (`resources/js/Components/dashboard/checklist/AiChecklistModal.vue`):

```vue
<script setup>
import { ref } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const emit = defineEmits(['close', 'applied']);
const { t } = useLocale();

const ADAT  = ['Umum', 'Jawa', 'Sunda', 'Minang', 'Bali', 'Batak', 'Lainnya'];
const SKALA = [
  { v: 'intimate', l: 'Intimate (<100)' },
  { v: 'sedang',   l: 'Sedang (100–300)' },
  { v: 'besar',    l: 'Besar (300+)' },
];
const GAYA  = ['Formal', 'Intimate', 'Destination', 'Adat-kental'];

const step    = ref('input'); // input | loading | preview
const form    = ref({ adat: 'Umum', skala: 'sedang', gaya: '' });
const tasks   = ref([]);      // [{title, category, priority, due_date, _checked}]
const error   = ref('');
const applying = ref(false);

async function generate() {
  step.value = 'loading';
  error.value = '';
  try {
    const { data } = await window.axios.post(route('dashboard.checklist.ai-draft'), form.value);
    if (data.enabled === false) { error.value = t('dashboard.checklist.ai.disabled'); step.value = 'input'; return; }
    if (data.limited)           { error.value = t('dashboard.checklist.ai.limited');  step.value = 'input'; return; }
    tasks.value = (data.tasks ?? []).map(x => ({ ...x, _checked: true }));
    step.value = 'preview';
  } catch {
    error.value = t('dashboard.checklist.ai.failed');
    step.value = 'input';
  }
}

async function apply() {
  const selected = tasks.value.filter(x => x._checked);
  if (!selected.length) { emit('close'); return; }
  applying.value = true;
  try {
    await window.axios.post(route('dashboard.checklist.ai-apply'), {
      tasks: selected.map(({ title, category, priority, due_date }) => ({ title, category, priority, due_date })),
    });
    emit('applied');
  } catch {
    error.value = t('dashboard.checklist.ai.failed');
  } finally {
    applying.value = false;
  }
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="emit('close')" />
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[85vh] flex flex-col">
      <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
        <h3 class="text-base font-semibold text-stone-800">✨ {{ t('dashboard.checklist.ai.title') }}</h3>
        <button @click="emit('close')" class="p-1 text-stone-400 hover:text-stone-600">✕</button>
      </div>

      <div class="flex-1 overflow-y-auto px-5 py-4">
        <p v-if="error" class="mb-3 text-xs text-rose-500">{{ error }}</p>

        <!-- INPUT -->
        <template v-if="step === 'input'">
          <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.checklist.ai.adat') }}</label>
          <select v-model="form.adat" class="w-full mb-3 px-3 py-2 text-sm border border-stone-200 rounded-xl bg-white">
            <option v-for="a in ADAT" :key="a" :value="a">{{ a }}</option>
          </select>

          <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.checklist.ai.scale') }}</label>
          <div class="flex gap-2 mb-3">
            <button v-for="s in SKALA" :key="s.v" type="button" @click="form.skala = s.v"
                    :class="['flex-1 py-2 text-xs rounded-xl border', form.skala === s.v ? 'border-transparent text-white' : 'border-stone-200 text-stone-600']"
                    :style="form.skala === s.v ? 'background:#92A89C' : ''">{{ s.l }}</button>
          </div>

          <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.checklist.ai.style') }}</label>
          <div class="flex flex-wrap gap-2">
            <button v-for="g in GAYA" :key="g" type="button" @click="form.gaya = form.gaya === g ? '' : g"
                    :class="['px-3 py-1.5 text-xs rounded-xl border', form.gaya === g ? 'border-transparent text-white' : 'border-stone-200 text-stone-600']"
                    :style="form.gaya === g ? 'background:#92A89C' : ''">{{ g }}</button>
          </div>
        </template>

        <!-- LOADING -->
        <div v-else-if="step === 'loading'" class="py-8 text-center text-sm text-stone-500">
          {{ t('dashboard.checklist.ai.generating') }}
        </div>

        <!-- PREVIEW -->
        <template v-else>
          <p v-if="!tasks.length" class="text-sm text-stone-500">{{ t('dashboard.checklist.ai.allExist') }}</p>
          <div v-else class="space-y-1.5">
            <label v-for="(tk, i) in tasks" :key="i" class="flex items-start gap-2.5 p-2 rounded-lg hover:bg-stone-50 cursor-pointer">
              <input type="checkbox" v-model="tk._checked" class="mt-0.5" />
              <div>
                <div class="text-[13px] text-stone-800">{{ tk.title }}</div>
                <div class="text-[11px] text-stone-400">{{ tk.category }}<span v-if="tk.due_date"> · {{ tk.due_date }}</span></div>
              </div>
            </label>
          </div>
        </template>
      </div>

      <div class="px-5 py-4 border-t border-stone-100 flex gap-2">
        <button @click="emit('close')" class="flex-1 py-2.5 text-sm text-stone-600 border border-stone-200 rounded-xl">{{ t('common.cancel') }}</button>
        <button v-if="step === 'input'" @click="generate" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#92A89C">{{ t('dashboard.checklist.ai.generate') }}</button>
        <button v-else-if="step === 'preview' && tasks.length" @click="apply" :disabled="applying" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#92A89C">{{ t('dashboard.checklist.ai.add') }}</button>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 3: Wire into `Checklist/Index.vue`.** Add the import near the other component imports:
```js
import AiChecklistModal from '@/Components/dashboard/checklist/AiChecklistModal.vue';
```
Add a reactive flag in `<script setup>` (near other `ref(...)` UI state):
```js
const showAiModal = ref(false);
```
Find where `<TemplatePresetsRail ... />` is rendered and add the handler `@ai-generate="showAiModal = true"` to it. Then add the modal near the other modals/sheets in the template:
```vue
        <AiChecklistModal v-if="showAiModal" @close="showAiModal = false" @applied="showAiModal = false; reloadTasks()" />
```
Note: the page already has a function that fetches tasks after mutations (e.g. it calls the tasks endpoint / reloads on create). Use the SAME existing reload mechanism the other task mutations use — search the file for how `store`/`toggle` refresh the list (likely a `loadTasks()`/`fetchTasks()` function or `router.reload`). Call that in the `@applied` handler instead of `reloadTasks()` if the real function has a different name.

- [ ] **Step 4: Add i18n.** In BOTH `lang/id.json` and `lang/en.json`, under `dashboard.checklist.rail.templates` add `aiTitle`/`aiSub`, and add a new `dashboard.checklist.ai` object. Keep JSON valid.

`lang/id.json` — `rail.templates` gets: `"aiTitle": "Buatkan dengan AI", "aiSub": "Checklist personal sesuai pernikahanmu"`. New `ai` block under `dashboard.checklist`:
```json
"ai": {
  "title": "Buat Checklist dengan AI",
  "adat": "Adat / budaya",
  "scale": "Skala tamu",
  "style": "Gaya (opsional)",
  "generate": "Buatkan",
  "generating": "Menyusun checklist untukmu…",
  "add": "Tambahkan terpilih",
  "allExist": "Checklist kamu sudah lengkap — tidak ada usulan baru.",
  "disabled": "Fitur AI belum aktif.",
  "limited": "Batas pembuatan harian tercapai. Coba lagi besok.",
  "failed": "Gagal membuat checklist. Coba lagi."
}
```
`lang/en.json` — `rail.templates`: `"aiTitle": "Generate with AI", "aiSub": "A personal checklist for your wedding"`. New `ai` block:
```json
"ai": {
  "title": "Generate Checklist with AI",
  "adat": "Tradition / culture",
  "scale": "Guest scale",
  "style": "Style (optional)",
  "generate": "Generate",
  "generating": "Building your checklist…",
  "add": "Add selected",
  "allExist": "Your checklist is already complete — no new suggestions.",
  "disabled": "AI feature is not enabled.",
  "limited": "Daily generation limit reached. Try again tomorrow.",
  "failed": "Failed to generate. Please try again."
}
```

- [ ] **Step 5: Validate JSON + build.**
- `php -r "json_decode(file_get_contents('lang/id.json')); echo json_last_error_msg().PHP_EOL; json_decode(file_get_contents('lang/en.json')); echo json_last_error_msg().PHP_EOL;"` → `No error` twice.
- `npm run build` → no Vue errors.

- [ ] **Step 6: Commit**

```bash
git add resources/js lang/id.json lang/en.json public/build
git commit -m "feat(checklist): AI generator rail button + preview modal + i18n"
```

---

## Task 4: Verify

- [ ] **Step 1:** `php artisan test --filter="ChecklistGenerator|Checklist|Planner"` → all PASS.
- [ ] **Step 2 (optional live):** with a key set, `php artisan tinker --execute="$u=App\Models\User::first(); $p=App\Models\WeddingPlan::firstOrCreate(['user_id'=>$u->id]); print_r(app(App\Actions\Planner\GenerateChecklistDraftAction::class)->execute($p, ['adat'=>'Jawa','skala'=>'sedang']));"` → tasks with valid categories, clamped offsets, deduped.
- [ ] **Step 3:** Confirm the 3 fake preset cards are gone in the UI and "Paket Standar 12 Bulan" still applies.

---

## Notes
- **No persistence of the draft** — it lives only in the modal until applied.
- **Preview gate is mandatory** — `apply` is the only path that writes tasks.
- **Dedupe twice** — in the draft action and again in `apply` (covers concurrent edits).
