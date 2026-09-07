# Checklist "Fokus Sekarang" Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the Checklist page open to a small "Fokus Sekarang" set (5–8 relevant tasks) instead of the full task wall, to reduce couple overwhelm.

**Architecture:** A pure function `selectFocusTasks` picks the focus set from the couple's active tasks using the existing deadline buckets (overdue/today/week/month). The Checklist page gains a `focusScope` state ('focus' | 'all'); when 'focus', the existing `displayList` is narrowed to the focus set, so all existing rendering (timeline groups, task rows, mobile buckets) is reused unchanged. A momentum header reframes progress honestly.

**Tech Stack:** Vue 3 (Inertia), Tailwind, Vitest + @vue/test-utils, Laravel i18n JSON (`lang/id.json`, `lang/en.json`).

**Branch:** `feat/checklist-focus-now` (already created off clean `develop`; spec committed there).

---

## File Structure

- **Create** `resources/js/Pages/Dashboard/Checklist/selectFocusTasks.js` — pure focus-selection logic (no Vue deps). One responsibility: given tasks + now, return `{ tasks, mode }`.
- **Create** `resources/js/Pages/Dashboard/Checklist/selectFocusTasks.test.js` — colocated Vitest unit tests.
- **Modify** `resources/js/Pages/Dashboard/Checklist/Index.vue` — add `focusScope` state (persisted), `focusResult` computed, narrow `displayList`, render toggle + momentum header + mode banners + "Lihat semua" footer; pass momentum + scope props/events to `MobileChecklist`.
- **Modify** `resources/js/Components/dashboard/checklist/mobile/MobileChecklist.vue` — add Fokus/Semua toggle + momentum line (display only; logic stays in Index).
- **Modify** `lang/id.json` and `lang/en.json` — strings under `dashboard.checklist.focus.*`.

---

## Task 1: Pure focus-selection function (TDD)

**Files:**
- Create: `resources/js/Pages/Dashboard/Checklist/selectFocusTasks.js`
- Test: `resources/js/Pages/Dashboard/Checklist/selectFocusTasks.test.js`

- [ ] **Step 1: Write the failing test**

Create `resources/js/Pages/Dashboard/Checklist/selectFocusTasks.test.js`:

