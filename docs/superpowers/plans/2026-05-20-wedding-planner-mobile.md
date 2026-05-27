# Wedding Planner Redesign — Plan 2: Mobile

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the mobile (`<lg`) Wedding Planner experience matching `theday(4)/mobchecklist.jsx` — a compact progress hero, AI-hint banner (dummy), horizontal filter chips, H-bucket task cards, a collapsed "Selesai" row, a FAB, a **filter bottom-sheet**, and a **task-detail bottom-sheet** — all bound to the same `Index.vue` state built in Plan 1.

**Architecture:** A `<lg` branch in `Dashboard/Checklist/Index.vue` renders `MobileChecklist` instead of the desktop composition (toggle via `useMediaQuery('(max-width: 1023px)')`). Two Teleported bottom-sheets (`MobileFilterSheet`, `MobileTaskSheet`) are controlled by new Index refs. Everything reuses Plan 1's computeds/handlers; no backend changes.

**Tech Stack:** Vue 3 `<script setup>` + Tailwind v3. Reuses `WidgetIcon`, `DemoBadge`, the existing subtask/toggle/form handlers, and Plan 1's checklist computeds.

**Prerequisite:** Plan 1 (`2026-05-20-wedding-planner-desktop.md`) must be implemented first — Plan 2 reuses its refs/computeds: `view`, `activeChip`, `daysUntil`, `urgentCount`, `doneThisMonth`, `picSplit`, `filterChips`, `onChip`, `displayList`, `timelineGroups`, `reminders`, `exportCalendar`, plus existing `summary`, `tasks`, `filterStatus/Cat/Priority/Assignee`, `sortBy`, `showForm`, `editingTask`, `toggle`, subtask handlers (`getSubtaskState`, `loadSubtasks`, `addSubtask`, `toggleSubtask`, `deleteSubtask`), `categoryLabel`, `priorityConfig`, `categories`.

**Reference:** spec `docs/superpowers/specs/2026-05-20-wedding-planner-redesign-design.md`; mockup `theday(4)/mobchecklist.jsx`.

---

## Conventions (every task)

- Run from `c:\laragon\www\theday2`; prefix git/build with `rtk`.
- Build check: `rtk npm run build` → `✓ built in …`, no errors.
- New strings via `t('dashboard.checklist.mobile.…')`; keys added in Task 6 (raw key shows until then — acceptable).
- Reuse, do NOT recreate `WidgetIcon` / `DemoBadge`.

## Data contracts (props into mobile components)

```
MobileTaskCard:
  task:Object                                  // emits: tap(task), toggle(task)
MobileChecklist:
  progress, done, total, remaining, urgentCount, upcoming7d, daysUntil:Number|null, hasEventDate:Boolean,
  chips:[{key,label,count}], activeChip:String,
  buckets:[{ cat, label, stamp, tasks:[task] }],   // non-done timeline groups
  doneCount:Number
  // emits: select(chipKey), openFilter(), addTask(), openTask(task), toggle(task), showDone()
MobileFilterSheet:
  open:Boolean, sortBy:String, filterStatus:String, filterCat:String, filterPriority:String,
  filterAssignee:String, categories:[{value,label}], resultCount:Number
  // emits: close(), update:sortBy, update:filterStatus, update:filterCat, update:filterAssignee, reset()
MobileTaskSheet:
  task:Object|null, subtaskState:Object   // { items, loading, newTitle, ... } from getSubtaskState(task.id)
  // emits: close(), toggleDone(task), edit(task), addSubtask(), toggleSubtask(subtask), deleteSubtask(subtask)
```

---

## Task 1: `MobileTaskCard.vue`

