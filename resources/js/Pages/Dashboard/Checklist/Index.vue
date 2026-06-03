<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import { useLocale } from '@/Composables/useLocale';
import ChecklistProgressHero from '@/Components/dashboard/checklist/ChecklistProgressHero.vue';
import PlannerPanel from '@/Components/dashboard/checklist/PlannerPanel.vue';
import ChecklistStatStrip from '@/Components/dashboard/checklist/ChecklistStatStrip.vue';
import ChecklistFilterChips from '@/Components/dashboard/checklist/ChecklistFilterChips.vue';
import ChecklistViewToggle from '@/Components/dashboard/checklist/ChecklistViewToggle.vue';
import TaskKanban from '@/Components/dashboard/checklist/TaskKanban.vue';
import ReminderRail from '@/Components/dashboard/checklist/rail/ReminderRail.vue';
import TemplatePresetsRail from '@/Components/dashboard/checklist/rail/TemplatePresetsRail.vue';
import AiChecklistModal from '@/Components/dashboard/checklist/AiChecklistModal.vue';
import PicSplitRail from '@/Components/dashboard/checklist/rail/PicSplitRail.vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import MobileChecklist  from '@/Components/dashboard/checklist/mobile/MobileChecklist.vue';
import MobileFilterSheet from '@/Components/dashboard/checklist/mobile/MobileFilterSheet.vue';
import MobileTaskSheet   from '@/Components/dashboard/checklist/mobile/MobileTaskSheet.vue';
import { useMediaQuery } from '@/Composables/useMediaQuery';
import { checklistCache } from '@/Composables/checklistCache';

const { t, locale } = useLocale();

const props = defineProps({
    weddingPlan: Object,
    plannerPanel: { type: Object, default: () => ({ facts: {}, enabled: true, insights: [], fresh: true }) },
});

const SUMMARY_DEFAULT = { total: 0, todo: 0, done: 0, archived: 0, progress: 0, overdue: 0, upcoming_7d: 0, has_event_date: false };

// ── State ──────────────────────────────────────────────────────────────────
// Hydrate from the SPA-lived cache so returning to the Planner is instant —
// no spinner on every revisit; we revalidate quietly in the background instead.
const tasks          = ref(checklistCache.tasks ?? []);
const summary        = ref(checklistCache.summary ?? { ...SUMMARY_DEFAULT });
const loading        = ref(checklistCache.tasks === null && !!props.weddingPlan?.initialized);
const error          = ref(null);
const filterStatus   = ref('');
const filterCat      = ref('');
const filterPriority = ref('');
const filterAssignee = ref('');
const sortBy         = ref('');
const groupBy        = ref('category');
const eventDate      = ref('');
const savingDate     = ref(false);
const eventDateError = ref('');
const showForm       = ref(false);
const editingTask    = ref(null);
const togglingId     = ref(null);
const moveDoneToBottom = ref(false);
const view           = ref('timeline');  // 'timeline' | 'list' | 'kanban'
const activeChip     = ref('all');

// ── First-run setup choice ────────────────────────────────────────────────
// The plan is "initialized" once the couple picks standard template OR blank.
const initialized  = ref(!!props.weddingPlan?.initialized);
const setupLoading = ref(null); // 'standard' | 'blank' while a choice is saving
const hasSystemTasks = computed(() => tasks.value.some(t => t.source === 'system'));

// ── Expand state — default empty = all closed ─────────────────────────────
const EXPAND_KEY = 'checklist_expanded_v1';
const expandedGroups = ref(new Set(
    JSON.parse(sessionStorage.getItem(EXPAND_KEY) ?? '[]')
));

watch(expandedGroups, (v) => {
    sessionStorage.setItem(EXPAND_KEY, JSON.stringify([...v]));
}, { deep: true });

function toggleGroup(key) {
    const next = new Set(expandedGroups.value);
    if (next.has(key)) next.delete(key);
    else next.add(key);
    expandedGroups.value = next;
}

// ── Subtask state ──────────────────────────────────────────────────────────
const expandedTasks = ref(new Set());
const subtaskMap    = reactive({});

function getSubtaskState(taskId) {
    if (!subtaskMap[taskId]) {
        subtaskMap[taskId] = { items: [], loading: false, loaded: false, newTitle: '', saving: false };
    }
    return subtaskMap[taskId];
}

async function loadSubtasks(taskId) {
    const state = getSubtaskState(taskId);
    if (state.loaded || state.loading) return;
    state.loading = true;
    try {
        const { data } = await axios.get(route('dashboard.checklist.tasks.subtasks.index', taskId));
        state.items  = data.subtasks;
        state.loaded = true;
    } finally {
        state.loading = false;
    }
}

function toggleExpand(task) {
    const next = new Set(expandedTasks.value);
    if (next.has(task.id)) {
        next.delete(task.id);
    } else {
        next.add(task.id);
        loadSubtasks(task.id);
    }
    expandedTasks.value = next;
}

async function addSubtask(task) {
    const state = getSubtaskState(task.id);
    const title = state.newTitle.trim();
    if (!title || state.saving) return;
    state.saving = true;
    try {
        const { data } = await axios.post(route('dashboard.checklist.tasks.subtasks.store', task.id), { title });
        state.items.push(data);
        state.newTitle = '';
        task.subtasks_count = (task.subtasks_count || 0) + 1;
    } finally {
        state.saving = false;
    }
}

async function toggleSubtask(task, subtask) {
    const prev = subtask.is_completed;
    subtask.is_completed = !subtask.is_completed;
    try {
        await axios.patch(
            route('dashboard.checklist.tasks.subtasks.update', [task.id, subtask.id]),
            { is_completed: subtask.is_completed }
        );
        const state = getSubtaskState(task.id);
        task.subtasks_done_count = state.items.filter(s => s.is_completed).length;
    } catch {
        subtask.is_completed = prev;
    }
}

async function deleteSubtask(task, subtask) {
    await axios.delete(route('dashboard.checklist.tasks.subtasks.destroy', [task.id, subtask.id]));
    const state = getSubtaskState(task.id);
    const idx   = state.items.indexOf(subtask);
    if (idx > -1) state.items.splice(idx, 1);
    task.subtasks_count      = Math.max(0, (task.subtasks_count || 0) - 1);
    if (subtask.is_completed) {
        task.subtasks_done_count = Math.max(0, (task.subtasks_done_count || 0) - 1);
    }
}

// ── Bulk selection ─────────────────────────────────────────────────────────
const bulkMode    = ref(false);
const selectedIds = ref(new Set());
const bulking     = ref(false);
let longPressTimer = null;

function startBulkMode(task) {
    bulkMode.value = true;
    selectedIds.value = new Set([task.id]);
}

function cancelBulkMode() {
    bulkMode.value = false;
    selectedIds.value = new Set();
}

function toggleSelect(task) {
    const next = new Set(selectedIds.value);
    if (next.has(task.id)) next.delete(task.id);
    else next.add(task.id);
    selectedIds.value = next;
    if (next.size === 0) cancelBulkMode();
}

function onLongPressStart(task) {
    if (bulkMode.value) return;
    longPressTimer = setTimeout(() => startBulkMode(task), 500);
}

function onLongPressCancel() {
    clearTimeout(longPressTimer);
}

// ── Swipe state ────────────────────────────────────────────────────────────
const swipeMap = reactive({});

function getSwipe(id) {
    if (!swipeMap[id]) swipeMap[id] = { startX: 0, offsetX: 0, open: false, dragging: false };
    return swipeMap[id];
}

function onSwipeStart(task, e) {
    if (bulkMode.value) return;
    const sw = getSwipe(task.id);
    sw.startX   = e.touches[0].clientX;
    sw.dragging = true;
}

function onSwipeMove(task, e) {
    const sw = getSwipe(task.id);
    if (!sw.dragging) return;
    const dx = e.touches[0].clientX - sw.startX;
    if (dx < 0) sw.offsetX = Math.max(dx, -120);
    else if (sw.open) sw.offsetX = Math.min(0, -120 + dx);
}

function onSwipeEnd(task) {
    const sw = getSwipe(task.id);
    sw.dragging = false;
    if (sw.offsetX < -55) { sw.offsetX = -120; sw.open = true; }
    else { sw.offsetX = 0; sw.open = false; }
}

function closeSwipe(id) {
    const sw = swipeMap[id];
    if (sw) { sw.offsetX = 0; sw.open = false; }
}