```js
import { describe, it, expect } from 'vitest';
import { selectFocusTasks } from './selectFocusTasks';

// Fixed "today" so due-date math is deterministic.
const NOW = new Date('2026-06-14T09:00:00');

// Helper: build a task with a due date N days from NOW (null = no date).
function task(id, offsetDays, extra = {}) {
  let due = null;
  if (offsetDays !== null) {
    const d = new Date('2026-06-14T00:00:00');
    d.setDate(d.getDate() + offsetDays);
    due = d.toISOString().slice(0, 10);
  }
  return { id, due_date: due, status: 'todo', priority: 'medium', ...extra };
}

describe('selectFocusTasks', () => {
  it('returns due-soon tasks (overdue+today+week) in normal mode', () => {
    const tasks = [
      task(1, -3),  // overdue
      task(2, 0),   // today
      task(3, 5),   // this week
      task(4, 20),  // month — excluded when enough due-soon
      task(5, 60),  // later — excluded
    ];
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW, minCount: 3 });
    expect(mode).toBe('normal');
    expect(focus.map(t => t.id)).toEqual([1, 2, 3]);
  });

  it('sorts overdue before today before week, by due date', () => {
    const tasks = [task(3, 6), task(1, -5), task(2, -1)];
    const { tasks: focus } = selectFocusTasks(tasks, { now: NOW, minCount: 2 });
    expect(focus.map(t => t.id)).toEqual([1, 2, 3]);
  });

  it('tops up from the month bucket when due-soon is below minCount', () => {
    const tasks = [task(1, 0), task(2, 15), task(3, 25), task(4, 60)];
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW, minCount: 3 });
    expect(mode).toBe('normal');
    expect(focus.map(t => t.id)).toEqual([1, 2, 3]); // today + two from month, later excluded
  });

  it('caps and switches to overdueHeavy when overdue exceeds displayCap', () => {
    const tasks = Array.from({ length: 10 }, (_, i) => task(i + 1, -(i + 1)));
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW, displayCap: 8, overdueOnlyCap: 6 });
    expect(mode).toBe('overdueHeavy');
    expect(focus).toHaveLength(6);
  });

  it('is relaxed and shows upcoming month tasks when nothing is due soon', () => {
    const tasks = [task(1, 20), task(2, 25), task(3, 90)];
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW });
    expect(mode).toBe('relaxed');
    expect(focus.map(t => t.id)).toEqual([1, 2]); // month bucket, later excluded
  });

  it('falls back to highest-priority undated tasks when nothing is scheduled', () => {
    const tasks = [
      task(1, null, { priority: 'low' }),
      task(2, null, { priority: 'high' }),
      task(3, null, { priority: 'medium' }),
    ];
    const { tasks: focus, mode } = selectFocusTasks(tasks, { now: NOW });
    expect(mode).toBe('relaxed');
    expect(focus[0].id).toBe(2); // high priority first
  });

  it('ignores done and archived tasks', () => {
    const tasks = [
      task(1, -1, { status: 'done' }),
      task(2, 0, { status: 'archived' }),
      task(3, 2),
    ];
    const { tasks: focus } = selectFocusTasks(tasks, { now: NOW });
    expect(focus.map(t => t.id)).toEqual([3]);
  });

  it('returns an empty set with relaxed mode when there are no active tasks', () => {
    const { tasks: focus, mode } = selectFocusTasks([], { now: NOW });
    expect(focus).toEqual([]);
    expect(mode).toBe('relaxed');
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `rtk npx vitest run resources/js/Pages/Dashboard/Checklist/selectFocusTasks.test.js`
Expected: FAIL — `selectFocusTasks` is not defined / module not found.

- [ ] **Step 3: Write minimal implementation**

Create `resources/js/Pages/Dashboard/Checklist/selectFocusTasks.js`:

```js
// Pure focus-selection for the checklist "Fokus Sekarang" view. No Vue deps so
// it is trivially unit-testable. Mirrors the deadline bucketing used by
// Index.vue's deadlineGroups (overdue / today / week / month / later).

const PRIORITY_ORDER = { high: 0, medium: 1, low: 2 };

function byDueThenPriority(a, b) {
  if (a.due_date && b.due_date) {
    const diff = new Date(a.due_date) - new Date(b.due_date);
    if (diff !== 0) return diff;
  } else if (a.due_date && !b.due_date) {
    return -1;
  } else if (!a.due_date && b.due_date) {
    return 1;
  }
  return (PRIORITY_ORDER[a.priority] ?? 1) - (PRIORITY_ORDER[b.priority] ?? 1);
}

function byPriority(a, b) {
  return (PRIORITY_ORDER[a.priority] ?? 1) - (PRIORITY_ORDER[b.priority] ?? 1);
}

/**
 * @param {Array} tasks  active task objects ({ id, due_date, status, priority })
 * @param {object} opts  { now, minCount, displayCap, overdueOnlyCap }
 * @returns {{ tasks: Array, mode: 'normal'|'overdueHeavy'|'relaxed' }}
 */
