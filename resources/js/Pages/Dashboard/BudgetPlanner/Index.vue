<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useLocale } from '@/Composables/useLocale';
import axios from 'axios';

// ── New widget imports ─────────────────────────────────────────────────────
import BudgetHero            from '@/Components/dashboard/budget/BudgetHero.vue';
import BudgetDonutCard       from '@/Components/dashboard/budget/BudgetDonutCard.vue';
import CategoryBarsCard      from '@/Components/dashboard/budget/CategoryBarsCard.vue';
import TransactionsTable     from '@/Components/dashboard/budget/TransactionsTable.vue';
import UpcomingPaymentsRail  from '@/Components/dashboard/budget/rail/UpcomingPaymentsRail.vue';
import AiInsightRail         from '@/Components/dashboard/budget/rail/AiInsightRail.vue';
import CoupleNotesRail       from '@/Components/dashboard/budget/rail/CoupleNotesRail.vue';
import WidgetIcon            from '@/Components/dashboard/WidgetIcon.vue';

const { t, locale } = useLocale();

const props = defineProps({
    budget:            Object,
    summary:           Object,
    categoryBreakdown: Array,
    items:             Array,
    categories:        Array,
    vendors:           { type: Array, default: () => [] },
    budgetInsights:    { type: Object, default: () => ({ enabled: true, insights: [], fresh: true }) },
    filters:           Object,
    budgetNotes:       { type: Array, default: () => [] },
});

// ─── View state ───────────────────────────────────────────────────────────────

const activeView       = ref('category');
const expandedCats     = ref(new Set());
const searchQuery      = ref(props.filters?.search ?? '');
const filterStatus     = ref(props.filters?.payment_status ?? 'all');
const filterCategory   = ref(props.filters?.category_id ?? '');
const sortBy           = ref(props.filters?.sort ?? 'newest');
const showFilterSheet  = ref(false);
const showAddItem      = ref(false);
const showEditItem     = ref(false);
const showManageCats   = ref(false);
const showSetBudget    = ref(false);
const showConfirmArchive = ref(false);
const selectedCatId    = ref(null); // pre-select category for add modal

const editingItem   = ref(null);
const archivingItem = ref(null);
const toast         = ref(null);
let toastTimer      = null;

// ─── Form state ───────────────────────────────────────────────────────────────

const budgetForm = ref({
    total_budget: props.budget?.total_budget ?? '',
    notes:        props.budget?.notes ?? '',
});

const blankItemForm = () => ({
    title:          '',
    category_id:    '',
    vendor_id:      '',
    vendor_name:    '',
    vendor_total_cost:  '',
    vendor_paid_amount: '',
    planned_amount: '',
    actual_amount:  '',
    dp_amount:      '',
    dp_paid:        false,
    final_amount:   '',
    final_paid:     false,
    due_date:       '',
    payment_status: 'unpaid',
    payment_date:   '',
    notes:          '',
    use_dp_tracking: false,
});

const itemForm   = ref(blankItemForm());
const itemErrors = ref({});
const categoryForm = ref({ name: '' });

// ─── Vendor linking ─────────────────────────────────────────────────────────

// Vendors selectable for the current form: any not already linked to ANOTHER
// item, plus the one this item is currently linked to (so edit keeps showing it).
const availableVendors = computed(() => {
    const editingId = editingItem.value?.id ?? null;
    return props.vendors.filter(v =>
        !v.linked_item_id || v.linked_item_id === editingId
    );
});

const selectedVendor = computed(() =>
    props.vendors.find(v => v.id === itemForm.value.vendor_id) ?? null
);

// Prefill the editable cost/paid fields from the vendor when one is picked, so
// the budget form is a true "second door" onto the same numbers.
watch(() => itemForm.value.vendor_id, (id, prev) => {
    if (id === prev) return;
    const v = props.vendors.find(x => x.id === id);
    if (v) {
        itemForm.value.vendor_total_cost  = v.total_cost || '';
        itemForm.value.vendor_paid_amount = v.paid_amount || '';
    } else {
        itemForm.value.vendor_total_cost  = '';
        itemForm.value.vendor_paid_amount = '';
    }
});

function vendorStatusLabel(v) {
    if (!v) return '';
    if (v.total_cost > 0 && v.paid_amount >= v.total_cost) return t('dashboard.budget.modal.addItem.vendorStatusPaid');
    if (v.paid_amount > 0) return t('dashboard.budget.modal.addItem.vendorStatusDp');
    return t('dashboard.budget.modal.addItem.vendorStatusBooked');
}

// ─── Category colors (brand palette) ─────────────────────────────────────────

const CATEGORY_COLORS = {
    'Venue':           '#C8A26B',
    'Catering':        '#B5C4A8',
    'Dekorasi':        '#D4A5A5',
    'Busana':          '#A8B8C4',
    'Dokumentasi':     '#C4B8A8',
    'Undangan':        '#B8C4A8',
    'Hiburan':         '#D4B8A8',
    'Transportasi':    '#A8C4B8',
    'Perhiasan':       '#C8B8A8',
    'Lainnya':         '#D4C4A8',
    'Makeup & Beauty': '#E8C4B8',
    'Souvenir':        '#B8D4C8',
    'Administrasi':    '#C8C4B8',
};

const FALLBACK_COLORS = [
    '#C8A26B','#B5C4A8','#D4A5A5','#A8B8C4',
    '#C4B8A8','#B8C4A8','#D4B8A8','#A8C4B8',
];

function catColor(cat, idx) {
    return cat.color || CATEGORY_COLORS[cat.name] || FALLBACK_COLORS[idx % FALLBACK_COLORS.length];
}

// ─── Status config ────────────────────────────────────────────────────────────