**Files:**
- Create: `resources/js/Components/dashboard/checklist/mobile/MobileTaskCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ task: { type: Object, required: true } });
const emit = defineEmits(['tap', 'toggle']);
const { t } = useLocale();

const done = computed(() => props.task.status === 'done');
const urgent = computed(() => {
  const tk = props.task;
  if (tk.status !== 'todo' || !tk.due_date) return false;
  const now = new Date(); now.setHours(0,0,0,0);
  const diff = Math.round((new Date(tk.due_date + 'T00:00:00') - now) / 86400000);
  return diff <= 1 || (tk.priority === 'high' && diff <= 7);
});
const who = computed(() => props.task.assignee_type === 'groom' ? 'R' : (props.task.assignee_type === 'bride' ? 'A' : null));
const whoColor = computed(() => props.task.assignee_type === 'groom' ? '#D9B5B0' : '#C7D3BC');
</script>

<template>
  <div class="rounded-[14px] p-3 mb-2 grid grid-cols-[auto_1fr] gap-3"
       :style="`background:#FBFCF9; border:1px solid #D8DFD2; ${urgent ? 'border-left:3px solid #C19089;' : ''}`"
       @click="emit('tap', task)">
    <button type="button" @click.stop="emit('toggle', task)"
            class="w-[22px] h-[22px] rounded-[7px] grid place-items-center mt-0.5 text-[11px] font-bold text-white flex-shrink-0"
            :style="done ? 'background:#92A89C; border:2px solid #92A89C;' : 'border:2px solid #C7D0BE; background:transparent;'">
      {{ done ? '✓' : '' }}
    </button>
    <div class="min-w-0">
      <div class="text-[13.5px] font-medium leading-snug" :style="done ? 'color:#6C7A75; text-decoration:line-through;' : 'color:#1F2A2E;'">{{ task.title }}</div>
      <div class="flex items-center gap-2 mt-2 flex-wrap">
        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full" style="background:rgba(156,171,142,0.18); color:#4A5A4C;">{{ task.category }}</span>
        <span v-if="task.vendor" class="text-[11px] inline-flex items-center gap-1" style="color:#6F8270;">
          <WidgetIcon name="vendor" :size="11" stroke="#6F8270" /> {{ task.vendor }}
        </span>
        <span v-if="task.due_date" class="font-jet text-[10.5px] px-1.5 py-0.5 rounded"
              :style="urgent ? 'color:#C19089; background:rgba(217,181,176,0.18); border:1px solid #D9B5B0;' : 'color:#6C7A75; background:#F6F8F3; border:1px solid #D8DFD2;'">{{ task.due_date }}</span>
        <div v-if="who" class="ml-auto w-[22px] h-[22px] rounded-full grid place-items-center text-[10px] font-bold font-cormorant" :style="{ background: whoColor, color:'#1F2A2E' }">{{ who }}</div>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/mobile/MobileTaskCard.vue
rtk git commit -m "feat(checklist): add MobileTaskCard"
```

---

## Task 2: `MobileFilterSheet.vue`

A bottom sheet with drag handle; live-binds to the existing filter/sort refs via `update:*` events; "Terapkan · N" closes, "Reset" clears.

**Files:**
- Create: `resources/js/Components/dashboard/checklist/mobile/MobileFilterSheet.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  open:           { type: Boolean, default: false },
  sortBy:         { type: String, default: '' },
  filterStatus:   { type: String, default: '' },
  filterCat:      { type: String, default: '' },
  filterAssignee: { type: String, default: '' },
  categories:     { type: Array, default: () => [] },
  resultCount:    { type: Number, default: 0 },
});
const emit = defineEmits(['close', 'update:sortBy', 'update:filterStatus', 'update:filterCat', 'update:filterAssignee', 'reset']);
const { t } = useLocale();

const sorts = [
  { k: 'due_date', l: () => t('dashboard.checklist.mobile.sort.due') },
  { k: 'priority', l: () => t('dashboard.checklist.mobile.sort.priority') },
  { k: '',         l: () => t('dashboard.checklist.mobile.sort.newest') },
];
const statuses = [
  { k: '',     l: () => t('dashboard.checklist.chip.all') },
  { k: 'todo', l: () => t('dashboard.checklist.chip.todo') },
  { k: 'done', l: () => t('dashboard.checklist.chip.done') },
];
const pics = [
  { k: 'bride', l: () => t('dashboard.checklist.assignee.bride') },
  { k: 'groom', l: () => t('dashboard.checklist.assignee.groom') },
  { k: 'both',  l: () => t('dashboard.checklist.assignee.both') },
];
const pill = (active) => active
  ? 'background:#1F2A2E; color:#FBFCF9; border:1px solid #1F2A2E;'
  : 'background:#F6F8F3; color:#3D4A4D; border:1px solid #D8DFD2;';
</script>