// ── Form ───────────────────────────────────────────────────────────────────
const ASSIGNEE_OPTIONS = computed(() => [
    { value: 'bride',   label: t('dashboard.checklist.assignee.bride') },
    { value: 'groom',   label: t('dashboard.checklist.assignee.groom') },
    { value: 'both',    label: t('dashboard.checklist.assignee.both') },
    { value: 'parents', label: t('dashboard.checklist.assignee.parents') },
    { value: 'family',  label: t('dashboard.checklist.assignee.family') },
    { value: 'wo',      label: t('dashboard.checklist.assignee.wo') },
    { value: 'custom',  label: t('dashboard.checklist.assignee.custom') },
]);

const emptyForm = () => ({
    title:                '',
    category:             'lainnya',
    priority:             'medium',
    due_date:             '',
    description:          '',
    assignee_type:        '',
    assignee_label:       '',
    reminder_enabled:     false,
    reminder_offset_days: 7,
    vendor:               '',
});

const form      = ref(emptyForm());
const formError = ref({});
const saving    = ref(false);
const customCategory     = ref('');
const usingCustomCategory = ref(false);

// ── Toast ──────────────────────────────────────────────────────────────────
const toast      = ref(null);
let   toastTimer = null;
function showToast(message, type = 'success') {
    clearTimeout(toastTimer);
    toast.value = { message, type };
    toastTimer = setTimeout(() => { toast.value = null; }, 3500);
}

// ── Categories ─────────────────────────────────────────────────────────────
const CATEGORY_ORDER = [
    'administrasi','venue','vendor','undangan','keuangan',
    'busana','dekorasi','dokumentasi','tamu','acara','lainnya',
];

const categories = computed(() => [
    { value: 'administrasi', label: t('dashboard.checklist.category.administrasi') },
    { value: 'venue',        label: t('dashboard.checklist.category.venue') },
    { value: 'vendor',       label: t('dashboard.checklist.category.vendor') },
    { value: 'undangan',     label: t('dashboard.checklist.category.undangan') },
    { value: 'keuangan',     label: t('dashboard.checklist.category.keuangan') },
    { value: 'busana',       label: t('dashboard.checklist.category.busana') },
    { value: 'dekorasi',     label: t('dashboard.checklist.category.dekorasi') },
    { value: 'dokumentasi',  label: t('dashboard.checklist.category.dokumentasi') },
    { value: 'tamu',         label: t('dashboard.checklist.category.tamu') },
    { value: 'acara',        label: t('dashboard.checklist.category.acara') },
    { value: 'lainnya',      label: t('dashboard.checklist.category.lainnya') },
]);

const categoryLabel = (val) => {
    const found = categories.value.find(c => c.value === val);
    return found ? found.label : (val.charAt(0).toUpperCase() + val.slice(1));
};

const priorityConfig = computed(() => ({
    high:   { label: t('dashboard.checklist.priority.high'),   dotClass: 'bg-red-500',   textClass: 'text-red-600'   },
    medium: { label: t('dashboard.checklist.priority.medium'), dotClass: 'bg-[#92A89C]', textClass: 'text-[#73877C]' },
    low:    { label: t('dashboard.checklist.priority.low'),    dotClass: 'bg-stone-300', textClass: 'text-stone-400' },
}));

const assigneeLabel = (type, label) => {
    if (!type) return null;
    if (type === 'custom') return label || 'Custom';
    return ASSIGNEE_OPTIONS.value.find(o => o.value === type)?.label ?? type;
};

// ── Urgency ────────────────────────────────────────────────────────────────
function urgencyInfo(task) {
    if (!task.due_date || task.status === 'done' || task.status === 'archived') return null;
    const now = new Date(); now.setHours(0, 0, 0, 0);
    const due = new Date(task.due_date + 'T00:00:00');
    const diff = Math.round((due - now) / 86400000);

    if (diff < 0)  return { label: t('dashboard.checklist.urgency.overdue', { days: Math.abs(diff) }),  cls: 'bg-red-50 text-red-500 border border-red-100' };
    if (diff === 0) return { label: t('dashboard.checklist.urgency.today'),                              cls: 'bg-orange-50 text-orange-500 border border-orange-100' };
    if (diff === 1) return { label: t('dashboard.checklist.urgency.tomorrow'),                           cls: 'bg-amber-50 text-amber-600 border border-amber-100' };
    if (diff <= 7)  return { label: t('dashboard.checklist.urgency.daysLeft', { days: diff }),           cls: 'bg-yellow-50 text-yellow-600 border border-yellow-100' };
    return { label: t('dashboard.checklist.urgency.countdown', { days: diff }), cls: 'bg-stone-50 text-stone-400' };
}

// ── Computed ───────────────────────────────────────────────────────────────
const activeTasks   = computed(() => tasks.value.filter(t => t.status !== 'archived'));
const archivedTasks = computed(() => tasks.value.filter(t => t.status === 'archived'));

const priorityOrder = { high: 0, medium: 1, low: 2 };

const baseList = computed(() => {
    let list = filterStatus.value === 'archived' ? archivedTasks.value : activeTasks.value;
    if (filterStatus.value && filterStatus.value !== 'archived') {
        list = list.filter(t => t.status === filterStatus.value);
    }
    if (filterCat.value)      list = list.filter(t => t.category === filterCat.value);
    if (filterPriority.value) list = list.filter(t => t.priority === filterPriority.value);
    if (filterAssignee.value) list = list.filter(t => t.assignee_type === filterAssignee.value);
    return list;
});

function sortList(list) {
    let sorted = [...list];
    if (sortBy.value === 'due_date') {
        sorted.sort((a, b) => {
            if (!a.due_date && !b.due_date) return 0;
            if (!a.due_date) return 1;
            if (!b.due_date) return -1;
            return new Date(a.due_date) - new Date(b.due_date);
        });
    } else if (sortBy.value === 'priority') {
        sorted.sort((a, b) => (priorityOrder[a.priority] ?? 1) - (priorityOrder[b.priority] ?? 1));
    }
    if (moveDoneToBottom.value) {
        sorted.sort((a, b) => {
            if (a.status === 'done' && b.status !== 'done') return 1;
            if (a.status !== 'done' && b.status === 'done') return -1;
            return 0;
        });
    }
    return sorted;
}

function buildGroupShape(key, label, list) {
    const done  = list.filter(t => t.status === 'done').length;
    const total = list.length;
    return {
        cat: key,
        label,
        tasks:    sortList(list),
        done,
        total,
        progress: total > 0 ? Math.round((done / total) * 100) : 0,
    };
}

function categoryGroups(list) {
    const map = new Map();
    for (const task of list) {
        if (!map.has(task.category)) map.set(task.category, []);
        map.get(task.category).push(task);
    }
    return [...map.entries()]
        .sort((a, b) => {
            const ai = CATEGORY_ORDER.indexOf(a[0]);
            const bi = CATEGORY_ORDER.indexOf(b[0]);
            if (ai === -1 && bi === -1) return a[0].localeCompare(b[0]);
            if (ai === -1) return 1;
            if (bi === -1) return -1;
            return ai - bi;
        })
        .map(([cat, catTasks]) => {
            const allForCat  = activeTasks.value.filter(t => t.category === cat);
            const doneCount  = allForCat.filter(t => t.status === 'done').length;
            const totalCount = allForCat.length;
            return {
                cat,
                label:    categoryLabel(cat),
                tasks:    sortList(catTasks),
                done:     doneCount,
                total:    totalCount,
                progress: totalCount > 0 ? Math.round((doneCount / totalCount) * 100) : 0,
            };
        });
}

function deadlineGroups(list) {
    const now = new Date(); now.setHours(0, 0, 0, 0);
    const buckets = {
        overdue: [],
        today:   [],
        week:    [],
        month:   [],
        later:   [],
        done:    [],
    };

    for (const task of list) {
        if (task.status === 'done') { buckets.done.push(task); continue; }
        if (!task.due_date) { buckets.later.push(task); continue; }
        const due  = new Date(task.due_date + 'T00:00:00');
        const diff = Math.round((due - now) / 86400000);
        if (diff < 0)       buckets.overdue.push(task);
        else if (diff === 0) buckets.today.push(task);
        else if (diff <= 7)  buckets.week.push(task);
        else if (diff <= 30) buckets.month.push(task);
        else                 buckets.later.push(task);
    }

    const sections = [
        { key: 'overdue', label: t('dashboard.checklist.deadlineGroup.overdue'), list: buckets.overdue },
        { key: 'today',   label: t('dashboard.checklist.deadlineGroup.today'),   list: buckets.today   },
        { key: 'week',    label: t('dashboard.checklist.deadlineGroup.week'),    list: buckets.week    },
        { key: 'month',   label: t('dashboard.checklist.deadlineGroup.month'),   list: buckets.month   },
        { key: 'later',   label: t('dashboard.checklist.deadlineGroup.later'),   list: buckets.later   },
        { key: 'done',    label: t('dashboard.checklist.deadlineGroup.done'),    list: buckets.done    },
    ];

    return sections
        .filter(s => s.list.length > 0)
        .map(s => buildGroupShape(s.key, s.label, s.list));
}