export function selectFocusTasks(tasks, opts = {}) {
  const {
    now = new Date(),
    minCount = 5,
    displayCap = 8,
    overdueOnlyCap = 6,
  } = opts;

  const today = new Date(now);
  today.setHours(0, 0, 0, 0);

  const buckets = { overdue: [], today: [], week: [], month: [], later: [] };

  for (const t of tasks) {
    if (t.status === 'done' || t.status === 'archived') continue;
    if (!t.due_date) { buckets.later.push(t); continue; }
    const due = new Date(t.due_date + 'T00:00:00');
    const diff = Math.round((due - today) / 86400000);
    if (diff < 0) buckets.overdue.push(t);
    else if (diff === 0) buckets.today.push(t);
    else if (diff <= 7) buckets.week.push(t);
    else if (diff <= 30) buckets.month.push(t);
    else buckets.later.push(t);
  }

  // Too many overdue tasks is itself overwhelming — show a calm capped slice.
  if (buckets.overdue.length > displayCap) {
    const sorted = [...buckets.overdue].sort(byDueThenPriority);
    return { tasks: sorted.slice(0, overdueOnlyCap), mode: 'overdueHeavy' };
  }

  const dueSoon = [...buckets.overdue, ...buckets.today, ...buckets.week].sort(byDueThenPriority);

  if (dueSoon.length >= minCount) {
    return { tasks: dueSoon.slice(0, displayCap), mode: 'normal' };
  }

  if (dueSoon.length > 0) {
    // Top up from the next window so the list never looks sparse/anxious.
    const topUp = [...buckets.month].sort(byDueThenPriority);
    const combined = [...dueSoon, ...topUp].slice(0, Math.max(minCount, dueSoon.length));
    return { tasks: combined.slice(0, displayCap), mode: 'normal' };
  }

  // Nothing due soon — relaxed. Surface upcoming month tasks, or if nothing is
  // scheduled at all, the highest-priority undated tasks.
  const upcoming = [...buckets.month].sort(byDueThenPriority);
  if (upcoming.length > 0) {
    return { tasks: upcoming.slice(0, displayCap), mode: 'relaxed' };
  }
  const fallback = [...buckets.later].sort(byPriority);
  return { tasks: fallback.slice(0, displayCap), mode: 'relaxed' };
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `rtk npx vitest run resources/js/Pages/Dashboard/Checklist/selectFocusTasks.test.js`
Expected: PASS — all 8 tests green.

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/Pages/Dashboard/Checklist/selectFocusTasks.js resources/js/Pages/Dashboard/Checklist/selectFocusTasks.test.js
rtk git commit -m "feat(checklist): pure selectFocusTasks for Fokus Sekarang view"
```

---

## Task 2: i18n strings

**Files:**
- Modify: `lang/id.json` — add a `focus` block inside `dashboard.checklist`
- Modify: `lang/en.json` — same keys

- [ ] **Step 1: Add Indonesian strings**

In `lang/id.json`, inside the `dashboard.checklist` object (e.g. right after the existing `"ai": { ... }` block), add:

```json
                "focus": {
                    "tab": "Fokus Sekarang",
                    "tabAll": "Semua",
                    "heading": "Fokus Sekarang",
                    "progress": "{done} dari {total} kelar",
                    "onTrack": "Kamu on track ✓",
                    "behind": "{n} tugas telat — yuk kejar",
                    "relaxed": "Minggu ini santai 🎉 — yang akan datang:",
                    "overdueHeavy": "Banyak yang telat — mulai dari yang ini dulu.",
                    "seeAll": "Lihat semua {total} tugas →"
                },
```

- [ ] **Step 2: Add English strings**

In `lang/en.json`, inside `dashboard.checklist`, add:

```json
                "focus": {
                    "tab": "Focus Now",
                    "tabAll": "All",
                    "heading": "Focus Now",
                    "progress": "{done} of {total} done",
                    "onTrack": "You're on track ✓",
                    "behind": "{n} tasks overdue — let's catch up",
                    "relaxed": "Quiet week 🎉 — coming up next:",
                    "overdueHeavy": "A lot is overdue — start with these.",
                    "seeAll": "See all {total} tasks →"
                },
```

- [ ] **Step 3: Validate JSON**

Run: `php -r "json_decode(file_get_contents('lang/id.json')); echo 'id:'.json_last_error_msg().PHP_EOL; json_decode(file_get_contents('lang/en.json')); echo 'en:'.json_last_error_msg().PHP_EOL;"`
Expected: `id:No error` and `en:No error`.

- [ ] **Step 4: Commit**

```bash
rtk git add lang/id.json lang/en.json
rtk git commit -m "i18n(checklist): strings for Fokus Sekarang view"
```

---

## Task 3: Desktop Checklist integration (Index.vue)

**Files:**
- Modify: `resources/js/Pages/Dashboard/Checklist/Index.vue`

- [ ] **Step 1: Import the helper and add focus state**

Near the other imports (after line 12, `import TaskKanban ...`), add:

```js
import { selectFocusTasks } from './selectFocusTasks';
```

After the `activeTasks` computed (line 328), add focus state + result. The default is 'focus'; the choice persists in localStorage so power users who prefer the full list aren't forced back:

```js
const focusScope = ref(
  typeof localStorage !== 'undefined' && localStorage.getItem('checklistFocusScope') === 'all'
    ? 'all'
    : 'focus',
);
function setFocusScope(scope) {
  focusScope.value = scope;
  try { localStorage.setItem('checklistFocusScope', scope); } catch { /* ignore */ }
}

const focusResult = computed(() => selectFocusTasks(activeTasks.value, { now: new Date() }));
const focusIds    = computed(() => new Set(focusResult.value.tasks.map(t => t.id)));

// Honest momentum line: progress count + an overdue-driven status (no fabricated schedule).
const focusStatus = computed(() =>
  summary.value.overdue > 0
    ? { key: 'behind', params: { n: summary.value.overdue } }
    : { key: 'onTrack', params: {} },
);
```

- [ ] **Step 2: Narrow `displayList` when in focus scope**

Replace the existing `displayList` computed (lines 519-523):

```js
const displayList = computed(() => {
    let list = baseList.value;
    if (activeChip.value === 'urgent') list = list.filter(isUrgentTask);
    return list;
});
```

with:

```js
const displayList = computed(() => {
    // Focus scope: show only the curated focus set (timeline view then groups
    // it by deadline automatically — no separate card markup needed).
    if (focusScope.value === 'focus' && activeChip.value === 'all') {
        return focusResult.value.tasks;
    }
    let list = baseList.value;
    if (activeChip.value === 'urgent') list = list.filter(isUrgentTask);
    return list;
});
```

- [ ] **Step 3: Render toggle + momentum header (desktop)**

In the desktop toolbar area, immediately before the `<ChecklistFilterChips ... />` line (line 1066), insert:

```html
                        <!-- Fokus Sekarang vs Semua -->
                        <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                            <div class="inline-flex rounded-xl p-0.5" style="background:#EFE7D6;">
                                <button type="button" @click="setFocusScope('focus')"
                                        class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-colors"
                                        :style="focusScope === 'focus' ? 'background:#1F2A2E; color:#FBFCF9;' : 'color:#6C7A75;'">
                                    {{ t('dashboard.checklist.focus.tab') }}
                                </button>
                                <button type="button" @click="setFocusScope('all')"
                                        class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-colors"
                                        :style="focusScope === 'all' ? 'background:#1F2A2E; color:#FBFCF9;' : 'color:#6C7A75;'">
                                    {{ t('dashboard.checklist.focus.tabAll') }}
                                </button>
                            </div>
                            <p v-if="focusScope === 'focus'" class="text-[12.5px]" style="color:#6C7A75;">
                                <span style="color:#1F2A2E; font-weight:600;">{{ t('dashboard.checklist.focus.progress', { done: summary.done, total: summary.total }) }}</span>
                                · {{ t('dashboard.checklist.focus.' + focusStatus.key, focusStatus.params) }}
                            </p>
                        </div>
```

- [ ] **Step 4: Show mode banner + hide chips in focus scope**

Change the `<ChecklistFilterChips ... />` line (line 1066) so chips only show in "all" scope, and add a mode banner for focus. Replace:

```html
                        <ChecklistFilterChips :chips="filterChips" :active="activeChip" @select="onChip" />
```

with:

```html
                        <ChecklistFilterChips v-if="focusScope === 'all'" :chips="filterChips" :active="activeChip" @select="onChip" />
                        <p v-else-if="focusResult.mode === 'relaxed'" class="text-[12.5px] mb-3" style="color:#8E6515;">{{ t('dashboard.checklist.focus.relaxed') }}</p>
                        <p v-else-if="focusResult.mode === 'overdueHeavy'" class="text-[12.5px] mb-3" style="color:#B4524A;">{{ t('dashboard.checklist.focus.overdueHeavy') }}</p>
```

- [ ] **Step 5: Add "Lihat semua" footer in focus scope**

Immediately after the closing `</div>` of the timeline/groups loop block (the `<div v-for="group in (view === 'timeline' ? timelineGroups : groups)" ...>` container that starts at line 1138), add a footer button. Locate the end of that `v-for` container and insert after it:

```html
                        <button v-if="focusScope === 'focus'" type="button" @click="setFocusScope('all')"
                                class="w-full mt-2 py-3 rounded-xl text-[13px] font-semibold transition-colors"
                                style="background:#FBFCF9; border:1px solid #D8DFD2; color:#4A5A4C;">
                            {{ t('dashboard.checklist.focus.seeAll', { total: activeTasks.length }) }}
                        </button>
```

- [ ] **Step 6: Build and verify in the app**

Run: `rtk npm run build`
Expected: build completes with no errors (chunk-size warning is fine).

Manual check (desktop): open `/dashboard/checklist` with a couple that has many tasks.
- Defaults to "Fokus Sekarang" with a small set (≤8).
- Momentum line shows "X dari Y kelar · …".
- Toggle to "Semua" shows the full list + chips; reload page → still on "Semua" (persisted).
- Toggle back to "Fokus Sekarang"; "Lihat semua N tugas →" footer switches to "Semua".

- [ ] **Step 7: Commit**

```bash
rtk git add resources/js/Pages/Dashboard/Checklist/Index.vue public/build
rtk git commit -m "feat(checklist): Fokus Sekarang default view on desktop"
```

---

## Task 4: Mobile parity (MobileChecklist.vue)

**Files:**
- Modify: `resources/js/Components/dashboard/checklist/mobile/MobileChecklist.vue`
- Modify: `resources/js/Pages/Dashboard/Checklist/Index.vue` (pass props/events to mobile)

Mobile already receives `:buckets="mobileBuckets"`, and `mobileBuckets` derives from `timelineGroups` → `displayList`. Because Task 3 narrowed `displayList` in focus scope, the mobile list is **already** filtered to the focus set. This task only adds the mobile toggle + momentum line and wires them to the same `focusScope`.

- [ ] **Step 1: Add props + emit to MobileChecklist**

In `resources/js/Components/dashboard/checklist/mobile/MobileChecklist.vue`, add to `defineProps` (after `hasSystemTasks`, line 22):

```js
  focusScope:   { type: String, default: 'focus' },
  focusProgress:{ type: String, default: '' },
  focusStatus:  { type: String, default: '' },
```

Add `setFocusScope` to the emits list (line 24):

```js
const emit = defineEmits(['select', 'openFilter', 'addTask', 'openTask', 'toggle', 'showDone', 'applyTemplate', 'aiGenerate', 'setFocusScope']);
```

- [ ] **Step 2: Render mobile toggle + momentum**

In `MobileChecklist.vue` template, immediately before the chips row (the `<div class="flex items-center gap-2 mb-3.5">` block, around line 69), insert:

```html
    <div class="mb-3.5">
      <div class="inline-flex rounded-xl p-0.5 w-full" style="background:#EFE7D6;">
        <button type="button" @click="emit('setFocusScope', 'focus')"
                class="flex-1 px-3 py-2 rounded-[10px] text-[12px] font-semibold"
                :style="focusScope === 'focus' ? 'background:#1F2A2E; color:#FBFCF9;' : 'color:#6C7A75;'">
          {{ t('dashboard.checklist.focus.tab') }}
        </button>
        <button type="button" @click="emit('setFocusScope', 'all')"
                class="flex-1 px-3 py-2 rounded-[10px] text-[12px] font-semibold"
                :style="focusScope === 'all' ? 'background:#1F2A2E; color:#FBFCF9;' : 'color:#6C7A75;'">
          {{ t('dashboard.checklist.focus.tabAll') }}
        </button>
      </div>
      <p v-if="focusScope === 'focus' && focusProgress" class="mt-2 text-[12px]" style="color:#6C7A75;">
        <span style="color:#1F2A2E; font-weight:600;">{{ focusProgress }}</span> · {{ focusStatus }}
      </p>
    </div>
```

- [ ] **Step 3: Hide mobile chips in focus scope**

In `MobileChecklist.vue`, find the chips scroller row (`<div class="flex gap-1.5 overflow-x-auto flex-1" ...>` near line 70) and add `v-if` to its wrapping `<div class="flex items-center gap-2 mb-3.5">` so chips only show in "all":

Change:
```html
    <div class="flex items-center gap-2 mb-3.5">
```
to:
```html
    <div v-if="focusScope === 'all'" class="flex items-center gap-2 mb-3.5">
```

- [ ] **Step 4: Wire mobile props/events from Index.vue**

In `resources/js/Pages/Dashboard/Checklist/Index.vue`, update the `<MobileChecklist ... />` usage (line ~1471-1475) to pass focus props and handle the event. Add these attributes to the component:

```html
                    :focus-scope="focusScope"
                    :focus-progress="t('dashboard.checklist.focus.progress', { done: summary.done, total: summary.total })"
                    :focus-status="t('dashboard.checklist.focus.' + focusStatus.key, focusStatus.params)"
                    @set-focus-scope="setFocusScope"
```

- [ ] **Step 5: Build and verify (mobile)**

Run: `rtk npm run build`
Expected: build completes, no errors.

Manual check (mobile viewport / `isMobileView`): open checklist on a narrow screen.
- Defaults to "Fokus Sekarang"; small task set; momentum line visible.
- Toggle to "Semua" reveals chips + full list; persists on reload.
- AI banner and other mobile controls still work.

- [ ] **Step 6: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/mobile/MobileChecklist.vue resources/js/Pages/Dashboard/Checklist/Index.vue public/build
rtk git commit -m "feat(checklist): mobile parity for Fokus Sekarang"
```

---

## Task 5: Final verification

- [ ] **Step 1: Run the unit tests**

Run: `rtk npx vitest run resources/js/Pages/Dashboard/Checklist/selectFocusTasks.test.js`
Expected: PASS.

- [ ] **Step 2: Production build**

Run: `rtk npm run build`
Expected: completes, no errors.

- [ ] **Step 3: Cross-check definition of done (from spec)**

- [ ] Checklist opens to Fokus Sekarang by default (≤8 tasks).
- [ ] Toggle to Semua works and persists across sessions.
- [ ] Momentum header shows honest progress + overdue-based status.
- [ ] Edge states handled: relaxed (nothing due soon), overdueHeavy, all-done (existing `allDone`), not initialized (existing setup flow untouched).
- [ ] Mobile mirrors the behaviour.
- [ ] ID + EN strings present.

---

## Self-Review Notes

- **Spec coverage:** 3-zoom model → Task 3/4 (focus default + toggle + see-all). Focus window rule → Task 1. Momentum reframe → Task 3 (`focusStatus`). Edge states → Task 1 modes + existing `allDone`. Mobile parity → Task 4. i18n → Task 2. All spec sections covered.
- **No fabricated schedule:** `focusStatus` uses only `summary.overdue` — honest, matches spec anti-halu rule.
- **Type consistency:** `selectFocusTasks` returns `{ tasks, mode }` everywhere; `focusScope` is `'focus' | 'all'` in Index and MobileChecklist; `setFocusScope` name consistent across files.
- **Reuse:** focus is a `displayList` filter, so existing timeline/group/task-row rendering and `mobileBuckets` are reused — no duplicate card markup.