<template>
  <Teleport to="body">
    <Transition name="sheet">
      <div v-if="open" class="fixed inset-0 z-[60] flex flex-col justify-end" @click.self="emit('close')"
           style="background: rgba(31,42,46,0.4); backdrop-filter: blur(2px);">
        <div class="rounded-t-[24px] pb-7 pt-3 max-h-[82%] flex flex-col" style="background:#FBFCF9;">
          <div class="w-9 h-1 rounded-full mx-auto mb-4" style="background:#C7D0BE;" />
          <div class="flex items-center justify-between px-6 pb-4">
            <div class="font-cormorant font-semibold text-[22px]" style="color:#1F2A2E;">{{ t('dashboard.checklist.mobile.filter') }}</div>
            <button type="button" @click="emit('close')" class="w-8 h-8 rounded-full grid place-items-center" style="background:#F6F8F3; border:1px solid #D8DFD2;">
              <WidgetIcon name="plus" :size="16" stroke="#3D4A4D" class="rotate-45" />
            </button>
          </div>

          <div class="px-6 overflow-y-auto">
            <p class="text-[11px] tracking-[0.14em] uppercase font-bold mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.sortLabel') }}</p>
            <div class="flex gap-1.5 flex-wrap mb-5">
              <button v-for="s in sorts" :key="s.k" type="button" @click="emit('update:sortBy', s.k)"
                      class="px-3.5 py-2 rounded-full text-[12.5px] font-semibold" :style="pill(sortBy === s.k)">{{ s.l() }}</button>
            </div>

            <p class="text-[11px] tracking-[0.14em] uppercase font-bold mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.statusLabel') }}</p>
            <div class="flex gap-1.5 flex-wrap mb-5">
              <button v-for="s in statuses" :key="s.k" type="button" @click="emit('update:filterStatus', s.k)"
                      class="px-3.5 py-2 rounded-full text-[12.5px] font-semibold" :style="pill(filterStatus === s.k)">{{ s.l() }}</button>
            </div>

            <p class="text-[11px] tracking-[0.14em] uppercase font-bold mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.categoryLabel') }}</p>
            <div class="flex gap-1.5 flex-wrap mb-5">
              <button v-for="c in categories" :key="c.value" type="button"
                      @click="emit('update:filterCat', filterCat === c.value ? '' : c.value)"
                      class="px-3.5 py-2 rounded-full text-[12.5px] font-semibold" :style="pill(filterCat === c.value)">{{ c.label }}</button>
            </div>

            <p class="text-[11px] tracking-[0.14em] uppercase font-bold mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.picLabel') }}</p>
            <div class="flex gap-2 mb-2">
              <button v-for="p in pics" :key="p.k" type="button"
                      @click="emit('update:filterAssignee', filterAssignee === p.k ? '' : p.k)"
                      class="flex-1 py-2.5 rounded-[12px] text-[12.5px] font-semibold" :style="pill(filterAssignee === p.k)">{{ p.l() }}</button>
            </div>
          </div>

          <div class="px-6 pt-3 mt-2 flex gap-2.5" style="border-top:1px solid #D8DFD2;">
            <button type="button" @click="emit('reset')" class="flex-1 py-3 rounded-full text-[13px] font-semibold" style="background:transparent; border:1px solid #C7D0BE; color:#3D4A4D;">{{ t('dashboard.checklist.mobile.reset') }}</button>
            <button type="button" @click="emit('close')" class="flex-[2] py-3 rounded-full text-[13px] font-semibold text-white" style="background:#1F2A2E;">{{ t('dashboard.checklist.mobile.apply', { count: resultCount }) }}</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.sheet-enter-active, .sheet-leave-active { transition: opacity .2s; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
</style>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/mobile/MobileFilterSheet.vue
rtk git commit -m "feat(checklist): add MobileFilterSheet bottom sheet"
```

---

## Task 3: `MobileTaskSheet.vue`

Task-detail bottom sheet. Shows real fields only (badges, title, vendor name, due, priority, PIC, sub-steps, note). Reuses subtask state passed from Index.

**Files:**
- Create: `resources/js/Components/dashboard/checklist/mobile/MobileTaskSheet.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  task:         { type: Object, default: null },
  subtaskState: { type: Object, default: () => ({ items: [], loading: false, newTitle: '' }) },
});
const emit = defineEmits(['close', 'toggleDone', 'edit', 'addSubtask', 'toggleSubtask', 'deleteSubtask']);
const { t } = useLocale();

const done = computed(() => props.task?.status === 'done');
const who  = computed(() => props.task?.assignee_type === 'groom' ? 'R' : (props.task?.assignee_type === 'bride' ? 'A' : null));
const prioLabel = computed(() => ({ high: t('dashboard.checklist.priority.high'), medium: t('dashboard.checklist.priority.medium'), low: t('dashboard.checklist.priority.low') }[props.task?.priority] ?? '—'));
</script>