function assigneeGroups(list) {
    const map = new Map();
    for (const task of list) {
        const key = task.assignee_type || '__none__';
        if (!map.has(key)) map.set(key, []);
        map.get(key).push(task);
    }
    return [...map.entries()].map(([key, items]) => {
        const label = key === '__none__'
            ? t('dashboard.checklist.assignee.noPic')
            : assigneeLabel(key, items[0]?.assignee_label) ?? key;
        return buildGroupShape(key, label, items);
    });
}

const groups = computed(() => {
    const list = displayList.value;
    if (groupBy.value === 'deadline') return deadlineGroups(list);
    if (groupBy.value === 'assignee') return assigneeGroups(list);
    return categoryGroups(list);
});

const allDone = computed(() =>
    summary.value.total > 0 && summary.value.todo === 0
);

// ── New desktop redesign computeds ────────────────────────────────────────
const daysUntil = computed(() => {
    const ed = props.weddingPlan?.event_date || summary.value.event_date;
    if (!ed) return null;
    const now = new Date(); now.setHours(0,0,0,0);
    const d = new Date(ed + 'T00:00:00');
    return Math.max(0, Math.round((d - now) / 86400000));
});
function isUrgentTask(tk) {
    if (tk.status !== 'todo' || !tk.due_date) return false;
    const now = new Date(); now.setHours(0,0,0,0);
    const diff = Math.round((new Date(tk.due_date + 'T00:00:00') - now) / 86400000);
    return diff <= 1 || (tk.priority === 'high' && diff <= 7);
}
const urgentCount   = computed(() => activeTasks.value.filter(isUrgentTask).length);
const doneThisMonth = computed(() => {
    const n = new Date(); const y = n.getFullYear(); const m = n.getMonth();
    return tasks.value.filter(tk => tk.status === 'done' && tk.completed_at &&
        new Date(tk.completed_at).getFullYear() === y && new Date(tk.completed_at).getMonth() === m).length;
});
const picSplit = computed(() => {
    const b = activeTasks.value.filter(tk => tk.assignee_type === 'bride').length;
    const g = activeTasks.value.filter(tk => tk.assignee_type === 'groom').length;
    const tot = b + g;
    return { brideCount: b, groomCount: g, bridePct: tot ? Math.round(b/tot*100) : 0, groomPct: tot ? Math.round(g/tot*100) : 0 };
});
const filterChips = computed(() => {
    const base = activeTasks.value;
    const chips = [
        { key: 'all',    label: t('dashboard.checklist.chip.all'),    count: base.length },
        { key: 'urgent', label: t('dashboard.checklist.chip.urgent'), count: urgentCount.value },
        { key: 'todo',   label: t('dashboard.checklist.chip.todo'),   count: base.filter(tk => tk.status === 'todo').length },
        { key: 'done',   label: t('dashboard.checklist.chip.done'),   count: base.filter(tk => tk.status === 'done').length },
    ];
    for (const c of ['venue','vendor','dekorasi','busana','dokumentasi','acara']) {
        const n = base.filter(tk => tk.category === c).length;
        if (n > 0) chips.push({ key: 'cat:' + c, label: categoryLabel(c), count: n });
    }
    return chips;
});
function onChip(key) {
    activeChip.value = key;
    filterStatus.value = ''; filterCat.value = ''; filterPriority.value = '';
    if (key === 'todo' || key === 'done') filterStatus.value = key;
    else if (key.startsWith('cat:')) filterCat.value = key.slice(4);
}
const H_STAMP = { overdue: 'LEWAT', today: 'TODAY', week: '7 HARI', month: 'BULAN INI', later: 'NANTI', done: '✓' };
const displayList = computed(() => {
    let list = baseList.value;
    if (activeChip.value === 'urgent') list = list.filter(isUrgentTask);
    return list;
});
const timelineGroups = computed(() => deadlineGroups(displayList.value).map(g => ({ ...g, stamp: H_STAMP[g.cat] ?? g.cat.toUpperCase() })));
const kanbanColumns  = computed(() => categoryGroups(displayList.value).map(g => ({ key: g.cat, label: g.label, tasks: g.tasks })));
const reminders = computed(() =>
    activeTasks.value.filter(tk => tk.status === 'todo' && tk.due_date)
        .sort((a, b) => new Date(a.due_date) - new Date(b.due_date)).slice(0, 4)
        .map(tk => ({ when: tk.due_date, title: tk.title,
            who: tk.assignee_type === 'groom' ? 'groom' : (tk.assignee_type === 'bride' ? 'bride' : null),
            urgent: isUrgentTask(tk) }))
);
// ── Mobile layout state ───────────────────────────────────────────────────
const isMobileView     = useMediaQuery('(max-width: 1023px)');
const showMobileFilter = ref(false);
const mobileTask       = ref(null);
const mobileSubtasks   = computed(() => mobileTask.value ? getSubtaskState(mobileTask.value.id) : { items: [], newTitle: '' });

function openMobileTask(tk) { mobileTask.value = tk; loadSubtasks(tk.id); }
function closeMobileTask()  { mobileTask.value = null; }
function mobileEdit(tk)     { mobileTask.value = null; openEdit(tk); }
function resetMobileFilters() { filterStatus.value=''; filterCat.value=''; filterPriority.value=''; filterAssignee.value=''; sortBy.value=''; activeChip.value='all'; }

const mobileBuckets   = computed(() => timelineGroups.value.filter(g => g.cat !== 'done'));
const mobileDoneCount = computed(() => summary.value.done);
const resultCount     = computed(() => displayList.value.length);

async function applyStandardTemplate() {
    // The standard set is "applied" only when system tasks actually exist —
    // a blank-started plan is initialized but still has none.
    if (hasSystemTasks.value) { showToast(t('dashboard.checklist.rail.templates.applied')); return; }
    await axios.post(route('dashboard.checklist.initialize'));
    initialized.value = true;
    await Promise.all([loadTasks(), loadSummary()]);
    showToast(t('dashboard.checklist.toast.templateApplied'));
}

// ── First-run choice: standard template vs. start blank ───────────────────
async function chooseStandard() {
    if (setupLoading.value) return;
    setupLoading.value = 'standard';
    try {
        await axios.post(route('dashboard.checklist.initialize'));
        initialized.value = true;
        await Promise.all([loadTasks(), loadSummary()]);
        playSwipeHint();
        showToast(t('dashboard.checklist.toast.templateApplied'));
    } catch {
        error.value = t('dashboard.checklist.error.loadFailed');
    } finally {
        setupLoading.value = null;
    }
}

async function chooseBlank() {
    if (setupLoading.value) return;
    setupLoading.value = 'blank';
    try {
        await axios.post(route('dashboard.checklist.initialize'), { mode: 'blank' });
        initialized.value = true;
        await Promise.all([loadTasks(), loadSummary()]);
    } catch {
        error.value = t('dashboard.checklist.error.loadFailed');
    } finally {
        setupLoading.value = null;
    }
}
function exportCalendar() { window.location.href = route('dashboard.checklist.export'); }

// ── Swipe hint (peek animation on load) ───────────────────────────────────
const swipeHintId = ref(null);

async function playSwipeHint() {
    const firstTask = tasks.value.find(t => t.status === 'todo');
    if (!firstTask) return;

    // Expand the group containing this task so the animation is visible
    const firstGroup = groups.value.find(g => g.tasks.some(t => t.id === firstTask.id));
    if (firstGroup && !expandedGroups.value.has(firstGroup.cat)) {
        toggleGroup(firstGroup.cat);
        await new Promise(r => setTimeout(r, 300));
    }

    await new Promise(r => setTimeout(r, 500));
    swipeHintId.value = firstTask.id;
    const sw = getSwipe(firstTask.id);
    sw.offsetX = -72;
    await new Promise(r => setTimeout(r, 550));
    sw.offsetX = 0;
    await new Promise(r => setTimeout(r, 300));
    swipeHintId.value = null;
}