const statusConfig = computed(() => ({
    aman:      { label: t('dashboard.budget.status.aman'),    bg: 'bg-emerald-100', text: 'text-emerald-700', dot: '#4CAF50', bar: '#34D399' },
    mendekati: { label: t('dashboard.budget.status.mendekati'), bg: 'bg-[#92A89C]/20',   text: 'text-[#73877C]',   dot: '#92A89C', bar: '#92A89C' },
    melebihi:  { label: t('dashboard.budget.status.melebihi'),  bg: 'bg-rose-100',    text: 'text-rose-700',    dot: '#EF4444', bar: '#F87171' },
    no_data:   { label: t('dashboard.budget.status.noData'),   bg: 'bg-stone-100',   text: 'text-stone-500',   dot: '#9CA3AF', bar: '#D1D5DB' },
}));

function statusCfg(status) {
    return statusConfig.value[status] ?? statusConfig.value.no_data;
}

// ─── Computed ─────────────────────────────────────────────────────────────────

const hasBudget      = computed(() => props.summary?.has_budget);
const isFirstTime    = computed(() =>
    !props.summary?.has_budget &&
    (props.items ?? []).length === 0 &&
    (props.categoryBreakdown ?? []).every(c => c.items_count === 0)
);

const progressPct   = computed(() => props.summary?.usage_percentage ?? 0);
const progressColor = computed(() => {
    if (props.summary?.is_total_overbudget) return '#F87171';
    if ((progressPct.value ?? 0) >= 80) return '#92A89C';
    return '#92A89C';
});

const paymentStatusOptions = computed(() => [
    { value: 'unpaid', label: t('dashboard.budget.payment.unpaid') },
    { value: 'dp',     label: t('dashboard.budget.payment.dp') },
    { value: 'paid',   label: t('dashboard.budget.payment.paid') },
]);

const paymentBadge = {
    unpaid: 'bg-stone-100 text-stone-600',
    dp:     'bg-[#92A89C]/20 text-[#73877C]',
    paid:   'bg-emerald-100 text-emerald-700',
};

const paymentLabel = computed(() => ({
    unpaid: t('dashboard.budget.payment.unpaid'),
    dp:     t('dashboard.budget.payment.dp'),
    paid:   t('dashboard.budget.payment.paid'),
}));

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatRupiah(val) {
    if (val === null || val === undefined || val === '') return '';
    const n = parseInt(String(val).replace(/\D/g, ''), 10) || 0;
    return 'Rp ' + n.toLocaleString('id-ID');
}

function parseRupiah(val) {
    if (!val) return null;
    const n = parseInt(String(val).replace(/\D/g, ''), 10);
    return isNaN(n) ? null : n;
}

function compactAmount(val) {
    if (val === null || val === undefined) return '—';
    if (val >= 1_000_000_000) return 'Rp ' + (val / 1_000_000_000).toFixed(1).replace('.0','') + 'M';
    if (val >= 1_000_000)     return 'Rp ' + (val / 1_000_000).toFixed(1).replace('.0','') + 'jt';
    if (val >= 1_000)         return 'Rp ' + (val / 1_000).toFixed(0) + 'rb';
    return 'Rp ' + val;
}

function showToast(message, type = 'success') {
    clearTimeout(toastTimer);
    toast.value = { message, type };
    toastTimer = setTimeout(() => { toast.value = null; }, 4000);
}

// ─── Category expand ──────────────────────────────────────────────────────────

function toggleCat(id) {
    const s = new Set(expandedCats.value);
    s.has(id) ? s.delete(id) : s.add(id);
    expandedCats.value = s;
}

// ─── Search / filter reload ───────────────────────────────────────────────────

let searchDebounce = null;
watch(searchQuery, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => reloadItems(), 400);
});

function applyFilters() {
    showFilterSheet.value = false;
    reloadItems();
}