<template>
  <Teleport to="body">
    <Transition name="sheet">
      <div v-if="task" class="fixed inset-0 z-[60] flex flex-col justify-end" @click.self="emit('close')"
           style="background: rgba(31,42,46,0.4); backdrop-filter: blur(2px);">
        <div class="rounded-t-[24px] max-h-[88%] flex flex-col" style="background:#FBFCF9;">
          <div class="w-9 h-1 rounded-full mx-auto mt-3" style="background:#C7D0BE;" />

          <!-- header -->
          <div class="px-6 pt-4 pb-3.5" style="border-bottom:1px solid #D8DFD2;">
            <div class="flex justify-between items-start gap-3">
              <div class="flex-1 min-w-0">
                <div class="flex gap-1.5 mb-2 flex-wrap">
                  <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background:rgba(156,171,142,0.18); color:#4A5A4C;">{{ task.category }}</span>
                  <span v-if="task.due_date" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background:rgba(217,162,74,0.18); color:#8E6515;">{{ task.due_date }}</span>
                </div>
                <div class="font-cormorant font-medium text-[24px] leading-[1.15]" style="color:#1F2A2E;">{{ task.title }}</div>
              </div>
              <button type="button" @click="emit('close')" class="w-8 h-8 rounded-full grid place-items-center flex-shrink-0" style="background:#F6F8F3; border:1px solid #D8DFD2;">
                <WidgetIcon name="plus" :size="16" stroke="#3D4A4D" class="rotate-45" />
              </button>
            </div>
            <div v-if="task.vendor" class="flex items-center gap-2.5 mt-3.5 px-3 py-2 rounded-[10px]" style="background:#F4EDDC;">
              <div class="w-7 h-7 rounded-lg grid place-items-center" style="background:#fff; color:#8E6515;"><WidgetIcon name="vendor" :size="15" stroke="#8E6515" /></div>
              <div class="text-[12.5px] font-semibold" style="color:#1F2A2E;">{{ task.vendor }}</div>
            </div>
          </div>

          <!-- body -->
          <div class="px-6 py-4 overflow-y-auto flex-1">
            <div class="grid grid-cols-2 gap-2.5 mb-4">
              <div class="rounded-[10px] p-2.5" style="background:#F6F8F3; border:1px solid #D8DFD2;">
                <div class="text-[10.5px] uppercase font-semibold tracking-wide" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.due') }}</div>
                <div class="text-[13px] mt-1 font-medium" style="color:#1F2A2E;">{{ task.due_date || '—' }}</div>
              </div>
              <div class="rounded-[10px] p-2.5" style="background:#F6F8F3; border:1px solid #D8DFD2;">
                <div class="text-[10.5px] uppercase font-semibold tracking-wide" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.priority') }}</div>
                <div class="text-[13px] mt-1 font-medium" style="color:#1F2A2E;">{{ prioLabel }}</div>
              </div>
            </div>

            <div v-if="who" class="mb-4">
              <div class="text-[11px] uppercase font-bold tracking-wide mb-2" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.pic') }}</div>
              <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px]" style="background:#F6F8F3; border:1px solid #D8DFD2;">
                <div class="w-8 h-8 rounded-full grid place-items-center text-[13px] font-bold font-cormorant" :style="`background:${who === 'R' ? '#D9B5B0' : '#C7D3BC'}; color:#1F2A2E;`">{{ who }}</div>
                <div class="text-[13px] font-medium" style="color:#1F2A2E;">{{ who === 'R' ? t('dashboard.checklist.assignee.groom') : t('dashboard.checklist.assignee.bride') }}</div>
                <button type="button" @click="emit('edit', task)" class="ml-auto px-3 py-1.5 rounded-full text-[11.5px] font-semibold" style="border:1px solid #C7D0BE; color:#3D4A4D;">{{ t('dashboard.checklist.mobile.detail.change') }}</button>
              </div>
            </div>

            <!-- sub-steps -->
            <div class="mb-4">
              <div class="text-[11px] uppercase font-bold tracking-wide mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.substeps') }}</div>
              <div v-for="(s, i) in subtaskState.items" :key="s.id" class="flex items-center gap-2.5 py-2" :style="i ? 'border-top:1px solid #D8DFD2;' : ''">
                <button type="button" @click="emit('toggleSubtask', s)"
                        class="w-[18px] h-[18px] rounded-[5px] grid place-items-center text-white text-[10px] font-bold"
                        :style="s.is_completed ? 'background:#92A89C; border:2px solid #92A89C;' : 'border:2px solid #C7D0BE;'">{{ s.is_completed ? '✓' : '' }}</button>
                <span class="flex-1 text-[13px]" :style="s.is_completed ? 'color:#6C7A75; text-decoration:line-through;' : 'color:#1F2A2E;'">{{ s.title }}</span>
                <button type="button" @click="emit('deleteSubtask', s)" class="text-[#C19089] text-[11px]">✕</button>
              </div>
              <div class="flex items-center gap-2 mt-2">
                <input v-model="subtaskState.newTitle" type="text" :placeholder="t('dashboard.checklist.mobile.detail.addStep')"
                       class="flex-1 rounded-[10px] px-3 py-2 text-[13px]" style="background:#F6F8F3; border:1px solid #D8DFD2; outline:none;"
                       @keyup.enter="emit('addSubtask')" />
                <button type="button" @click="emit('addSubtask')" class="px-3 py-2 rounded-[10px] text-[12px] font-semibold text-white" style="background:#92A89C;">+</button>
              </div>
            </div>

            <!-- note -->
            <div v-if="task.description">
              <div class="text-[11px] uppercase font-bold tracking-wide mb-2" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.note') }}</div>
              <div class="rounded-[10px] px-3 py-2.5 font-cormorant text-[14px] italic" style="background:#F6F8F3; border:1px solid #D8DFD2; color:#3D4A4D;">{{ task.description }}</div>
            </div>
          </div>

          <!-- CTA -->
          <div class="px-6 py-3 pb-7 flex gap-2.5" style="border-top:1px solid #D8DFD2;">
            <button type="button" @click="emit('edit', task)" class="w-11 h-11 rounded-[12px] grid place-items-center flex-shrink-0" style="background:#F6F8F3; border:1px solid #D8DFD2;">
              <WidgetIcon name="settings" :size="18" stroke="#3D4A4D" />
            </button>
            <button type="button" @click="emit('toggleDone', task)"
                    class="flex-1 py-3 rounded-full text-[13px] font-bold text-white inline-flex items-center justify-center gap-2"
                    :style="done ? 'background:#6C7A75;' : 'background:#92A89C;'">
              <WidgetIcon name="check" :size="16" stroke="#fff" /> {{ done ? t('dashboard.checklist.mobile.detail.markUndone') : t('dashboard.checklist.mobile.detail.markDone') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.sheet-enter-active, .sheet-leave-active { transition: opacity .2s; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
</style>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/mobile/MobileTaskSheet.vue
rtk git commit -m "feat(checklist): add MobileTaskSheet detail bottom sheet"
```

---

## Task 4: `MobileChecklist.vue`

Mobile main view: compact hero + AI-hint banner (DemoBadge) + horizontal chips + bucket cards + collapsed "Selesai" + FAB.

**Files:**
- Create: `resources/js/Components/dashboard/checklist/mobile/MobileChecklist.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import MobileTaskCard from '@/Components/dashboard/checklist/mobile/MobileTaskCard.vue';
import { useLocale } from '@/Composables/useLocale';

defineProps({
  progress:     { type: Number, default: 0 },
  done:         { type: Number, default: 0 },
  total:        { type: Number, default: 0 },
  urgentCount:  { type: Number, default: 0 },
  upcoming7d:   { type: Number, default: 0 },
  daysUntil:    { type: Number, default: null },
  hasEventDate: { type: Boolean, default: false },
  chips:        { type: Array, default: () => [] },
  activeChip:   { type: String, default: 'all' },
  buckets:      { type: Array, default: () => [] }, // [{cat,label,stamp,tasks}]
  doneCount:    { type: Number, default: 0 },
});
const emit = defineEmits(['select', 'openFilter', 'addTask', 'openTask', 'toggle', 'showDone']);
const { t } = useLocale();

const stampColor = (cat) => ({ overdue: '#C19089', today: '#C19089', week: '#D9A24A' }[cat] || '#92A89C');
</script>

<template>
  <div class="relative pb-24">
    <!-- progress hero (compact) -->
    <div class="rounded-[18px] p-[18px] mb-3 relative overflow-hidden" style="background:linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); color:#FBFCF9;">
      <span aria-hidden="true" class="absolute -top-12 -right-8 w-40 h-40 rounded-full" style="background:radial-gradient(circle, rgba(156,171,142,0.35), transparent 70%);" />
      <div class="relative">
        <div class="text-[10px] tracking-[0.2em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.checklist.hero.overall') }}</div>
        <div class="flex items-baseline gap-3 mt-1.5">
          <div class="font-cormorant font-medium text-[44px] leading-none">{{ progress }}%</div>
          <div class="text-[12px]" style="color:rgba(251,252,249,0.7);">{{ t('dashboard.checklist.hero.doneOfTotal', { done, total }) }}</div>
        </div>
        <div class="mt-3 h-1.5 rounded-full overflow-hidden" style="background:rgba(251,252,249,0.12);">
          <div class="h-full rounded-full" :style="{ width: progress + '%', background: 'linear-gradient(90deg, #92A89C, #C7D3BC)' }" />
        </div>
        <div class="flex justify-between mt-3 text-[11px]" style="color:rgba(251,252,249,0.7);">
          <span>⚠ <strong class="text-white">{{ urgentCount }}</strong> {{ t('dashboard.checklist.hero.urgent') }}</span>
          <span>⏱ <strong class="text-white">{{ upcoming7d }}</strong> {{ t('dashboard.checklist.stat.due7d') }}</span>
          <span v-if="hasEventDate && daysUntil !== null">D-<strong class="text-white">{{ daysUntil }}</strong></span>
        </div>
      </div>
    </div>

    <!-- AI hint (dummy) -->
    <div class="rounded-[12px] px-3.5 py-2.5 mb-3.5 flex items-center gap-2.5" style="background:#F4EDDC; border:1px solid #E0D2BD;">
      <div class="w-7 h-7 rounded-lg grid place-items-center flex-shrink-0" style="background:#fff; color:#8E6515;"><WidgetIcon name="sparkle" :size="15" stroke="#8E6515" /></div>
      <div class="flex-1 text-[12px] leading-snug" style="color:#5A4B1A;"><strong>{{ t('dashboard.checklist.hero.aiSuggest') }}</strong> · {{ t('dashboard.checklist.mobile.aiHint') }}</div>
      <DemoBadge />
    </div>

    <!-- filter chips (horizontal scroll) + filter button -->
    <div class="flex items-center gap-2 mb-3.5">
      <div class="flex gap-1.5 overflow-x-auto flex-1" style="-webkit-overflow-scrolling:touch;">
        <button v-for="c in chips" :key="c.key" type="button" @click="emit('select', c.key)"
                class="flex-shrink-0 px-3 py-1.5 rounded-full text-[12px] font-semibold inline-flex items-center gap-1.5"
                :style="activeChip === c.key ? 'background:#1F2A2E; color:#FBFCF9; border:1px solid #1F2A2E;' : 'background:#FBFCF9; color:#3D4A4D; border:1px solid #D8DFD2;'">
          {{ c.label }} <span class="font-jet text-[10px] px-1 rounded-full" :style="activeChip === c.key ? 'background:rgba(255,255,255,0.15);' : 'background:#DCE4D3; color:#4A5A4C;'">{{ c.count }}</span>
        </button>
      </div>
      <button type="button" @click="emit('openFilter')" class="w-9 h-9 rounded-full grid place-items-center flex-shrink-0" style="background:#FBFCF9; border:1px solid #D8DFD2;">
        <WidgetIcon name="filter" :size="16" stroke="#3D4A4D" />
      </button>
    </div>

    <!-- buckets -->
    <div v-for="g in buckets" :key="g.cat" class="mb-1">
      <div class="flex items-center gap-2.5 py-2">
        <span class="font-jet text-[10px] font-bold tracking-wide px-2 py-0.5 rounded-full text-white" :style="{ background: stampColor(g.cat) }">{{ g.stamp }}</span>
        <span class="font-cormorant font-semibold text-[17px]" style="color:#1F2A2E;">{{ g.label }}</span>
        <span class="ml-auto text-[10.5px]" style="color:#6C7A75;">{{ g.tasks.length }} {{ t('dashboard.checklist.mobile.tasks') }}</span>
      </div>
      <MobileTaskCard v-for="tk in g.tasks" :key="tk.id" :task="tk" @tap="emit('openTask', $event)" @toggle="emit('toggle', $event)" />
    </div>

    <!-- collapsed done -->
    <button v-if="doneCount" type="button" @click="emit('showDone')" class="w-full flex items-center gap-2.5 py-3 mt-1" style="border-top:1px solid #D8DFD2;">
      <span class="font-jet text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#DCE4D3; color:#4A5A4C;">✓ {{ doneCount }}</span>
      <span class="text-[13px]" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.doneTasks') }}</span>
      <span class="ml-auto text-[11px] font-semibold" style="color:#6F8270;">{{ t('dashboard.checklist.mobile.see') }} ›</span>
    </button>

    <!-- FAB -->
    <button type="button" @click="emit('addTask')"
            class="fixed bottom-24 right-5 z-20 inline-flex items-center gap-2 px-4 py-3 rounded-full text-[13px] font-semibold text-white"
            style="background:#1F2A2E; box-shadow: 0 16px 32px -10px rgba(31,42,46,0.5);">
      <WidgetIcon name="plus" :size="16" stroke="#fff" /> {{ t('dashboard.checklist.mobile.addTask') }}
    </button>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/mobile/MobileChecklist.vue
rtk git commit -m "feat(checklist): add MobileChecklist main view"
```

---

## Task 5: Integrate mobile into `Index.vue`

Render `MobileChecklist` + the two sheets below `lg`; reuse Plan 1 computeds. Keep the desktop composition for `lg+`.

**Files:**
- Modify: `resources/js/Pages/Dashboard/Checklist/Index.vue`

- [ ] **Step 1: Imports + media query + sheet state**

Add imports:
```js
import MobileChecklist  from '@/Components/dashboard/checklist/mobile/MobileChecklist.vue';
import MobileFilterSheet from '@/Components/dashboard/checklist/mobile/MobileFilterSheet.vue';
import MobileTaskSheet   from '@/Components/dashboard/checklist/mobile/MobileTaskSheet.vue';
import { useMediaQuery } from '@/Composables/useMediaQuery';
```
Add state:
```js
const isMobileView   = useMediaQuery('(max-width: 1023px)');
const showMobileFilter = ref(false);
const mobileTask     = ref(null);              // selected task for detail sheet
const mobileSubtasks = computed(() => mobileTask.value ? getSubtaskState(mobileTask.value.id) : { items: [], newTitle: '' });

function openMobileTask(tk) { mobileTask.value = tk; loadSubtasks(tk.id); }
function closeMobileTask()  { mobileTask.value = null; }
function mobileEdit(tk)     { mobileTask.value = null; editingTask.value = tk; showForm.value = true; }
function resetMobileFilters() { filterStatus.value=''; filterCat.value=''; filterPriority.value=''; filterAssignee.value=''; sortBy.value=''; activeChip.value='all'; }

// buckets for mobile = timelineGroups minus 'done'; doneCount from summary
const mobileBuckets = computed(() => timelineGroups.value.filter(g => g.cat !== 'done'));
const mobileDoneCount = computed(() => summary.value.done);
const resultCount = computed(() => displayList.value.length);
```

(For the detail-sheet subtask add: reuse existing `addSubtask(task)` which reads `getSubtaskState(task.id).newTitle` — so the sheet's `addSubtask` event calls `addSubtask(mobileTask.value)`. Same for toggle/delete with `(mobileTask.value, subtask)`.)

- [ ] **Step 2: Template — branch desktop vs mobile**

Wrap the Plan 1 desktop composition root (`<div class="max-w-[1200px] mx-auto">`) in `v-if="!isMobileView"`. Immediately after it, add the mobile branch:

```vue
<template v-if="isMobileView">
  <MobileChecklist
    :progress="summary.progress" :done="summary.done" :total="summary.total"
    :urgent-count="urgentCount" :upcoming7d="summary.upcoming_7d" :days-until="daysUntil" :has-event-date="summary.has_event_date"
    :chips="filterChips" :active-chip="activeChip" :buckets="mobileBuckets" :done-count="mobileDoneCount"
    @select="onChip" @open-filter="showMobileFilter = true" @add-task="showForm = true"
    @open-task="openMobileTask" @toggle="toggle" @show-done="onChip('done')" />
</template>

<MobileFilterSheet
  :open="showMobileFilter" :sort-by="sortBy" :filter-status="filterStatus" :filter-cat="filterCat"
  :filter-assignee="filterAssignee" :categories="categories" :result-count="resultCount"
  @close="showMobileFilter = false" @reset="resetMobileFilters"
  @update:sort-by="sortBy = $event" @update:filter-status="filterStatus = $event"
  @update:filter-cat="filterCat = $event" @update:filter-assignee="filterAssignee = $event" />

<MobileTaskSheet
  :task="mobileTask" :subtask-state="mobileSubtasks"
  @close="closeMobileTask" @toggle-done="(tk) => { toggle(tk); }" @edit="mobileEdit"
  @add-subtask="addSubtask(mobileTask)" @toggle-subtask="(s) => toggleSubtask(mobileTask, s)"
  @delete-subtask="(s) => deleteSubtask(mobileTask, s)" />
```

Notes:
- The existing mobile FAB in the old template (`class="fixed bottom-20 right-6 lg:hidden …"`) is now redundant with `MobileChecklist`'s FAB — remove the old one to avoid a double FAB (delete the `<!-- FAB mobile -->` block).
- The task-form modal stays (shared by FAB + edit on both desktop & mobile).
- `toggle(tk)` is the existing per-task toggle handler (the function the checkbox already calls). If its name differs, use the existing handler name.

- [ ] **Step 3: Build check**

Run: `rtk npm run build`
Expected: success.

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Pages/Dashboard/Checklist/Index.vue
rtk git commit -m "feat(checklist): wire mobile layout + filter/detail sheets into Index"
```

---

## Task 6: i18n keys (mobile, id + en)

**Files:**
- Modify: `lang/id.json`, `lang/en.json` (extend `dashboard.checklist.mobile`)

- [ ] **Step 1: Add under `dashboard.checklist.mobile` (id)**

```json
"mobile": {
  "filter": "Filter", "sortLabel": "Urutkan", "statusLabel": "Status", "categoryLabel": "Kategori", "picLabel": "Penanggung Jawab",
  "reset": "Reset", "apply": "Terapkan · {count} tugas",
  "sort": { "due": "Jatuh tempo terdekat", "priority": "Mendesak dulu", "newest": "Terbaru" },
  "aiHint": "4 tugas baru berdasarkan progress kamu",
  "tasks": "tugas", "doneTasks": "Tugas selesai", "see": "Lihat", "addTask": "Tambah tugas",
  "detail": { "due": "Jatuh tempo", "priority": "Prioritas", "pic": "Penanggung Jawab", "change": "Ganti", "substeps": "Sub-langkah", "addStep": "Tambah sub-langkah", "note": "Catatan", "markDone": "Tandai Selesai", "markUndone": "Batalkan Selesai" }
}
```

- [ ] **Step 2: Add under `dashboard.checklist.mobile` (en)**

```json
"mobile": {
  "filter": "Filter", "sortLabel": "Sort by", "statusLabel": "Status", "categoryLabel": "Category", "picLabel": "Assignee",
  "reset": "Reset", "apply": "Apply · {count} tasks",
  "sort": { "due": "Due soonest", "priority": "Urgent first", "newest": "Newest" },
  "aiHint": "4 new tasks based on your progress",
  "tasks": "tasks", "doneTasks": "Completed tasks", "see": "View", "addTask": "Add task",
  "detail": { "due": "Due date", "priority": "Priority", "pic": "Assignee", "change": "Change", "substeps": "Sub-steps", "addStep": "Add sub-step", "note": "Note", "markDone": "Mark Done", "markUndone": "Mark Undone" }
}
```

Validate both files parse (`node -e "JSON.parse(require('fs').readFileSync('lang/id.json','utf8'))"`, en).

- [ ] **Step 3: Build + commit**

```bash
rtk npm run build
rtk git add lang/id.json lang/en.json
rtk git commit -m "feat(checklist): add mobile i18n keys (id + en)"
```

---

## Task 7: Verification (mobile)

**Files:** none.

- [ ] **Step 1: Full build** — `rtk npm run build` → success.
- [ ] **Step 2: Backend tests** — `php artisan test --filter=Checklist` → pass (unchanged from Plan 1).
- [ ] **Step 3: Manual visual (mobile, viewport ≤ 1023px)** vs `theday(4)/Checklist Mobile.html`:
  - Main view: compact hero, AI-hint banner with Contoh badge, horizontal-scroll chips + filter button, bucket cards with colored stamps, collapsed "Selesai" row, FAB. Bottom nav (from layout) present; only ONE FAB.
  - Tap a card → detail sheet: badges, title, vendor (name only), due+priority tiles, PIC + Ganti, sub-steps (load/add/toggle/delete work), note, Tandai Selesai toggles + closes/refreshes.
  - Tap filter button → filter sheet: sort/status/category/PIC change the visible list; "Terapkan · N" shows the live count and closes; Reset clears.
  - FAB opens the create form; created task appears.
- [ ] **Step 4: Desktop regression** — at `lg+`, the desktop layout (Plan 1) still renders; sheets/mobile view hidden.
- [ ] **Step 5: Commit any fixes** —
```bash
rtk git add -A && rtk git commit -m "fix(checklist): mobile verification adjustments"
```

---

## Self-review notes (for the implementer)

- **Spec coverage (mobile portion):** main list w/ compact hero + AI-hint dummy (T4), horizontal chips (T4), bucket cards (T1,T4), collapsed done (T4), FAB (T4), filter bottom-sheet wired to existing refs (T2,T5), task-detail bottom-sheet with real fields + subtasks + note + mark-done/edit (T3,T5), reuse of existing `MobileBottomNav` (no rebuild), omitted fields (location/estimate/vendor-contact) not shown. ✅
- **Reuses Plan 1:** `urgentCount`, `daysUntil`, `picSplit`, `filterChips`, `onChip`, `displayList`, `timelineGroups`, `activeChip`. If Plan 1 isn't merged, these won't exist — Plan 1 is a hard prerequisite.
- **Category filter is single-select** on mobile (mirrors desktop chips + existing single `filterCat`), not the mockup's multi-select — a deliberate simplification noted in the spec's scope.
- **Removed** the old redundant mobile FAB from the pre-redesign template (Task 5 Step 2) to avoid a double FAB.
- **Subtask add** reuses `addSubtask(task)` which reads `getSubtaskState(task.id).newTitle`; the sheet binds `subtaskState.newTitle` (same reactive object) so the existing function works unchanged.