// ── Bootstrap ──────────────────────────────────────────────────────────────
onMounted(async () => {
    try {
        // Don't auto-apply the template anymore. New couples get a choice
        // (standard vs. blank); only load existing data here.
        if (!initialized.value) return;

        if (checklistCache.tasks !== null) {
            // Have cached data → show it instantly, revalidate in background.
            loading.value = false;
            Promise.all([loadTasks(), loadSummary()]).catch(() => {});
        } else {
            // First load this session → wait + show the spinner once.
            await Promise.all([loadTasks(), loadSummary()]);
            playSwipeHint();
        }
    } catch {
        error.value = t('dashboard.checklist.error.loadFailed');
    } finally {
        loading.value = false;
    }
});

async function loadTasks() {
    const { data } = await axios.get(route('dashboard.checklist.tasks'));
    tasks.value = data.tasks;
    checklistCache.tasks = data.tasks;
}

async function loadSummary() {
    const { data } = await axios.get(route('dashboard.checklist.summary'));
    summary.value = data;
    checklistCache.summary = data;
}

async function saveEventDate() {
    eventDateError.value = '';
    if (!eventDate.value || savingDate.value) return;
    const today = new Date(); today.setHours(0, 0, 0, 0);
    if (new Date(eventDate.value) < today) {
        eventDateError.value = t('dashboard.checklist.eventDate.errorPast');
        return;
    }
    savingDate.value = true;
    try {
        await axios.patch(route('dashboard.checklist.event-date'), { event_date: eventDate.value });
        await Promise.all([loadTasks(), loadSummary()]);
    } catch (e) {
        eventDateError.value = e.response?.data?.errors?.event_date?.[0] ?? t('dashboard.checklist.eventDate.errorSave');
    } finally {
        savingDate.value = false;
    }
}

// ── Toggle ─────────────────────────────────────────────────────────────────
async function toggle(task) {
    if (togglingId.value || bulkMode.value) return;
    const prev = task.status;
    task.status = task.status === 'done' ? 'todo' : 'done';
    togglingId.value = task.id;
    try {
        const { data } = await axios.patch(route('dashboard.checklist.tasks.toggle', task.id));
        Object.assign(task, data);
        await loadSummary();
    } catch {
        task.status = prev;
    } finally {
        togglingId.value = null;
    }
}

// ── Archive / Restore ──────────────────────────────────────────────────────
async function archiveTask(task) {
    closeSwipe(task.id);
    await axios.patch(route('dashboard.checklist.tasks.archive', task.id));
    await Promise.all([loadTasks(), loadSummary()]);
}

async function restoreTask(task) {
    await axios.patch(route('dashboard.checklist.tasks.restore', task.id));
    filterStatus.value = '';
    await Promise.all([loadTasks(), loadSummary()]);
}

// ── Delete ─────────────────────────────────────────────────────────────────
async function deleteTask(task) {
    closeSwipe(task.id);
    if (!confirm(t('dashboard.checklist.actions.deleteConfirm', { title: task.title }))) return;
    await axios.delete(route('dashboard.checklist.tasks.destroy', task.id));
    await Promise.all([loadTasks(), loadSummary()]);
    showToast(t('dashboard.checklist.actions.toastDeleted'));
}

// ── Bulk ───────────────────────────────────────────────────────────────────
async function doBulkAction(action) {
    if (selectedIds.value.size === 0 || bulking.value) return;
    bulking.value = true;
    try {
        await axios.post(route('dashboard.checklist.tasks.bulk'), {
            ids: [...selectedIds.value],
            action,
        });
        cancelBulkMode();
        await Promise.all([loadTasks(), loadSummary()]);
        const msg = action === 'done'
            ? t('dashboard.checklist.actions.toastBulkDone')
            : action === 'archive'
                ? t('dashboard.checklist.actions.toastBulkArchived')
                : t('dashboard.checklist.actions.toastBulkDeleted');
        showToast(msg);
    } catch {
        showToast(t('dashboard.checklist.actions.toastBulkFailed'), 'error');
    } finally {
        bulking.value = false;
    }
}

// ── Create / Edit ──────────────────────────────────────────────────────────
function openCreate() {
    editingTask.value = null;
    form.value = emptyForm();
    formError.value = {};
    usingCustomCategory.value = false;
    customCategory.value = '';
    showForm.value = true;
}

function openEdit(task) {
    closeSwipe(task.id);
    editingTask.value = task;
    const isKnown = categories.value.some(c => c.value === task.category);
    usingCustomCategory.value = !isKnown;
    customCategory.value = isKnown ? '' : task.category;
    form.value = {
        title:                task.title,
        category:             isKnown ? task.category : 'lainnya',
        priority:             task.priority,
        due_date:             task.due_date ?? '',
        description:          task.description ?? '',
        assignee_type:        task.assignee_type ?? '',
        assignee_label:       task.assignee_label ?? '',
        reminder_enabled:     task.reminder_enabled ?? false,
        reminder_offset_days: task.reminder_offset_days ?? 7,
        vendor:               task.vendor ?? '',
    };
    formError.value = {};
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingTask.value = null;
}

async function saveForm() {
    formError.value = {};
    saving.value = true;
    const effectiveCategory = usingCustomCategory.value
        ? customCategory.value.trim()
        : form.value.category;
    if (!effectiveCategory) {
        formError.value.category = t('dashboard.checklist.form.categoryRequired');
        saving.value = false;
        return;
    }
    try {
        const payload = {
            ...form.value,
            category:       effectiveCategory,
            due_date:       form.value.due_date || null,
            assignee_type:  form.value.assignee_type || null,
            assignee_label: form.value.assignee_type === 'custom' ? (form.value.assignee_label || null) : null,
        };
        if (editingTask.value) {
            await axios.patch(route('dashboard.checklist.tasks.update', editingTask.value.id), payload);
        } else {
            await axios.post(route('dashboard.checklist.tasks.store'), payload);
        }
        const isEdit = !!editingTask.value;
        closeForm();
        await Promise.all([loadTasks(), loadSummary()]);
        showToast(isEdit ? t('dashboard.checklist.actions.toastUpdated') : t('dashboard.checklist.actions.toastAdded'));
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data.errors ?? {};
            formError.value = Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, v[0]]));
        }
    } finally {
        saving.value = false;
    }
}

// ── Date picker modal ──────────────────────────────────────────────────────
const MONTHS_ID = computed(() => [
    t('dashboard.checklist.months.jan'), t('dashboard.checklist.months.feb'),
    t('dashboard.checklist.months.mar'), t('dashboard.checklist.months.apr'),
    t('dashboard.checklist.months.may'), t('dashboard.checklist.months.jun'),
    t('dashboard.checklist.months.jul'), t('dashboard.checklist.months.aug'),
    t('dashboard.checklist.months.sep'), t('dashboard.checklist.months.oct'),
    t('dashboard.checklist.months.nov'), t('dashboard.checklist.months.dec'),
]);
const DAYS_ID = computed(() => [
    t('dashboard.checklist.days.sun'), t('dashboard.checklist.days.mon'),
    t('dashboard.checklist.days.tue'), t('dashboard.checklist.days.wed'),
    t('dashboard.checklist.days.thu'), t('dashboard.checklist.days.fri'),
    t('dashboard.checklist.days.sat'),
]);

const showAiModal    = ref(false);
const showDatePicker = ref(false);
const datePickerMode = ref('');
const calToday       = new Date();
const calYear        = ref(calToday.getFullYear());
const calMonth       = ref(calToday.getMonth());