function reloadItems() {
    router.get(
        route('dashboard.budget-planner.index'),
        {
            search:         searchQuery.value || undefined,
            category_id:    filterCategory.value || undefined,
            payment_status: filterStatus.value !== 'all' ? filterStatus.value : undefined,
            sort:           sortBy.value !== 'newest' ? sortBy.value : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
}

function clearFilters() {
    searchQuery.value    = '';
    filterStatus.value   = 'all';
    filterCategory.value = '';
    sortBy.value         = 'newest';
    showFilterSheet.value = false;
    reloadItems();
}

// ─── Budget update ────────────────────────────────────────────────────────────

async function saveBudget() {
    const raw = parseRupiah(budgetForm.value.total_budget);
    try {
        await window.axios.patch(route('dashboard.budget-planner.budget.update'), {
            total_budget: raw,
            notes:        budgetForm.value.notes,
        });
        showSetBudget.value = false;
        showToast(t('dashboard.budget.toasts.budgetSaved'));
        router.reload({ preserveScroll: true });
    } catch {
        showToast(t('dashboard.budget.toasts.budgetError'), 'error');
    }
}

function openSetBudget() {
    budgetForm.value.total_budget = props.budget?.total_budget ?? '';
    budgetForm.value.notes        = props.budget?.notes ?? '';
    showSetBudget.value           = true;
}

// ─── Item CRUD ────────────────────────────────────────────────────────────────

function openAddItem(catId = null) {
    const form = blankItemForm();
    if (catId) form.category_id = catId;
    itemForm.value   = form;
    itemErrors.value = {};
    editingItem.value = null;
    showAddItem.value  = true;
}

function openEditItem(item) {
    editingItem.value = item;
    itemForm.value = {
        title:           item.title,
        category_id:     item.category_id ?? item.category?.id ?? '',
        vendor_id:       item.vendor_id ?? '',
        vendor_name:     item.vendor_name ?? '',
        planned_amount:  item.planned_amount ?? '',
        actual_amount:   item.actual_amount ?? '',
        dp_amount:       item.dp_amount ?? '',
        dp_paid:         item.dp_paid ?? false,
        final_amount:    item.final_amount ?? '',
        final_paid:      item.final_paid ?? false,
        due_date:        item.due_date ?? '',
        payment_status:  item.payment_status ?? 'unpaid',
        payment_date:    item.payment_date ?? '',
        notes:           item.notes ?? '',
        use_dp_tracking: !!(item.dp_amount || item.final_amount),
    };
    itemErrors.value = {};
    showEditItem.value = true;
}

async function saveItem() {
    itemErrors.value = {};
    const f = itemForm.value;

    const payload = {
        title:          f.title,
        category_id:    f.category_id,
        vendor_id:      f.vendor_id || null,
        vendor_name:    f.vendor_id ? null : (f.vendor_name || null),
        notes:          f.notes || null,
        planned_amount: parseRupiah(f.planned_amount) ?? 0,
        due_date:       f.due_date || null,
    };

    // Linked to a vendor: cost & payment live on the vendor. Send them as
    // write-through fields; the server saves them to the vendor, not the item.
    if (f.vendor_id) {
        payload.vendor_total_cost  = parseRupiah(f.vendor_total_cost) ?? 0;
        payload.vendor_paid_amount = parseRupiah(f.vendor_paid_amount) ?? 0;
        payload.actual_amount = null;
        payload.dp_amount     = null;
        payload.dp_paid       = false;
        payload.final_amount  = null;
        payload.final_paid    = false;
    } else if (f.use_dp_tracking) {
        payload.dp_amount    = parseRupiah(f.dp_amount) ?? null;
        payload.dp_paid      = f.dp_paid;
        payload.final_amount = parseRupiah(f.final_amount) ?? null;
        payload.final_paid   = f.final_paid;
        payload.actual_amount = null;
        // sync payment_status from dp/final
        if (f.final_paid)     payload.payment_status = 'paid';
        else if (f.dp_paid)   payload.payment_status = 'dp';
        else                  payload.payment_status = 'unpaid';
    } else {
        payload.actual_amount  = f.actual_amount !== '' ? (parseRupiah(f.actual_amount) ?? null) : null;
        payload.payment_status = f.payment_status;
        payload.payment_date   = f.payment_date || null;
        payload.dp_amount      = null;
        payload.final_amount   = null;
        payload.dp_paid        = false;
        payload.final_paid     = false;
    }

    try {
        if (editingItem.value) {
            await window.axios.patch(route('dashboard.budget-planner.items.update', editingItem.value.id), payload);
            showToast(t('dashboard.budget.toasts.itemUpdated'));
            showEditItem.value = false;
        } else {
            await window.axios.post(route('dashboard.budget-planner.items.store'), payload);
            showToast(t('dashboard.budget.toasts.itemSaved'));
            showAddItem.value = false;
        }
        router.reload({ preserveScroll: true });
    } catch (err) {
        if (err.response?.status === 422) {
            itemErrors.value = err.response.data.errors ?? {};
        } else {
            showToast(t('dashboard.budget.toasts.itemError'), 'error');
        }
    }
}

async function togglePayment(item, field) {
    const newVal = !item[field];
    try {
        await window.axios.patch(route('dashboard.budget-planner.items.payment', item.id), {
            [field]: newVal,
        });
        showToast(newVal ? t('dashboard.budget.toasts.paymentMarkedPaid') : t('dashboard.budget.toasts.paymentCancelled'));
        router.reload({ preserveScroll: true });
    } catch {
        showToast(t('dashboard.budget.toasts.paymentError'), 'error');
    }
}

function confirmArchiveItem(item) {
    archivingItem.value      = item;
    showConfirmArchive.value = true;
}

async function archiveItem() {
    if (!archivingItem.value) return;
    try {
        await window.axios.delete(route('dashboard.budget-planner.items.destroy', archivingItem.value.id));
        showConfirmArchive.value = false;
        archivingItem.value      = null;
        showToast(t('dashboard.budget.toasts.itemArchived'));
        router.reload({ preserveScroll: true });
    } catch {
        showToast(t('dashboard.budget.toasts.itemArchiveError'), 'error');
    }
}

// ─── Category management ──────────────────────────────────────────────────────

async function addCategory() {
    if (!categoryForm.value.name.trim()) return;
    try {
        await window.axios.post(route('dashboard.budget-planner.categories.store'), { name: categoryForm.value.name });
        categoryForm.value.name = '';
        showToast(t('dashboard.budget.toasts.categoryAdded'));
        router.reload({ preserveScroll: true });
    } catch {
        showToast(t('dashboard.budget.toasts.categoryAddError'), 'error');
    }
}

async function archiveCategory(cat) {
    try {
        await window.axios.delete(route('dashboard.budget-planner.categories.destroy', cat.id));
        showToast(t('dashboard.budget.toasts.categoryArchived'));
        router.reload({ preserveScroll: true });
    } catch (err) {
        showToast(err.response?.data?.message ?? t('dashboard.budget.toasts.categoryArchiveError'), 'error');
    }
}

// ─── Date Picker ─────────────────────────────────────────────────────────────

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

const showDatePicker  = ref(false);
const datePickerMode  = ref('');
const calToday        = new Date();
const calYear         = ref(calToday.getFullYear());
const calMonth        = ref(calToday.getMonth());

function openDatePicker(mode) {
    datePickerMode.value = mode;
    const val = mode === 'due_date' ? itemForm.value.due_date : itemForm.value.payment_date;
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
    if (datePickerMode.value === 'due_date') itemForm.value.due_date = val;
    else itemForm.value.payment_date = val;
}
function isPickedDay(day) {
    if (!day) return false;
    const val = datePickerMode.value === 'due_date' ? itemForm.value.due_date : itemForm.value.payment_date;
    if (!val) return false;
    const [y, m, d] = val.split('-').map(Number);
    return y === calYear.value && m === calMonth.value + 1 && d === day;
}
function calDisplayDate(dateStr) {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString(locale.value === 'en' ? 'en-US' : 'id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}
const currentPickerDate = computed(() =>
    datePickerMode.value === 'due_date' ? itemForm.value.due_date : itemForm.value.payment_date
);

// ─── Notes + Export (new) ─────────────────────────────────────────────────────

const notes = ref([...(props.budgetNotes ?? [])]);

async function postNote(body) {
    const { data } = await axios.post(route('dashboard.budget-planner.notes.store'), { body });
    notes.value.unshift(data);
}

async function deleteNote(id) {
    await axios.delete(route('dashboard.budget-planner.notes.destroy', id));
    notes.value = notes.value.filter(n => n.id !== id);
}

function exportCsv() { window.location.href = route('dashboard.budget-planner.export'); }

const upcomingPayments = computed(() =>
    (props.items ?? []).filter(it => it.payment_status !== 'paid' && it.due_date).slice(0, 4)
);
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h1 class="text-base font-semibold text-stone-800 truncate">{{ t('dashboard.budget.header.title') }}</h1>
        </template>

        <div class="w-full pb-28">

            <!-- ── Toast ──────────────────────────────────────────────────── -->
            <Transition name="slide-down">
                <div
                    v-if="toast"
                    :class="[
                        'fixed top-4 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-medium',
                        toast.type === 'error'
                            ? 'bg-rose-50 text-rose-700 border border-rose-100'
                            : 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                    ]"
                    role="alert" aria-live="polite"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path v-if="toast.type !== 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    {{ toast.message }}
                </div>
            </Transition>

            <!-- ── Onboarding Card ─────────────────────────────────────────── -->
            <div v-if="isFirstTime" class="bg-white border border-[#B8C7BF]/50 rounded-2xl p-6 shadow-sm mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                         style="background-color: #FFF3E0">
                        <svg class="w-6 h-6" style="color: #92A89C" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-stone-800">{{ t('dashboard.budget.onboarding.title') }}</h3>
                        <p class="text-xs text-stone-500 mt-0.5">{{ t('dashboard.budget.onboarding.subtitle') }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button @click="openAddItem()"
                            class="px-3 py-2 text-xs font-medium text-stone-600 border border-stone-200 rounded-xl hover:bg-stone-50 transition-colors">
                            {{ t('dashboard.budget.onboarding.addItemDirect') }}
                        </button>
                        <button @click="openSetBudget"
                            class="px-4 py-2 text-xs font-semibold text-white rounded-xl transition-opacity hover:opacity-90"
                            style="background-color: #92A89C">
                            {{ t('dashboard.budget.onboarding.setBudget') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── No budget notice (has items but no total budget) ────────── -->
            <div v-else-if="!hasBudget && (items?.length > 0 || categoryBreakdown?.some(c => c.items_count > 0))"
                 class="flex items-center gap-3 bg-[#92A89C]/10 border border-[#B8C7BF]/50 rounded-xl px-4 py-3 mb-4 text-xs">
                <svg class="w-4 h-4 text-[#92A89C] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <span class="text-[#73877C]">{{ t('dashboard.budget.noBudgetNotice.message') }}</span>
                <button @click="openSetBudget" class="ml-auto font-semibold text-[#73877C] underline whitespace-nowrap">{{ t('dashboard.budget.noBudgetNotice.action') }}</button>
            </div>

            <!-- ══════════════ NEW MOCKUP COMPOSITION ════════════════════════ -->
            <div class="w-full">
                <!-- Page header row -->
                <div class="flex items-end justify-between gap-3 mb-5 flex-wrap">
                    <div>
                        <h1 class="font-cormorant font-medium text-[30px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.pageTitle') }}</h1>
                        <p class="text-[13px] max-w-xl" style="color:#6C7A75;">{{ t('dashboard.budget.pageSub') }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" @click="exportCsv" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
                            <WidgetIcon name="download" :size="13" stroke="#4A5A4C" /> {{ t('dashboard.budget.export') }}
                        </button>
                        <button type="button" @click="showManageCats = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
                            {{ t('dashboard.budget.header.manageCategories') }}
                        </button>
                        <button type="button" @click="showSetBudget = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
                            {{ t('dashboard.budget.setBudget') }}
                        </button>
                        <button type="button" @click="showAddItem = true" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[12px] font-semibold text-white" style="background:#1F2A2E;">
                            <WidgetIcon name="plus" :size="13" stroke="#fff" /> {{ t('dashboard.budget.addExpense') }}
                        </button>
                    </div>
                </div>

                <!-- Two-column: content (left) + rail (right, full height) -->
                <div class="grid gap-5 lg:grid-cols-[1fr_320px] items-start">
                    <!-- LEFT: hero + donut/bars + transactions -->
                    <div class="min-w-0">
                        <BudgetHero :summary="summary" />

                        <div class="grid gap-5 lg:grid-cols-[300px_1fr] mt-6 mb-7">
                            <BudgetDonutCard :categories="categoryBreakdown" />
                            <CategoryBarsCard :categories="categoryBreakdown" />
                        </div>

                        <!-- Transactions header with search/filter/sort -->
                        <div class="flex items-end justify-between gap-3 mb-3 flex-wrap">
                            <div>
                                <h2 class="font-cormorant font-medium text-[24px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.transactions.title') }}</h2>
                                <p class="text-[12.5px]" style="color:#6C7A75;">{{ t('dashboard.budget.transactions.sub', { count: items?.length ?? 0 }) }}</p>
                            </div>
                            <!-- Search + Filter controls (reload logic preserved) -->
                            <div class="flex gap-2 items-center">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input v-model="searchQuery" type="search" :placeholder="t('dashboard.budget.itemList.searchPlaceholder')"
                                        class="pl-9 pr-3 py-2 text-sm bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent w-44"
                                        style="--tw-ring-color: #92A89C" />
                                </div>
                                <button @click="showFilterSheet = true"
                                    :class="['flex items-center gap-1.5 px-3 py-2 text-sm border rounded-xl transition-colors',
                                        (filterStatus !== 'all' || filterCategory || sortBy !== 'newest')
                                            ? 'bg-[#92A89C]/10 border-[#B8C7BF] text-[#73877C] font-medium'
                                            : 'bg-white border-stone-200 text-stone-600 hover:bg-stone-50']"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                                    </svg>
                                    {{ t('dashboard.budget.itemList.filter') }}
                                </button>
                            </div>
                        </div>

                        <TransactionsTable :items="items" @edit="openEditItem" />
                    </div>

                    <!-- RIGHT: rail (full height) -->
                    <aside class="flex flex-col gap-4">
                        <UpcomingPaymentsRail :payments="upcomingPayments" />
                        <AiInsightRail :initial="budgetInsights" />
                        <CoupleNotesRail :notes="notes" @post="postNote" @delete="deleteNote" />
                    </aside>
                </div>
            </div>
            <!-- ════════════════ END MOCKUP ═══════════════════════════════════ -->

        </div>

        <!-- ── Mobile FAB ──────────────────────────────────────────────────── -->
        <div class="fixed bottom-20 right-4 sm:hidden z-20">
            <button @click="openAddItem()"
                class="flex items-center gap-2 px-5 py-3 text-white text-sm font-semibold rounded-2xl shadow-lg transition-opacity hover:opacity-90"
                style="background-color: #92A89C">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                {{ t('dashboard.budget.header.addItem') }}
            </button>
        </div>

        <!-- ════════════════ MODALS ════════════════════════════════════════ -->

        <!-- ── Set Budget Modal ────────────────────────────────────────────── -->
        <Transition name="modal">
            <div v-if="showSetBudget" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="showSetBudget = false"/>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-base font-semibold text-stone-800 mb-4">{{ t('dashboard.budget.modal.setBudget.title') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.setBudget.labelBudget') }}</label>
                            <input
                                :value="formatRupiah(budgetForm.total_budget)"
                                @input="budgetForm.total_budget = $event.target.value.replace(/\D/g, '')"
                                type="text" inputmode="numeric" placeholder="Rp 0"
                                class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                                style="--tw-ring-color: #92A89C" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.setBudget.labelNotes') }}</label>
                            <textarea v-model="budgetForm.notes" rows="2"
                                class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent resize-none"
                                style="--tw-ring-color: #92A89C" />
                        </div>
                    </div>
                    <div class="flex gap-2 mt-5">
                        <button @click="showSetBudget = false" class="flex-1 py-2.5 text-sm text-stone-600 border border-stone-200 rounded-xl hover:bg-stone-50 transition-colors">{{ t('dashboard.budget.modal.setBudget.cancel') }}</button>
                        <button @click="saveBudget" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90" style="background-color: #92A89C">{{ t('dashboard.budget.modal.setBudget.save') }}</button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Add/Edit Item Modal ─────────────────────────────────────────── -->
        <Transition name="modal">
            <div v-if="showAddItem || showEditItem" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="showAddItem = showEditItem = false"/>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                        <h3 class="text-base font-semibold text-stone-800">{{ editingItem ? t('dashboard.budget.modal.addItem.titleEdit') : t('dashboard.budget.modal.addItem.titleAdd') }}</h3>
                        <button @click="showAddItem = showEditItem = false" class="p-1 text-stone-400 hover:text-stone-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                        <!-- Nama item -->
                        <div>
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelName') }} <span class="text-rose-400">*</span></label>
                            <input v-model="itemForm.title" type="text" :placeholder="t('dashboard.budget.modal.addItem.namePlaceholder')"
                                class="w-full px-3 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                                :class="itemErrors.title ? 'border-rose-300' : 'border-stone-200'"
                                style="--tw-ring-color: #92A89C" />
                            <p v-if="itemErrors.title" class="mt-1 text-xs text-rose-500">{{ itemErrors.title[0] }}</p>
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelCategory') }} <span class="text-rose-400">*</span></label>
                            <select v-model="itemForm.category_id"
                                class="w-full px-3 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent bg-white"
                                :class="itemErrors.category_id ? 'border-rose-300' : 'border-stone-200'"
                                style="--tw-ring-color: #92A89C">
                                <option value="" disabled>{{ t('dashboard.budget.modal.addItem.selectCategory') }}</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="itemErrors.category_id" class="mt-1 text-xs text-rose-500">{{ itemErrors.category_id[0] }}</p>
                        </div>

                        <!-- Hubungkan ke vendor (sumber harga & pembayaran) -->
                        <div v-if="vendors.length">
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelLinkVendor') }}</label>
                            <select v-model="itemForm.vendor_id"
                                class="w-full px-3 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent bg-white"
                                :class="itemErrors.vendor_id ? 'border-rose-300' : 'border-stone-200'"
                                style="--tw-ring-color: #92A89C">
                                <option value="">{{ t('dashboard.budget.modal.addItem.vendorNone') }}</option>
                                <option v-for="v in availableVendors" :key="v.id" :value="v.id">
                                    {{ v.name }} · {{ v.category_label }}
                                </option>
                            </select>
                            <p v-if="itemErrors.vendor_id" class="mt-1 text-xs text-rose-500">{{ itemErrors.vendor_id[0] }}</p>
                            <p v-else class="mt-1 text-xs text-stone-400">{{ t('dashboard.budget.modal.addItem.linkVendorHint') }}</p>
                        </div>

                        <!-- Planned amount (target — tetap bisa diisi walau ter-link) -->
                        <div>
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelPlannedAmount') }}</label>
                            <input
                                :value="itemForm.planned_amount ? formatRupiah(itemForm.planned_amount) : ''"
                                @input="itemForm.planned_amount = $event.target.value.replace(/\D/g, '')"
                                type="text" inputmode="numeric" placeholder="Rp 0"
                                class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                                style="--tw-ring-color: #92A89C" />
                        </div>

                        <!-- Ter-link vendor: edit biaya & bayar di sini, tersimpan ke vendor (write-through) -->
                        <div v-if="selectedVendor" class="rounded-xl border border-[#92A89C]/30 bg-[#92A89C]/5 p-3 space-y-3">
                            <div class="flex items-center gap-1.5 text-xs font-medium text-[#5e6f64]">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m6.828-6.828a4 4 0 015.656 5.656l-1.5 1.5"/>
                                </svg>
                                {{ t('dashboard.budget.modal.addItem.vendorLinkedNotice') }}
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.vendorTotalCost') }}</label>
                                <input
                                    :value="itemForm.vendor_total_cost !== '' ? formatRupiah(itemForm.vendor_total_cost) : ''"
                                    @input="itemForm.vendor_total_cost = $event.target.value.replace(/\D/g, '')"
                                    type="text" inputmode="numeric" placeholder="Rp 0"
                                    class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent bg-white"
                                    style="--tw-ring-color: #92A89C" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.vendorPaid') }}</label>
                                <input
                                    :value="itemForm.vendor_paid_amount !== '' ? formatRupiah(itemForm.vendor_paid_amount) : ''"
                                    @input="itemForm.vendor_paid_amount = $event.target.value.replace(/\D/g, '')"
                                    type="text" inputmode="numeric" placeholder="Rp 0"
                                    class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent bg-white"
                                    style="--tw-ring-color: #92A89C" />
                            </div>
                            <p class="text-xs text-stone-400">{{ t('dashboard.budget.modal.addItem.writeThroughHint') }}</p>
                            <a :href="route('dashboard.vendor.index')" class="inline-flex items-center gap-1 text-xs text-[#5e6f64] underline hover:opacity-80">
                                {{ t('dashboard.budget.modal.addItem.openVendorTab') }}
                            </a>
                        </div>

                        <!-- Payment tracking mode toggle (disembunyikan saat ter-link vendor) -->
                        <template v-if="!itemForm.vendor_id">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-stone-600">{{ t('dashboard.budget.modal.addItem.labelDpTracking') }}</label>
                            <button
                                @click="itemForm.use_dp_tracking = !itemForm.use_dp_tracking"
                                :class="['relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none',
                                    itemForm.use_dp_tracking ? 'bg-[#92A89C]' : 'bg-stone-200']"
                                role="switch" :aria-checked="itemForm.use_dp_tracking"
                            >
                                <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200',
                                    itemForm.use_dp_tracking ? 'translate-x-4' : 'translate-x-0']"/>
                            </button>
                        </div>

                        <!-- Simple payment mode -->
                        <template v-if="!itemForm.use_dp_tracking">
                            <div>
                                <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelAmountPaid') }}</label>
                                <input
                                    :value="itemForm.actual_amount !== '' ? formatRupiah(itemForm.actual_amount) : ''"
                                    @input="itemForm.actual_amount = $event.target.value.replace(/\D/g, '')"
                                    type="text" inputmode="numeric" :placeholder="t('dashboard.budget.modal.addItem.amountPaidPlaceholder')"
                                    class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                                    style="--tw-ring-color: #92A89C" />
                                <p class="mt-1 text-xs text-stone-400">{{ t('dashboard.budget.modal.addItem.amountPaidHint') }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelPaymentStatus') }}</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in paymentStatusOptions" :key="opt.value"
                                        @click="itemForm.payment_status = opt.value"
                                        :class="['flex-1 py-2 text-xs font-medium rounded-xl border transition-colors',
                                            itemForm.payment_status === opt.value ? 'border-transparent text-white' : 'border-stone-200 text-stone-600 hover:bg-stone-50']"
                                        :style="itemForm.payment_status === opt.value ? 'background-color: #92A89C' : ''">
                                        {{ opt.label }}
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelPaymentDate') }}</label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="openDatePicker('payment_date')"
                                            class="flex-1 border border-stone-200 rounded-xl px-3 py-2.5 text-sm text-left transition-colors hover:border-[#92A89C]/50">
                                        <span v-if="itemForm.payment_date" class="text-stone-800">{{ calDisplayDate(itemForm.payment_date) }}</span>
                                        <span v-else class="text-stone-400">{{ t('dashboard.budget.modal.addItem.pickDate') }}</span>
                                    </button>
                                    <button v-if="itemForm.payment_date" type="button" @click="itemForm.payment_date = ''"
                                            class="p-2 rounded-xl text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- DP + Pelunasan tracking mode -->
                        <template v-else>
                            <div class="space-y-3 bg-stone-50 rounded-xl p-3">
                                <p class="text-xs font-medium text-stone-500">{{ t('dashboard.budget.modal.addItem.dpSection') }}</p>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <input
                                            :value="itemForm.dp_amount !== '' ? formatRupiah(itemForm.dp_amount) : ''"
                                            @input="itemForm.dp_amount = $event.target.value.replace(/\D/g, '')"
                                            type="text" inputmode="numeric" :placeholder="t('dashboard.budget.modal.addItem.dpPlaceholder')"
                                            class="w-full px-3 py-2 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent bg-white"
                                            style="--tw-ring-color: #92A89C" />
                                    </div>
                                    <button
                                        @click="itemForm.dp_paid = !itemForm.dp_paid"
                                        :class="['flex-shrink-0 flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-xl border transition-colors',
                                            itemForm.dp_paid ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'border-stone-200 text-stone-500 bg-white hover:bg-stone-50']">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path v-if="itemForm.dp_paid" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        {{ itemForm.dp_paid ? t('dashboard.budget.modal.addItem.paid') : t('dashboard.budget.modal.addItem.notPaid') }}
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-3 bg-stone-50 rounded-xl p-3">
                                <p class="text-xs font-medium text-stone-500">{{ t('dashboard.budget.modal.addItem.settlementSection') }}</p>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <input
                                            :value="itemForm.final_amount !== '' ? formatRupiah(itemForm.final_amount) : ''"
                                            @input="itemForm.final_amount = $event.target.value.replace(/\D/g, '')"
                                            type="text" inputmode="numeric" :placeholder="t('dashboard.budget.modal.addItem.dpPlaceholder')"
                                            class="w-full px-3 py-2 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent bg-white"
                                            style="--tw-ring-color: #92A89C" />
                                    </div>
                                    <button
                                        @click="itemForm.final_paid = !itemForm.final_paid"
                                        :class="['flex-shrink-0 flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-xl border transition-colors',
                                            itemForm.final_paid ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'border-stone-200 text-stone-500 bg-white hover:bg-stone-50']">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path v-if="itemForm.final_paid" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        {{ itemForm.final_paid ? t('dashboard.budget.modal.addItem.paid') : t('dashboard.budget.modal.addItem.notPaid') }}
                                    </button>
                                </div>
                            </div>
                        </template>
                        </template>

                        <!-- Jatuh tempo -->
                        <div>
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelDueDate') }}</label>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="openDatePicker('due_date')"
                                        class="flex-1 border border-stone-200 rounded-xl px-3 py-2.5 text-sm text-left transition-colors hover:border-[#92A89C]/50">
                                    <span v-if="itemForm.due_date" class="text-stone-800">{{ calDisplayDate(itemForm.due_date) }}</span>
                                    <span v-else class="text-stone-400">{{ t('dashboard.budget.modal.addItem.pickDate') }}</span>
                                </button>
                                <button v-if="itemForm.due_date" type="button" @click="itemForm.due_date = ''"
                                        class="p-2 rounded-xl text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Vendor name bebas (hanya untuk item tanpa link vendor) -->
                        <div v-if="!itemForm.vendor_id">
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelVendor') }}</label>
                            <input v-model="itemForm.vendor_name" type="text" :placeholder="t('dashboard.budget.modal.addItem.vendorPlaceholder')"
                                class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                                style="--tw-ring-color: #92A89C" />
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.budget.modal.addItem.labelNotes') }}</label>
                            <textarea v-model="itemForm.notes" rows="2" :placeholder="t('dashboard.budget.modal.addItem.notesPlaceholder')"
                                class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent resize-none"
                                style="--tw-ring-color: #92A89C" />
                        </div>
                    </div>

                    <div class="px-5 py-4 border-t border-stone-100 flex gap-2">
                        <button v-if="showEditItem && editingItem" @click="showEditItem = false; confirmArchiveItem(editingItem)"
                            class="px-4 py-2.5 text-sm font-medium text-red-500 border border-red-100 rounded-xl hover:bg-red-50 transition-colors">{{ t('common.delete') }}</button>
                        <button @click="showAddItem = showEditItem = false"
                            class="flex-1 py-2.5 text-sm text-stone-600 border border-stone-200 rounded-xl hover:bg-stone-50 transition-colors">{{ t('dashboard.budget.modal.addItem.cancel') }}</button>
                        <button @click="saveItem"
                            class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90"
                            style="background-color: #92A89C">{{ t('dashboard.budget.modal.addItem.save') }}</button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Filter Sheet ────────────────────────────────────────────────── -->
        <Transition name="slide-up">
            <div v-if="showFilterSheet" class="fixed inset-0 z-50 flex items-end justify-center">
                <div class="absolute inset-0 bg-black/40" @click="showFilterSheet = false"/>
                <div class="relative bg-white rounded-t-3xl shadow-xl w-full max-w-lg p-5 pb-8">
                    <div class="w-10 h-1 bg-stone-200 rounded-full mx-auto mb-4"/>
                    <h3 class="text-base font-semibold text-stone-800 mb-4">{{ t('dashboard.budget.filter.title') }}</h3>

                    <div class="mb-4">
                        <p class="text-xs font-medium text-stone-500 mb-2">{{ t('dashboard.budget.filter.paymentStatus') }}</p>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="opt in [{ value: 'all', label: t('dashboard.budget.payment.allStatus') }, ...paymentStatusOptions]" :key="opt.value"
                                @click="filterStatus = opt.value"
                                :class="['px-3 py-1.5 text-xs font-medium rounded-xl border transition-colors',
                                    filterStatus === opt.value ? 'border-transparent text-white' : 'border-stone-200 text-stone-600 hover:bg-stone-50']"
                                :style="filterStatus === opt.value ? 'background-color: #92A89C' : ''">
                                {{ opt.label }}
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-xs font-medium text-stone-500 mb-2">{{ t('dashboard.budget.filter.category') }}</p>
                        <select v-model="filterCategory" class="w-full px-3 py-2 text-sm border border-stone-200 rounded-xl focus:outline-none bg-white">
                            <option value="">{{ t('dashboard.budget.filter.allCategories') }}</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <p class="text-xs font-medium text-stone-500 mb-2">{{ t('dashboard.budget.filter.sort') }}</p>
                        <select v-model="sortBy" class="w-full px-3 py-2 text-sm border border-stone-200 rounded-xl focus:outline-none bg-white">
                            <option value="newest">{{ t('dashboard.budget.filter.sortNewest') }}</option>
                            <option value="amount_desc">{{ t('dashboard.budget.filter.sortAmountDesc') }}</option>
                            <option value="amount_asc">{{ t('dashboard.budget.filter.sortAmountAsc') }}</option>
                            <option value="date_desc">{{ t('dashboard.budget.filter.sortDateDesc') }}</option>
                            <option value="category">{{ t('dashboard.budget.filter.sortCategory') }}</option>
                            <option value="payment_status">{{ t('dashboard.budget.filter.sortPaymentStatus') }}</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button @click="clearFilters" class="flex-1 py-2.5 text-sm text-stone-600 border border-stone-200 rounded-xl hover:bg-stone-50 transition-colors">{{ t('dashboard.budget.filter.reset') }}</button>
                        <button @click="applyFilters" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90" style="background-color: #92A89C">{{ t('dashboard.budget.filter.apply') }}</button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Manage Categories Modal ─────────────────────────────────────── -->
        <Transition name="modal">
            <div v-if="showManageCats" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="showManageCats = false"/>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[80vh] flex flex-col">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                        <h3 class="text-base font-semibold text-stone-800">{{ t('dashboard.budget.modal.manageCategories.title') }}</h3>
                        <button @click="showManageCats = false" class="p-1 text-stone-400 hover:text-stone-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto px-5 py-4">
                        <div class="flex gap-2 mb-4">
                            <input v-model="categoryForm.name" type="text" :placeholder="t('dashboard.budget.modal.manageCategories.newCategoryPlaceholder')"
                                @keyup.enter="addCategory"
                                class="flex-1 px-3 py-2 text-sm border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                                style="--tw-ring-color: #92A89C" />
                            <button @click="addCategory"
                                class="px-4 py-2 text-sm font-medium text-white rounded-xl transition-opacity hover:opacity-90"
                                style="background-color: #92A89C">{{ t('dashboard.budget.modal.manageCategories.add') }}</button>
                        </div>
                        <div class="space-y-2">
                            <div v-for="cat in categoryBreakdown" :key="cat.id"
                                class="flex items-center justify-between py-2.5 px-3 bg-stone-50 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: cat.color || '#92A89C' }"/>
                                    <span class="text-sm text-stone-700 font-medium">{{ cat.name }}</span>
                                    <span v-if="cat.type === 'custom'" class="text-xs text-[#73877C] bg-[#92A89C]/10 px-1.5 py-0.5 rounded">{{ t('dashboard.budget.modal.manageCategories.customBadge') }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-stone-400">{{ t('dashboard.budget.modal.manageCategories.itemCount', { count: cat.items_count }) }}</span>
                                    <button v-if="cat.type === 'custom'" @click="archiveCategory(cat)"
                                        class="p-1.5 text-stone-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-stone-400 mt-3">{{ t('dashboard.budget.modal.manageCategories.hint') }}</p>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Confirm Archive Dialog ──────────────────────────────────────── -->
        <Transition name="modal">
            <div v-if="showConfirmArchive" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="showConfirmArchive = false"/>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
                    <div class="w-12 h-12 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-800 mb-1">{{ t('dashboard.budget.modal.confirmArchive.title') }}</h3>
                    <p class="text-sm text-stone-500">{{ t('dashboard.budget.modal.confirmArchive.body', { title: archivingItem?.title }) }}</p>
                    <div class="flex gap-2 mt-5">
                        <button @click="showConfirmArchive = false; archivingItem = null"
                            class="flex-1 py-2.5 text-sm text-stone-600 border border-stone-200 rounded-xl hover:bg-stone-50 transition-colors">{{ t('dashboard.budget.modal.confirmArchive.cancel') }}</button>
                        <button @click="archiveItem"
                            class="flex-1 py-2.5 text-sm font-semibold text-white bg-rose-500 rounded-xl hover:bg-rose-600 transition-colors">{{ t('dashboard.budget.modal.confirmArchive.confirm') }}</button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Date Picker Modal ──────────────────────────────────────────── -->
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
                                <p class="text-sm font-bold text-stone-800">{{ t('dashboard.budget.modal.datePicker.title') }}</p>
                                <p v-if="currentPickerDate" class="text-xs text-[#73877C] mt-0.5">{{ calDisplayDate(currentPickerDate) }}</p>
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
                                 :class="d === t('dashboard.checklist.days.sun') ? 'text-rose-400' : 'text-stone-400'">{{ d }}</div>
                        </div>
                        <div class="grid grid-cols-7 px-4 pb-3 gap-y-1">
                            <div v-for="(day, i) in calDays" :key="i" class="flex items-center justify-center aspect-square">
                                <button v-if="day" type="button" @click="pickDay(day)"
                                        class="w-9 h-9 rounded-full text-sm font-medium transition-all"
                                        :class="isPickedDay(day) ? 'text-white font-bold shadow-sm' : 'text-stone-700 hover:bg-[#92A89C]/10 active:bg-[#92A89C]/20'"
                                        :style="isPickedDay(day) ? 'background-color:#92A89C' : ''">
                                    {{ day }}
                                </button>
                            </div>
                        </div>
                        <div class="px-5 pb-6 pt-2">
                            <button type="button" @click="closeDatePicker" :disabled="!currentPickerDate"
                                    class="w-full py-3.5 rounded-2xl text-sm font-bold text-white transition-all disabled:opacity-40"
                                    style="background-color:#92A89C">
                                <span v-if="currentPickerDate">{{ t('dashboard.budget.modal.datePicker.confirmWithDate', { date: calDisplayDate(currentPickerDate) }) }}</span>
                                <span v-else>{{ t('dashboard.budget.modal.datePicker.pickFirst') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </DashboardLayout>
</template>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-10px); }

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(100%); }

.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

.expand-enter-active, .expand-leave-active { transition: all 0.25s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 2000px; }
</style>