function openDatePicker(mode) {
    datePickerMode.value = mode;
    const val = mode === 'event' ? eventDate.value : form.value.due_date;
    if (val) {
        const [y, m] = val.split('-').map(Number);
        calYear.value  = y;
        calMonth.value = m - 1;
    } else {
        calYear.value  = calToday.getFullYear();
        calMonth.value = calToday.getMonth();
    }
    showDatePicker.value = true;
}
function closeDatePicker() { showDatePicker.value = false; }
function prevCalMonth() {
    if (calMonth.value === 0) { calMonth.value = 11; calYear.value--; }
    else calMonth.value--;
}
function nextCalMonth() {
    if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; }
    else calMonth.value++;
}
const calDays = computed(() => {
    const first = new Date(calYear.value, calMonth.value, 1).getDay();
    const total = new Date(calYear.value, calMonth.value + 1, 0).getDate();
    const cells = [];
    for (let i = 0; i < first; i++) cells.push(null);
    for (let d = 1; d <= total; d++) cells.push(d);
    return cells;
});
function pickDay(day) {
    if (!day) return;
    const m   = String(calMonth.value + 1).padStart(2, '0');
    const d   = String(day).padStart(2, '0');
    const val = `${calYear.value}-${m}-${d}`;
    if (datePickerMode.value === 'event') eventDate.value = val;
    else form.value.due_date = val;
}
function isPickedDay(day) {
    if (!day) return false;
    const val = datePickerMode.value === 'event' ? eventDate.value : form.value.due_date;
    if (!val) return false;
    const [y, m, d] = val.split('-').map(Number);
    return y === calYear.value && m === calMonth.value + 1 && d === day;
}
function isCalPastDay(day) {
    if (!day || datePickerMode.value !== 'event') return false;
    const t = new Date(); t.setHours(0, 0, 0, 0);
    return new Date(calYear.value, calMonth.value, day) < t;
}
function calDisplayDate(dateStr) {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString(locale.value === 'en' ? 'en-US' : 'id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}
const currentPickerDate = computed(() =>
    datePickerMode.value === 'event' ? eventDate.value : form.value.due_date
);


</script>

<template>
    <DashboardLayout>
        <PlannerPanel :initial="plannerPanel" />
        <template #header>
            <h1 class="text-base font-semibold text-stone-800">{{ t('dashboard.checklist.header.title') }}</h1>
        </template>

        <!-- Toast -->
        <Transition name="slide-down">
            <div
                v-if="toast"
                :class="[
                    'fixed top-4 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-medium',
                    toast.type === 'error'
                        ? 'bg-rose-50 text-rose-700 border border-rose-100'
                        : 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                ]"
                role="alert"
                aria-live="polite"
            >
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="toast.type !== 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v4m0 4h.01"/>
                </svg>
                {{ toast.message }}
            </div>
        </Transition>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-24">
            <div class="w-8 h-8 rounded-full border-2 border-[#92A89C]/50 border-t-[#73877C] animate-spin"/>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="flex flex-col items-center py-24 gap-3">
            <p class="text-stone-500 text-sm">{{ error }}</p>
            <button @click="() => { error = null; loading = true; onMounted(); }"
                    class="text-sm text-[#73877C] underline">{{ t('dashboard.checklist.error.retry') }}</button>
        </div>

        <template v-else>

            <!-- ── First-run setup choice ─────────────────────────── -->
            <div v-if="!initialized" class="max-w-2xl mx-auto py-10 sm:py-16 px-1">
                <div class="text-center mb-7">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center bg-[#92A89C]/10">
                        <svg class="w-7 h-7 text-[#92A89C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h2 class="font-cormorant font-medium text-[28px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.setup.title') }}</h2>
                    <p class="text-[13px] mt-1" style="color:#6C7A75;">{{ t('dashboard.checklist.setup.sub') }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <!-- Standard (recommended) -->
                    <button type="button" @click="chooseStandard" :disabled="!!setupLoading"
                            class="group relative text-left rounded-2xl border-2 p-5 transition-all hover:-translate-y-0.5 disabled:opacity-60 disabled:translate-y-0"
                            style="border-color:#92A89C; background:#FBFCF9;">
                        <span class="absolute top-3 right-3 text-[10px] font-semibold px-2 py-0.5 rounded-full"
                              style="background:rgba(146,168,156,0.18); color:#4A5A4C;">{{ t('dashboard.checklist.setup.recommended') }}</span>
                        <div class="w-10 h-10 rounded-xl grid place-items-center mb-3" style="background:#92A89C;">
                            <WidgetIcon name="check" :size="18" stroke="#fff" />
                        </div>
                        <div class="text-[15px] font-semibold mb-1" style="color:#1F2A2E;">{{ t('dashboard.checklist.setup.standardTitle') }}</div>
                        <p class="text-[12.5px] leading-relaxed" style="color:#6C7A75;">{{ t('dashboard.checklist.setup.standardDesc') }}</p>
                        <span v-if="setupLoading === 'standard'" class="mt-3 inline-flex items-center gap-2 text-[12px] font-medium" style="color:#4A5A4C;">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ t('dashboard.checklist.setup.applying') }}
                        </span>
                    </button>

                    <!-- Start blank -->
                    <button type="button" @click="chooseBlank" :disabled="!!setupLoading"
                            class="group text-left rounded-2xl border-2 border-stone-200 p-5 transition-all hover:-translate-y-0.5 hover:border-stone-300 disabled:opacity-60 disabled:translate-y-0"
                            style="background:#FFFFFF;">
                        <div class="w-10 h-10 rounded-xl grid place-items-center mb-3 bg-stone-100">
                            <WidgetIcon name="plus" :size="18" stroke="#6C7A75" />
                        </div>
                        <div class="text-[15px] font-semibold mb-1" style="color:#1F2A2E;">{{ t('dashboard.checklist.setup.blankTitle') }}</div>
                        <p class="text-[12.5px] leading-relaxed" style="color:#6C7A75;">{{ t('dashboard.checklist.setup.blankDesc') }}</p>
                        <span v-if="setupLoading === 'blank'" class="mt-3 inline-flex items-center gap-2 text-[12px] font-medium text-stone-500">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ t('dashboard.checklist.setup.preparing') }}
                        </span>
                    </button>
                </div>

                <p class="text-center text-[11.5px] mt-4" style="color:#9AA69F;">{{ t('dashboard.checklist.setup.note') }}</p>
            </div>

            <!-- ── Bulk action bar ────────────────────────────────── -->
            <Transition name="slide-down">
                <div v-if="bulkMode && initialized"
                     class="sticky top-0 z-20 mb-4 px-4 py-3 bg-white border border-stone-200 rounded-xl shadow-sm flex items-center gap-2">
                    <span class="text-sm font-medium text-stone-700 flex-1">{{ t('dashboard.checklist.bulk.selected', { count: selectedIds.size }) }}</span>
                    <button @click="doBulkAction('done')" :disabled="bulking"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-green-50 text-green-700 hover:bg-green-100 transition-colors disabled:opacity-50">{{ t('dashboard.checklist.bulk.markDone') }}</button>
                    <button @click="doBulkAction('archive')" :disabled="bulking"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-stone-100 text-stone-600 hover:bg-stone-200 transition-colors disabled:opacity-50">{{ t('dashboard.checklist.bulk.archive') }}</button>
                    <button @click="doBulkAction('delete')" :disabled="bulking"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors disabled:opacity-50">{{ t('dashboard.checklist.bulk.delete') }}</button>
                    <button @click="cancelBulkMode"
                            class="ml-1 p-1.5 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </Transition>

            <!-- ── Desktop layout wrapper ─────────────────────────── -->
            <div v-if="initialized && !isMobileView" class="w-full">

                <!-- Page heading + top actions -->
                <div class="flex items-end justify-between gap-3 mb-4 flex-wrap">
                    <div>
                        <h1 class="font-cormorant font-medium text-[30px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.pageTitle') }}</h1>
                        <p class="text-[13px]" style="color:#6C7A75;">{{ t('dashboard.checklist.pageSub', { total: summary.total }) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <ChecklistViewToggle :view="view" @update="view = $event" />
                        <button type="button" @click="exportCalendar" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
                            <WidgetIcon name="cal" :size="13" stroke="#4A5A4C" /> {{ t('dashboard.checklist.exportCalendar') }}
                        </button>
                        <button type="button" @click="openCreate" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[12px] font-semibold text-white" style="background:#1F2A2E;">
                            <WidgetIcon name="plus" :size="13" stroke="#fff" /> {{ t('dashboard.checklist.addTask') }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5 items-start">
                    <!-- ── Main column ────────────────────────────── -->
                    <div>
                        <ChecklistProgressHero
                            :progress="summary.progress"
                            :done="summary.done"
                            :total="summary.total"
                            :remaining="summary.todo"
                            :urgent-count="urgentCount"
                            :days-until="daysUntil"
                            :has-event-date="summary.has_event_date"
                        />
                        <ChecklistStatStrip
                            :urgent-count="urgentCount"
                            :upcoming7d="summary.upcoming_7d"
                            :done-this-month="doneThisMonth"
                            :pic-split="picSplit"
                        />
                        <ChecklistFilterChips :chips="filterChips" :active="activeChip" @select="onChip" />

                        <!-- ── All done celebration ───────────────── -->
                        <Transition name="slide-down">
                            <div v-if="allDone"
                                 class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-700 flex items-center gap-2">
                                <span class="text-lg">🎉</span>
                                <span class="font-medium">{{ t('dashboard.checklist.allDone') }}</span>
                            </div>
                        </Transition>

                        <!-- ── No event date prompt ───────────────── -->
                        <div v-if="!summary.has_event_date"
                             class="mb-4 px-4 py-3 rounded-xl bg-[#92A89C]/10 border border-[#B8C7BF]/50 text-sm text-[#73877C]">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ t('dashboard.checklist.eventDate.prompt') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openDatePicker('event')"
                                        class="flex-1 border rounded-lg px-3 py-1.5 text-sm text-left bg-white transition-colors hover:border-[#92A89C]/60"
                                        :class="eventDateError ? 'border-red-300' : 'border-[#B8C7BF]'">
                                    <span v-if="eventDate" class="text-stone-800">{{ calDisplayDate(eventDate) }}</span>
                                    <span v-else class="text-stone-400">{{ t('dashboard.checklist.eventDate.placeholder') }}</span>
                                </button>
                                <button @click="saveEventDate" :disabled="!eventDate || savingDate"
                                        class="px-4 py-1.5 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-40"
                                        style="background-color: #92A89C">
                                    {{ savingDate ? t('dashboard.checklist.eventDate.saving') : t('dashboard.checklist.eventDate.save') }}
                                </button>
                            </div>
                            <p v-if="eventDateError" class="mt-1.5 text-xs text-red-500">{{ eventDateError }}</p>
                        </div>

                        <!-- ── Kanban view ────────────────────────── -->
                        <TaskKanban
                            v-if="view === 'kanban'"
                            :columns="kanbanColumns"
                            @toggle="toggle"
                            @edit="openEdit"
                        />

                        <!-- ── Timeline / List view ───────────────── -->
                        <template v-else>

                        <!-- ── Empty state (no tasks at all) ───────── -->
                        <div v-if="tasks.length === 0" class="py-16 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center bg-[#92A89C]/10">
                                <svg class="w-8 h-8 text-[#92A89C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h3 class="text-stone-700 font-medium mb-1">{{ t('dashboard.checklist.empty.noTasks') }}</h3>
                            <p class="text-stone-400 text-sm mb-4">{{ t('dashboard.checklist.empty.noTasksHint') }}</p>
                            <button @click="openCreate"
                                    class="px-4 py-2 rounded-xl text-sm font-medium text-white"
                                    style="background-color: #92A89C">
                                {{ t('dashboard.checklist.empty.addTask') }}
                            </button>
                        </div>

                        <!-- ── Empty state (filtered) ────────────── -->
                        <div v-else-if="(view === 'timeline' ? timelineGroups : groups).length === 0" class="py-12 text-center">
                            <p class="text-stone-400 text-sm">{{ t('dashboard.checklist.empty.noFilterMatch') }}</p>
                        </div>

                        <!-- ── Groups ─────────────────────────────── -->
                        <div v-else class="space-y-1">
                            <div v-for="group in (view === 'timeline' ? timelineGroups : groups)" :key="group.cat">

                                <!-- Group header -->
                                <button
                                    class="w-full flex items-center gap-2.5 py-2.5 px-1 text-left select-none hover:bg-stone-50 rounded-lg transition-colors"
                                    @click="toggleGroup(group.cat)"
                                >
                                    <svg class="w-4 h-4 text-stone-400 flex-shrink-0 transition-transform duration-200"
                                         :class="expandedGroups.has(group.cat) ? 'rotate-90' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>

                                    <!-- Overdue indicator dot -->
                                    <span v-if="group.cat === 'overdue'"
                                          class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"/>

                                    <!-- Timeline stamp pill -->
                                    <span v-if="view === 'timeline' && group.stamp"
                                          class="text-[10px] font-bold px-1.5 py-0.5 rounded-full tracking-wider"
                                          :class="group.cat === 'overdue' ? 'bg-red-100 text-red-600' : 'bg-stone-100 text-stone-500'">
                                        {{ group.stamp }}
                                    </span>

                                    <span class="text-sm font-semibold"
                                          :class="group.cat === 'overdue' ? 'text-red-600' : 'text-stone-700'">
                                        {{ group.label }}
                                    </span>
                                    <span class="text-xs text-stone-400">{{ group.done }}/{{ group.total }}</span>
                                    <div class="flex-1 h-1.5 rounded-full bg-stone-100 overflow-hidden max-w-24">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             :style="{
                                                 width: group.progress + '%',
                                                 backgroundColor: group.cat === 'overdue' ? '#ef4444' : '#92A89C',
                                             }"/>
                                    </div>
                                    <span v-if="!expandedGroups.has(group.cat)"
                                          class="text-xs text-stone-400 ml-auto">
                                        {{ t('dashboard.checklist.group.taskCount', { count: group.tasks.length }) }}
                                    </span>
                                </button>

                                <!-- Task list -->
                                <Transition
                                    enter-active-class="transition-all duration-200 ease-out"
                                    enter-from-class="opacity-0 -translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition-all duration-150 ease-in"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 -translate-y-1"
                                >
                                <div v-show="expandedGroups.has(group.cat)" class="space-y-1.5 pb-3 pl-6">

                            <div v-for="task in group.tasks" :key="task.id"
                                 class="relative overflow-hidden rounded-xl"
                                 @click.self="closeSwipe(task.id)">

                                <!-- Swipe backdrop (mobile) -->
                                <div v-if="task.status === 'todo'"
                                     class="absolute right-0 top-0 h-full flex items-stretch">
                                    <button @click="toggle(task)"
                                            class="h-full px-4 flex flex-col items-center justify-center gap-0.5 text-xs font-medium text-green-700 bg-green-50">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ t('dashboard.checklist.swipe.done') }}
                                    </button>
                                    <button @click="openEdit(task)"
                                            class="h-full px-4 flex flex-col items-center justify-center gap-0.5 text-xs font-medium text-stone-600 bg-stone-100">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ t('dashboard.checklist.swipe.edit') }}
                                    </button>
                                    <button @click="deleteTask(task)"
                                            class="h-full px-4 flex flex-col items-center justify-center gap-0.5 text-xs font-medium text-red-600 bg-red-50">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        {{ t('dashboard.checklist.swipe.delete') }}
                                    </button>
                                </div>

                                <!-- Task card -->
                                <div
                                    class="relative z-10 rounded-[14px] px-3 py-3 transition-opacity"
                                    style="background:#FBFCF9; border:1px solid #D8DFD2;"
                                    :class="[
                                        task.status === 'done' || task.status === 'archived' ? 'opacity-60' : '',
                                        expandedTasks.has(task.id) ? 'rounded-b-none border-b-0' : '',
                                    ]"
                                    :style="{
                                        transform: `translateX(${getSwipe(task.id).offsetX}px)`,
                                        transition: swipeHintId === task.id
                                            ? 'transform 0.45s cubic-bezier(0.4,0,0.2,1)'
                                            : getSwipe(task.id).dragging ? 'none' : 'transform 0.2s ease',
                                        borderColor: selectedIds.has(task.id) ? '#92A89C' : '',
                                    }"
                                    @touchstart.passive="onSwipeStart(task, $event); onLongPressStart(task)"
                                    @touchmove.passive="onSwipeMove(task, $event); onLongPressCancel()"
                                    @touchend="onSwipeEnd(task); onLongPressCancel()"
                                >
                                    <div class="flex items-start gap-0">
                                        <!-- Checkbox -->
                                        <button
                                            class="flex-shrink-0 flex items-center justify-center w-11 h-11 -ml-1.5 -mt-0.5"
                                            @click.stop="bulkMode ? toggleSelect(task) : toggle(task)"
                                            :disabled="togglingId === task.id && !bulkMode"
                                        >
                                            <div v-if="bulkMode"
                                                 class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                                                 :class="selectedIds.has(task.id) ? 'border-[#92A89C] bg-[#92A89C]' : 'border-stone-300'">
                                                <svg v-if="selectedIds.has(task.id)" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <div v-else-if="task.status === 'archived'"
                                                 class="w-5 h-5 rounded-full border-2 border-stone-200 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/>
                                                </svg>
                                            </div>
                                            <div v-else
                                                 class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                                 :class="task.status === 'done'
                                                     ? 'border-green-500 bg-green-500'
                                                     : 'border-stone-300 hover:border-[#92A89C]'">
                                                <svg v-if="task.status === 'done'" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </button>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0 ml-0.5">
                                            <p class="text-sm font-medium text-stone-800 leading-snug line-clamp-2"
                                               :class="task.status === 'done' ? 'line-through text-stone-400' : ''">
                                                {{ task.title }}
                                            </p>
                                            <p v-if="task.description"
                                               class="text-xs text-stone-400 mt-0.5 truncate">
                                                {{ task.description }}
                                            </p>

                                            <!-- Vendor line -->
                                            <span v-if="task.vendor" class="inline-flex items-center gap-1 text-[11px] mt-0.5" style="color:#6F8270;">
                                                <WidgetIcon name="vendor" :size="11" stroke="#6F8270" /> {{ task.vendor }}
                                            </span>

                                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                                <!-- Priority -->
                                                <div class="flex items-center gap-1">
                                                    <div class="w-2 h-2 rounded-full flex-shrink-0"
                                                         :class="priorityConfig[task.priority]?.dotClass"/>
                                                    <span class="text-xs" :class="priorityConfig[task.priority]?.textClass">
                                                        {{ priorityConfig[task.priority]?.label }}
                                                    </span>
                                                </div>

                                                <!-- Urgency badge -->
                                                <span v-if="urgencyInfo(task)"
                                                      class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                                                      :class="urgencyInfo(task).cls">
                                                    {{ urgencyInfo(task).label }}
                                                </span>

                                                <!-- Assignee badge -->
                                                <span v-if="task.assignee_type"
                                                      class="text-xs px-1.5 py-0.5 rounded-full bg-[#92A89C]/10 text-[#73877C]">
                                                    {{ assigneeLabel(task.assignee_type, task.assignee_label) }}
                                                </span>

                                                <!-- Subtasks count -->
                                                <button
                                                    v-if="task.subtasks_count > 0 && task.status !== 'archived'"
                                                    class="text-xs px-1.5 py-0.5 rounded-full bg-stone-50 text-stone-500 hover:bg-stone-100 transition-colors"
                                                    @click.stop="toggleExpand(task)"
                                                >
                                                    {{ task.subtasks_done_count }}/{{ task.subtasks_count }} subtugas
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Desktop actions -->
                                        <div class="hidden sm:flex items-center gap-0.5 flex-shrink-0 ml-2">
                                            <template v-if="task.status !== 'archived'">
                                                <!-- Add subtask -->
                                                <button @click.stop="toggleExpand(task)"
                                                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors"
                                                        title="Subtugas">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                                    </svg>
                                                </button>
                                                <button @click.stop="openEdit(task)"
                                                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors"
                                                        title="Edit">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button @click.stop="archiveTask(task)"
                                                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors"
                                                        title="Arsipkan">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/>
                                                    </svg>
                                                </button>
                                                <button @click.stop="deleteTask(task)"
                                                        class="p-1.5 rounded-lg text-stone-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                                        title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <template v-else>
                                                <button @click.stop="restoreTask(task)"
                                                        class="text-xs px-2.5 py-1 rounded-lg text-[#73877C] bg-[#92A89C]/10 hover:bg-[#92A89C]/20 transition-colors">
                                                    {{ t('dashboard.checklist.task.restore') }}
                                                </button>
                                            </template>
                                        </div>

                                        <!-- Mobile: archived restore -->
                                        <div v-if="task.status === 'archived'" class="flex sm:hidden items-center ml-2">
                                            <button @click.stop="restoreTask(task)"
                                                    class="text-xs px-2.5 py-1 rounded-lg text-[#73877C] bg-[#92A89C]/10">
                                                {{ t('dashboard.checklist.task.restore') }}
                                            </button>
                                        </div>

                                        <!-- Mobile: swipe indicator dots -->
                                        <div v-if="task.status !== 'archived' && !bulkMode"
                                             class="sm:hidden flex-shrink-0 flex flex-col items-center justify-center gap-0.5 ml-1 opacity-20">
                                            <span class="w-1 h-1 rounded-full bg-stone-500"/>
                                            <span class="w-1 h-1 rounded-full bg-stone-500"/>
                                            <span class="w-1 h-1 rounded-full bg-stone-500"/>
                                        </div>
                                    </div>
                                </div>

                                <!-- ── Subtask expand panel ────────── -->
                                <div v-if="expandedTasks.has(task.id) && task.status !== 'archived'"
                                     class="relative z-10 bg-stone-50 border border-t-0 rounded-b-xl px-4 py-3 space-y-2">

                                    <div v-if="getSubtaskState(task.id).loading"
                                         class="flex items-center gap-2 text-xs text-stone-400 py-1">
                                        <div class="w-3 h-3 rounded-full border border-stone-300 border-t-[#92A89C] animate-spin"/>
                                        {{ t('dashboard.checklist.subtask.loading') }}
                                    </div>

                                    <template v-else>
                                        <!-- Subtask items -->
                                        <div v-for="sub in getSubtaskState(task.id).items" :key="sub.id"
                                             class="flex items-center gap-2 group">
                                            <button @click="toggleSubtask(task, sub)"
                                                    class="flex-shrink-0 w-4 h-4 rounded border-2 flex items-center justify-center transition-colors"
                                                    :class="sub.is_completed
                                                        ? 'border-green-500 bg-green-500'
                                                        : 'border-stone-300 hover:border-[#92A89C]'">
                                                <svg v-if="sub.is_completed" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            <span class="flex-1 text-xs text-stone-700"
                                                  :class="sub.is_completed ? 'line-through text-stone-400' : ''">
                                                {{ sub.title }}
                                            </span>
                                            <button @click="deleteSubtask(task, sub)"
                                                    class="opacity-0 group-hover:opacity-100 p-0.5 rounded text-stone-300 hover:text-red-400 transition-all">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Empty subtasks -->
                                        <p v-if="getSubtaskState(task.id).items.length === 0 && getSubtaskState(task.id).loaded"
                                           class="text-xs text-stone-400 italic">{{ t('dashboard.checklist.subtask.empty') }}</p>

                                        <!-- Add subtask input -->
                                        <div class="flex items-center gap-2 mt-1">
                                            <input
                                                v-model="getSubtaskState(task.id).newTitle"
                                                type="text"
                                                :placeholder="t('dashboard.checklist.subtask.placeholder')"
                                                class="flex-1 text-xs border border-stone-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-[#92A89C]/40"
                                                @keydown.enter="addSubtask(task)"
                                            />
                                            <button
                                                @click="addSubtask(task)"
                                                :disabled="!getSubtaskState(task.id).newTitle.trim() || getSubtaskState(task.id).saving"
                                                class="text-xs px-2 py-1.5 rounded-lg font-medium text-white disabled:opacity-40 transition-opacity"
                                                style="background-color: #92A89C">
                                                {{ t('dashboard.checklist.subtask.add') }}
                                            </button>
                                        </div>
                                    </template>
                                </div>

                            </div>

                                </div>
                                </Transition>
                            </div>
                        </div>

                        </template><!-- end timeline/list view -->
                    </div><!-- end main column -->

                    <!-- ── Right rail (desktop only) ───────────────── -->
                    <aside class="hidden lg:flex flex-col gap-4">
                        <ReminderRail :reminders="reminders" />
                        <TemplatePresetsRail :initialized="hasSystemTasks" @apply="applyStandardTemplate" @ai-generate="showAiModal = true" />
                        <PicSplitRail :bride-pct="picSplit.bridePct" :groom-pct="picSplit.groomPct" :bride-count="picSplit.brideCount" :groom-count="picSplit.groomCount" />
                    </aside>

                </div><!-- end grid -->
            </div><!-- end desktop wrapper -->

            <!-- ── Mobile layout branch ──────────────────────────── -->
            <template v-if="initialized && isMobileView">
                <MobileChecklist
                    :progress="summary.progress" :done="summary.done" :total="summary.total"
                    :urgent-count="urgentCount" :upcoming7d="summary.upcoming_7d" :days-until="daysUntil" :has-event-date="summary.has_event_date"
                    :chips="filterChips" :active-chip="activeChip" :buckets="mobileBuckets" :done-count="mobileDoneCount"
                    :has-system-tasks="hasSystemTasks"
                    @select="onChip" @open-filter="showMobileFilter = true" @add-task="openCreate"
                    @open-task="openMobileTask" @toggle="toggle" @show-done="onChip('done')"
                    @apply-template="applyStandardTemplate" />
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

        </template>

        <!-- ── Task Form Modal ────────────────────────────────────── -->
        <Transition name="fade">
            <div v-if="showForm"
                 class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4"
                 @click.self="closeForm">
                <div class="bg-white w-full max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl p-6 max-h-[90dvh] overflow-y-auto">
                    <h2 class="text-base font-semibold text-stone-800 mb-4">
                        {{ editingTask ? t('dashboard.checklist.form.titleEdit') : t('dashboard.checklist.form.titleAdd') }}
                    </h2>

                    <div class="space-y-3">
                        <!-- Title -->
                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.checklist.form.fieldName') }} <span class="text-red-400">*</span></label>
                            <input v-model="form.title" type="text" :placeholder="t('dashboard.checklist.form.namePlaceholder')"
                                   class="w-full border rounded-lg px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"
                                   :class="formError.title ? 'border-red-300' : 'border-stone-200'"/>
                            <p v-if="formError.title" class="text-xs text-red-500 mt-1">{{ formError.title }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <!-- Category -->
                            <div>
                                <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.checklist.form.fieldCategory') }} <span class="text-red-400">*</span></label>
                                <select v-if="!usingCustomCategory"
                                        v-model="form.category"
                                        class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                                    <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
                                </select>
                                <input v-else v-model="customCategory" type="text" :placeholder="t('dashboard.checklist.form.categoryCustomPlaceholder')"
                                       class="w-full border rounded-lg px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"
                                       :class="formError.category ? 'border-red-300' : 'border-stone-200'"/>
                                <button @click="usingCustomCategory = !usingCustomCategory"
                                        class="mt-1 text-xs text-[#73877C] hover:underline">
                                    {{ usingCustomCategory ? t('dashboard.checklist.form.switchToList') : t('dashboard.checklist.form.switchToCustom') }}
                                </button>
                                <p v-if="formError.category" class="text-xs text-red-500 mt-1">{{ formError.category }}</p>
                            </div>
                            <!-- Priority -->
                            <div>
                                <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.checklist.form.fieldPriority') }}</label>
                                <select v-model="form.priority"
                                        class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                                    <option value="low">{{ t('dashboard.checklist.priority.low') }}</option>
                                    <option value="medium">{{ t('dashboard.checklist.priority.medium') }}</option>
                                    <option value="high">{{ t('dashboard.checklist.priority.high') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Assignee -->
                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.checklist.form.fieldAssignee') }}</label>
                            <select v-model="form.assignee_type"
                                    class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                                <option value="">{{ t('dashboard.checklist.form.assigneeNone') }}</option>
                                <option v-for="o in ASSIGNEE_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
                            </select>
                            <input v-if="form.assignee_type === 'custom'"
                                   v-model="form.assignee_label"
                                   type="text"
                                   :placeholder="t('dashboard.checklist.form.assigneeCustomPlaceholder')"
                                   class="mt-1.5 w-full border border-stone-200 rounded-lg px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"/>
                        </div>

                        <!-- Vendor -->
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">{{ t('dashboard.checklist.form.vendor') }}</label>
                            <input v-model="form.vendor" type="text" maxlength="120"
                                   class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-brand-primary"
                                   :placeholder="t('dashboard.checklist.form.vendorPlaceholder')" />
                        </div>

                        <!-- Due date -->
                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.checklist.form.fieldDueDate') }}</label>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="openDatePicker('due')"
                                        class="flex-1 border border-stone-200 rounded-lg px-3 py-2 text-sm text-left transition-colors hover:border-[#92A89C]/50">
                                    <span v-if="form.due_date" class="text-stone-800">{{ calDisplayDate(form.due_date) }}</span>
                                    <span v-else class="text-stone-400">{{ t('dashboard.checklist.form.dueDatePlaceholder') }}</span>
                                </button>
                                <button v-if="form.due_date" type="button" @click="form.due_date = ''"
                                        class="p-2 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Reminder toggle -->
                        <div class="flex items-center justify-between py-1">
                            <div>
                                <p class="text-xs text-stone-700 font-medium">{{ t('dashboard.checklist.form.reminderLabel') }}</p>
                                <p class="text-xs text-stone-400">{{ t('dashboard.checklist.form.reminderSub') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select v-if="form.reminder_enabled"
                                        v-model="form.reminder_offset_days"
                                        class="text-xs border border-stone-200 rounded-lg px-2 py-1 bg-white text-stone-700 focus:outline-none">
                                    <option :value="1">{{ t('dashboard.checklist.form.reminderDaysBefore', { days: 1 }) }}</option>
                                    <option :value="7">{{ t('dashboard.checklist.form.reminderDaysBefore', { days: 7 }) }}</option>
                                    <option :value="14">{{ t('dashboard.checklist.form.reminderDaysBefore', { days: 14 }) }}</option>
                                    <option :value="30">{{ t('dashboard.checklist.form.reminderDaysBefore', { days: 30 }) }}</option>
                                </select>
                                <div class="relative w-9 h-5 flex-shrink-0 cursor-pointer"
                                     @click="form.reminder_enabled = !form.reminder_enabled">
                                    <div class="w-full h-full rounded-full transition-colors"
                                         :class="form.reminder_enabled ? 'bg-[#92A89C]' : 'bg-stone-200'"/>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                                         :class="form.reminder_enabled ? 'translate-x-4' : 'translate-x-0'"/>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.checklist.form.fieldNotes') }}</label>
                            <textarea v-model="form.description" rows="2" :placeholder="t('dashboard.checklist.form.notesPlaceholder')"
                                      class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm text-stone-800 resize-none focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"/>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button @click="closeForm"
                                class="flex-1 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50 transition-colors">
                            {{ t('dashboard.checklist.form.btnCancel') }}
                        </button>
                        <button @click="saveForm" :disabled="saving"
                                class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                                style="background-color: #92A89C">
                            {{ saving ? t('dashboard.checklist.form.btnSaving') : (editingTask ? t('dashboard.checklist.form.btnSave') : t('dashboard.checklist.form.btnAdd')) }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── AI Checklist Modal ────────────────────────────────── -->
        <AiChecklistModal v-if="showAiModal" @close="showAiModal = false" @applied="showAiModal = false; loadTasks(); loadSummary();" />

        <!-- ── Date Picker Modal ──────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDatePicker" class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeDatePicker"/>
                    <div class="relative w-full sm:max-w-sm bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden"
                         style="max-height: 92dvh; overflow-y: auto">

                        <div class="sm:hidden flex justify-center pt-3 pb-1">
                            <div class="w-10 h-1 rounded-full bg-stone-200"/>
                        </div>

                        <div class="flex items-center justify-between px-5 py-4">
                            <div>
                                <p class="text-sm font-bold text-stone-800">{{ t('dashboard.checklist.datePicker.title') }}</p>
                                <p v-if="currentPickerDate" class="text-xs text-[#73877C] mt-0.5">
                                    {{ calDisplayDate(currentPickerDate) }}
                                </p>
                            </div>
                            <button type="button" @click="closeDatePicker"
                                    class="w-8 h-8 rounded-full bg-stone-100 flex items-center justify-center hover:bg-stone-200 transition-colors">
                                <svg class="w-4 h-4 text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center justify-between px-5 pb-2">
                            <button type="button" @click="prevCalMonth"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-stone-500 hover:bg-stone-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <span class="text-sm font-semibold text-stone-700">{{ MONTHS_ID[calMonth] }} {{ calYear }}</span>
                            <button type="button" @click="nextCalMonth"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-stone-500 hover:bg-stone-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-7 px-4 pb-1">
                            <div v-for="d in DAYS_ID" :key="d"
                                 class="text-center text-xs font-semibold py-1"
                                 :class="d === 'Min' ? 'text-rose-400' : 'text-stone-400'">{{ d }}</div>
                        </div>

                        <div class="grid grid-cols-7 px-4 pb-3 gap-y-1">
                            <div v-for="(day, i) in calDays" :key="i"
                                 class="flex items-center justify-center aspect-square">
                                <button v-if="day" type="button" @click="pickDay(day)"
                                        :disabled="isCalPastDay(day)"
                                        class="w-9 h-9 rounded-full text-sm font-medium transition-all"
                                        :class="[
                                            isPickedDay(day)
                                                ? 'text-white font-bold shadow-sm'
                                                : isCalPastDay(day)
                                                    ? 'text-stone-200 cursor-not-allowed'
                                                    : 'text-stone-700 hover:bg-[#92A89C]/10 active:bg-[#92A89C]/20',
                                        ]"
                                        :style="isPickedDay(day) ? 'background-color:#92A89C' : ''">
                                    {{ day }}
                                </button>
                            </div>
                        </div>

                        <div class="px-5 pb-6 pt-2">
                            <button type="button" @click="closeDatePicker"
                                    :disabled="!currentPickerDate"
                                    class="w-full py-3.5 rounded-2xl text-sm font-bold text-white transition-all disabled:opacity-40"
                                    style="background-color:#92A89C">
                                <span v-if="currentPickerDate">{{ t('dashboard.checklist.datePicker.confirm', { date: calDisplayDate(currentPickerDate) }) }}</span>
                                <span v-else>{{ t('dashboard.checklist.datePicker.noDate') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </DashboardLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>
