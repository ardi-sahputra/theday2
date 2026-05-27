<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, reactive, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useLocale } from '@/Composables/useLocale';
import { guestListCache } from '@/Composables/guestListCache';

const { t } = useLocale();

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
    invitations:     { type: Array,  default: () => [] },
    hasTemplate:     { type: Boolean, default: false },
    defaultTemplate: { type: Object,  default: null },
});

// ── State ──────────────────────────────────────────────────────────────────
// Hydrate from the SPA-lived cache so returning is instant (revalidate quietly).
const guests      = ref(guestListCache.guests ?? []);
const guestsMeta  = ref(guestListCache.guestsMeta ?? { total: 0, current_page: 1, last_page: 1, per_page: 20 });
const summary     = ref(guestListCache.summary ?? { total: 0, not_sent: 0, sent: 0, opened: 0, attending: 0, not_attending: 0, pending_rsvp: 0 });
const categories  = ref(guestListCache.categories ?? []);
const loading     = ref(guestListCache.summary === null);
const loadingGuests = ref(false);
const error       = ref(null);

// ── Filters ────────────────────────────────────────────────────────────────
const search      = ref('');
const filters     = reactive({ send_status: '', rsvp_status: '', category: '', invitation_id: '' });
const sort        = ref('newest');
const page        = ref(1);
let   searchTimer = null;

// ── Modals ─────────────────────────────────────────────────────────────────
const showGuestForm       = ref(false);
const editingGuest        = ref(null);
const showTemplateModal   = ref(false);
const showSendModal       = ref(false);
const showImportModal     = ref(false);
const showDeleteConfirm   = ref(null);
const showBulkDeleteConfirm = ref(false);
const showBulkCategoryModal = ref(false);
const showFilterSheet     = ref(false); // mobile filter bottom sheet

// ── Guest form ─────────────────────────────────────────────────────────────
const emptyGuestForm = () => ({
    name: '', phone_number: '', category: '', greeting: '', note: '', invitation_id: '',
});
const guestForm   = ref(emptyGuestForm());
const guestErrors = ref({});
const savingGuest = ref(false);

// ── Template editor ────────────────────────────────────────────────────────
const templateForm     = reactive({ name: 'Template Default', content: '' });
const templateErrors   = ref({});
const savingTemplate   = ref(false);
const templatePreview  = ref('');
const templateWarnings = ref([]);
const previewLoading   = ref(false);

// ── Send WA modal ──────────────────────────────────────────────────────────
const sendTarget      = ref(null);
const generatedMsg    = ref('');
const generatedWaUrl  = ref('');
const generatedPhone  = ref('');
const generating      = ref(false);
const markingSent     = ref(false);

// ── Import modal ───────────────────────────────────────────────────────────
const importMode      = ref('paste');
const importText      = ref('');
const importFile      = ref(null);
const importInvId     = ref('');
const importPreview   = ref([]);
const importPreviewing = ref(false);
const importCommitting = ref(false);
const importResult    = ref(null);
const importStep      = ref('input');

// ── Bulk selection ─────────────────────────────────────────────────────────
const selected            = ref([]);
const bulkMarkSentLoading = ref(false);
const bulkDeleteLoading   = ref(false);
const bulkCategoryLoading = ref(false);
const bulkCategoryValue   = ref('');

const allSelected = computed(() =>
    guests.value.length > 0 && selected.value.length === guests.value.length
);

// ── Computed ───────────────────────────────────────────────────────────────
const hasActiveFilter = computed(() =>
    search.value || filters.send_status || filters.rsvp_status ||
    filters.category || filters.invitation_id
);

const activeFilterCount = computed(() => {
    let n = 0;
    if (filters.send_status)   n++;
    if (filters.rsvp_status)   n++;
    if (filters.category)      n++;
    if (filters.invitation_id) n++;
    return n;
});

const activeFilterChips = computed(() => {
    const chips = [];
    if (filters.send_status) {
        const labels = {
            not_sent: t('dashboard.guests.sendStatusNotSent'),
            sent: t('dashboard.guests.sendStatusSent'),
            opened: t('dashboard.guests.sendStatusOpened'),
        };
        chips.push({ key: 'send_status', label: `${t('dashboard.guests.chipStatusPrefix')}${labels[filters.send_status] ?? filters.send_status}` });
    }
    if (filters.rsvp_status) {
        const labels = {
            pending: t('dashboard.guests.rsvpPending'),
            attending: t('dashboard.guests.rsvpAttending'),
            not_attending: t('dashboard.guests.rsvpNotAttending'),
            maybe: t('dashboard.guests.rsvpMaybe'),
        };
        chips.push({ key: 'rsvp_status', label: `${t('dashboard.guests.chipRsvpPrefix')}${labels[filters.rsvp_status] ?? filters.rsvp_status}` });
    }
    if (filters.category) {
        chips.push({ key: 'category', label: `${t('dashboard.guests.chipCategoryPrefix')}${filters.category}` });
    }
    if (filters.invitation_id) {
        const inv = props.invitations.find(i => i.id == filters.invitation_id);
        chips.push({ key: 'invitation_id', label: `${t('dashboard.guests.chipInvitationPrefix')}${inv?.title ?? filters.invitation_id}` });
    }
    return chips;
});

const suggestedCategories = computed(() => {
    const defaults = ['Keluarga', 'Teman', 'Kantor', 'Tetangga', 'VIP', 'Lainnya'];
    const custom   = categories.value.filter(c => !defaults.includes(c));
    return [...defaults, ...custom];
});

// ── Bootstrap ──────────────────────────────────────────────────────────────
onMounted(async () => {
    try {
        if (guestListCache.summary !== null) {
            // Cached → render instantly, revalidate in the background.
            loading.value = false;
            Promise.all([loadGuests(), loadSummary(), loadCategories()]).catch(() => {});
        } else {
            await Promise.all([loadGuests(), loadSummary(), loadCategories()]);
        }
    } catch (e) {
        error.value = t('dashboard.guests.loadError');
    } finally {
        loading.value = false;
    }

    if (props.defaultTemplate) {
        templateForm.name    = props.defaultTemplate.name;
        templateForm.content = props.defaultTemplate.content;
    }
});

// Only the default (unfiltered, first-page) view matches a fresh mount, so we
// only cache that — otherwise a revisit could flash stale, filtered results.
function isDefaultGuestView() {
    return !search.value && !filters.send_status && !filters.rsvp_status
        && !filters.category && !filters.invitation_id && page.value === 1;
}

// ── Watchers ───────────────────────────────────────────────────────────────
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { page.value = 1; loadGuests(); }, 350);
});

watch([filters, sort], () => { page.value = 1; loadGuests(); }, { deep: true });

// ── Data loaders ───────────────────────────────────────────────────────────
async function loadGuests() {
    loadingGuests.value = true;
    try {
        const params = { page: page.value, per_page: 20, sort: sort.value };
        if (search.value)           params.search        = search.value;
        if (filters.send_status)    params.send_status   = filters.send_status;
        if (filters.rsvp_status)    params.rsvp_status   = filters.rsvp_status;
        if (filters.category)       params.category      = filters.category;
        if (filters.invitation_id)  params.invitation_id = filters.invitation_id;

        const { data } = await axios.get(route('dashboard.guest-list.guests'), { params });
        guests.value     = data.data;
        guestsMeta.value = data.meta;
        selected.value   = [];
        if (isDefaultGuestView()) {
            guestListCache.guests     = data.data;
            guestListCache.guestsMeta = data.meta;
        }
    } finally {
        loadingGuests.value = false;
    }
}

async function loadSummary() {
    const params = {};
    if (filters.invitation_id) params.invitation_id = filters.invitation_id;
    const { data } = await axios.get(route('dashboard.guest-list.summary'), { params });
    summary.value = data;
    if (!filters.invitation_id) guestListCache.summary = data;
}

async function loadCategories() {
    const { data } = await axios.get(route('dashboard.guest-list.categories'));
    categories.value = data.categories;
    guestListCache.categories = data.categories;
}

async function changePage(p) {
    page.value = p;
    await loadGuests();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Stat card quick filters ────────────────────────────────────────────────
function applyStatFilter(type, value) {
    if (type === 'reset') {
        filters.send_status  = '';
        filters.rsvp_status  = '';
        page.value = 1;
        return;
    }
    // toggle: clicking same active card resets it
    if (type === 'send_status') {
        filters.send_status = filters.send_status === value ? '' : value;
        filters.rsvp_status = '';
    } else if (type === 'rsvp_status') {
        filters.rsvp_status = filters.rsvp_status === value ? '' : value;
        filters.send_status = '';
    }
    page.value = 1;
}

function isStatActive(type, value) {
    if (type === 'send_status') return filters.send_status === value;
    if (type === 'rsvp_status') return filters.rsvp_status === value;
    return !filters.send_status && !filters.rsvp_status;
}

function removeFilterChip(key) {
    filters[key] = '';
}

function resetAllFilters() {
    search.value = '';
    Object.assign(filters, { send_status: '', rsvp_status: '', category: '', invitation_id: '' });
}

// ── Guest CRUD ─────────────────────────────────────────────────────────────
function openCreate() {
    editingGuest.value = null;
    guestForm.value    = emptyGuestForm();
    guestErrors.value  = {};
    showGuestForm.value = true;
}

function openEdit(guest) {
    editingGuest.value = guest;
    guestForm.value    = {
        name:          guest.name,
        phone_number:  guest.phone_number,
        category:      guest.category ?? '',
        greeting:      guest.greeting ?? '',
        note:          guest.note ?? '',
        invitation_id: guest.invitation?.id ?? '',
    };
    guestErrors.value  = {};
    showGuestForm.value = true;
}

function closeGuestForm() {
    showGuestForm.value = false;
    editingGuest.value  = null;
}

async function saveGuest() {
    guestErrors.value = {};
    savingGuest.value = true;
    try {
        const payload = { ...guestForm.value };
        if (!payload.invitation_id) delete payload.invitation_id;

        if (editingGuest.value) {
            const { data } = await axios.patch(
                route('dashboard.guest-list.update', editingGuest.value.id), payload
            );
            const idx = guests.value.findIndex(g => g.id === editingGuest.value.id);
            if (idx > -1) guests.value[idx] = data;
            showToast(t('dashboard.guests.toastGuestUpdated'));
        } else {
            await axios.post(route('dashboard.guest-list.store'), payload);
            showToast(t('dashboard.guests.toastGuestAdded'));
        }

        closeGuestForm();
        await Promise.all([loadGuests(), loadSummary(), loadCategories()]);
    } catch (e) {
        if (e.response?.status === 422) {
            guestErrors.value = e.response.data.errors ?? {};
        } else {
            showToast(t('dashboard.guests.toastSaveFailed'));
        }
    } finally {
        savingGuest.value = false;
    }
}

async function deleteGuest(guest) {
    await axios.delete(route('dashboard.guest-list.destroy', guest.id));
    guests.value = guests.value.filter(g => g.id !== guest.id);
    showDeleteConfirm.value = null;
    await loadSummary();
}

// ── WA Template ────────────────────────────────────────────────────────────
function openTemplateEditor() {
    showTemplateModal.value = true;
    templatePreview.value   = '';
    templateWarnings.value  = [];
    if (!templateForm.content) {
        templateForm.content = "Halo {{guest_name}},\n\nDengan penuh kebahagiaan, kami mengundang Anda untuk hadir di hari spesial kami.\n\nBuka undangannya di sini:\n{{invitation_url}}\n\nTerima kasih atas doa dan kehadirannya.";
    }
}

async function previewTemplate() {
    if (!templateForm.content) return;
    previewLoading.value = true;
    try {
        const { data } = await axios.post(route('dashboard.guest-list.template.preview'), {
            content: templateForm.content,
        });
        templatePreview.value  = data.rendered;
        templateWarnings.value = data.warnings ?? [];
    } catch {
        showToast(t('dashboard.guests.toastPreviewFailed'));
    } finally {
        previewLoading.value = false;
    }
}

async function saveTemplate() {
    templateErrors.value = {};
    savingTemplate.value = true;
    try {
        await axios.put(route('dashboard.guest-list.template.update'), {
            name:    templateForm.name || 'Template Default',
            content: templateForm.content,
        });
        showTemplateModal.value = false;
        showToast(t('dashboard.guests.toastTemplateSaved'));
    } catch (e) {
        if (e.response?.status === 422) {
            templateErrors.value = e.response.data.errors ?? {};
        }
    } finally {
        savingTemplate.value = false;
    }
}

async function resetTemplate() {
    const { data } = await axios.post(route('dashboard.guest-list.template.reset'));
    templateForm.name    = data.template.name;
    templateForm.content = data.template.content;
    templatePreview.value = '';
}

function insertPlaceholder(ph) {
    templateForm.content += `{{${ph}}}`;
}

// ── Send WA ────────────────────────────────────────────────────────────────
async function openSendModal(guest) {
    sendTarget.value     = guest;
    generatedMsg.value   = '';
    generatedWaUrl.value = '';
    showSendModal.value  = true;
    generating.value     = true;

    try {
        const { data } = await axios.post(
            route('dashboard.guest-list.message.generate', guest.id)
        );
        generatedMsg.value   = data.message;
        generatedWaUrl.value = data.whatsapp_url;
        generatedPhone.value = data.phone;
    } catch (e) {
        if (e.response?.status === 422) {
            showSendModal.value = false;
            openTemplateEditor();
        }
    } finally {
        generating.value = false;
    }
}

function openWhatsApp() {
    if (generatedWaUrl.value) {
        window.open(generatedWaUrl.value, '_blank');
    }
}

async function confirmSent() {
    if (!sendTarget.value || markingSent.value) return;
    markingSent.value = true;
    try {
        const { data } = await axios.post(
            route('dashboard.guest-list.message.mark-sent', sendTarget.value.id)
        );
        const idx = guests.value.findIndex(g => g.id === sendTarget.value.id);
        if (idx > -1) guests.value[idx] = data;
        await loadSummary();
        showSendModal.value = false;
        showToast(t('dashboard.guests.toastStatusUpdated'));
    } finally {
        markingSent.value = false;
    }
}

async function copyMessage() {
    if (!generatedMsg.value) return;
    try {
        await navigator.clipboard.writeText(generatedMsg.value);
        showToast(t('dashboard.guests.toastCopied'));
        if (sendTarget.value) {
            axios.post(route('dashboard.guest-list.message.copy-log', sendTarget.value.id)).catch(() => {});
        }
    } catch {
        showToast(t('dashboard.guests.toastCopyFailed'));
    }
}

// ── Import ─────────────────────────────────────────────────────────────────
function openImport() {
    importStep.value    = 'input';
    importMode.value    = 'paste';
    importText.value    = '';
    importFile.value    = null;
    importInvId.value   = props.invitations[0]?.id ?? '';
    importPreview.value = [];
    importResult.value  = null;
    showImportModal.value = true;
}

function onFileChange(e) {
    importFile.value = e.target.files[0] ?? null;
}

async function previewImport() {
    importPreviewing.value = true;
    try {
        const form = new FormData();
        form.append('mode', importMode.value);
        if (importInvId.value) form.append('invitation_id', importInvId.value);
        if (importMode.value === 'paste') {
            form.append('raw_text', importText.value);
        } else if (importFile.value) {
            form.append('file', importFile.value);
        }

        const { data } = await axios.post(
            route('dashboard.guest-list.import.preview'), form,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        importPreview.value = data.rows;
        importStep.value    = 'preview';
    } catch (e) {
        showToast(t('dashboard.guests.toastImportFailed'));
    } finally {
        importPreviewing.value = false;
    }
}

async function commitImport() {
    const validRows = importPreview.value.filter(r => r.valid);
    if (validRows.length === 0) return;

    importCommitting.value = true;
    try {
        const { data } = await axios.post(route('dashboard.guest-list.import.store'), {
            invitation_id: importInvId.value || null,
            rows: validRows.map(r => ({
                name:     r.name,
                phone:    r.phone,
                category: r.category,
                greeting: r.greeting,
                note:     r.note,
            })),
        });
        importResult.value = data;
        importStep.value   = 'done';
        await Promise.all([loadGuests(), loadSummary(), loadCategories()]);
    } finally {
        importCommitting.value = false;
    }
}

// ── Bulk selection ─────────────────────────────────────────────────────────
function toggleSelectAll() {
    selected.value = allSelected.value ? [] : guests.value.map(g => g.id);
}

function toggleSelect(id) {
    const idx = selected.value.indexOf(id);
    if (idx > -1) selected.value.splice(idx, 1);
    else selected.value.push(id);
}

// ── Bulk actions ───────────────────────────────────────────────────────────
async function bulkMarkSent() {
    if (selected.value.length === 0 || bulkMarkSentLoading.value) return;
    const ids = [...selected.value];
    bulkMarkSentLoading.value = true;
    try {
        await Promise.all(
            ids.map(id => axios.post(route('dashboard.guest-list.message.mark-sent', id)))
        );
        await Promise.all([loadGuests(), loadSummary()]);
        showToast(t('dashboard.guests.toastBulkSent', { n: ids.length }));
    } finally {
        bulkMarkSentLoading.value = false;
    }
}

async function bulkDelete() {
    if (selected.value.length === 0 || bulkDeleteLoading.value) return;
    const ids = [...selected.value];
    bulkDeleteLoading.value = true;
    try {
        await axios.post(route('dashboard.guest-list.bulk.destroy'), { ids });
        showBulkDeleteConfirm.value = false;
        await Promise.all([loadGuests(), loadSummary()]);
        showToast(t('dashboard.guests.toastBulkDeleted', { n: ids.length }));
    } finally {
        bulkDeleteLoading.value = false;
    }
}

function openBulkCategoryModal() {
    bulkCategoryValue.value = '';
    showBulkCategoryModal.value = true;
}

async function bulkUpdateCategory() {
    if (!bulkCategoryValue.value || bulkCategoryLoading.value) return;
    const ids = [...selected.value];
    bulkCategoryLoading.value = true;
    try {
        await axios.post(route('dashboard.guest-list.bulk.category'), {
            ids,
            category: bulkCategoryValue.value,
        });
        showBulkCategoryModal.value = false;
        await Promise.all([loadGuests(), loadCategories()]);
        showToast(t('dashboard.guests.toastBulkCategoryUpdated', { n: ids.length }));
    } finally {
        bulkCategoryLoading.value = false;
    }
}

function bulkExport() {
    const ids = selected.value.join(',');
    const url = route('dashboard.guest-list.export') + (ids ? `?ids=${ids}` : '');
    window.open(url, '_blank');
}

// ── Toast ──────────────────────────────────────────────────────────────────
const toast = ref('');
let toastTimer = null;

function showToast(msg) {
    toast.value = msg;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.value = ''; }, 3000);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function sendStatusBadge(status) {
    return {
        not_sent: { label: t('dashboard.guests.sendStatusNotSent'),  cls: 'bg-stone-100 text-stone-500 ring-1 ring-stone-200' },
        sent:     { label: t('dashboard.guests.sendStatusSent'),     cls: 'bg-[#92A89C]/10 text-[#73877C] ring-1 ring-[#92A89C]/20' },
        opened:   { label: t('dashboard.guests.sendStatusOpened'),   cls: 'bg-green-50 text-green-700 ring-1 ring-green-100' },
    }[status] ?? { label: status, cls: 'bg-stone-100 text-stone-400' };
}

function rsvpBadge(status) {
    return {
        pending:       { label: t('dashboard.guests.rsvpPending'),      cls: 'bg-[#92A89C]/10 text-[#73877C] ring-1 ring-[#92A89C]/20' },
        attending:     { label: t('dashboard.guests.rsvpAttending'),    cls: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' },
        not_attending: { label: t('dashboard.guests.rsvpNotAttending'), cls: 'bg-red-50 text-red-500 ring-1 ring-red-100' },
        maybe:         { label: t('dashboard.guests.rsvpMaybe'),        cls: 'bg-violet-50 text-violet-600 ring-1 ring-violet-100' },
    }[status] ?? { label: status, cls: 'bg-stone-100 text-stone-400' };
}

function relativeTime(iso) {
    if (!iso) return t('dashboard.guests.relativeNever');
    const now  = new Date();
    const date = new Date(iso);
    const ms   = now - date;
    const min  = Math.floor(ms / 60000);
    const hr   = Math.floor(min / 60);
    const days = Math.floor(hr / 24);

    if (min < 1)    return t('dashboard.guests.relativeJustNow');
    if (min < 60)   return t('dashboard.guests.relativeMinutes', { n: min });
    if (hr  < 24)   return t('dashboard.guests.relativeHours', { n: hr });
    if (days === 1) return t('dashboard.guests.relativeYesterday');
    if (days < 7)   return t('dashboard.guests.relativeDays', { n: days });
    return formatDate(iso);
}

function formatDate(iso) {
    if (!iso) return null;
    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function phLabel(ph) {
    return '{{' + ph + '}}';
}

function formatPhone(phone) {
    if (!phone) return '';
    const local = '0' + phone.slice(2);
    if (local.length >= 10) {
        return local.slice(0, 4) + '-' + local.slice(4, 8) + '-' + local.slice(8);
    }
    return local;
}

function hasValidPhone(guest) {
    return !!(guest.normalized_phone || guest.phone_number);
}

function sendLabel(guest) {
    return (guest.send_status === 'sent' || guest.send_status === 'opened')
        ? t('dashboard.guests.sendLabelResend')
        : t('dashboard.guests.sendLabelSend');
}
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h1 class="text-base font-semibold text-stone-800">{{ t('dashboard.guests.title') }}</h1>
        </template>

        <!-- ── Loading ───────────────────────────────────────────── -->
        <div v-if="loading" class="flex items-center justify-center py-24">
            <div class="w-8 h-8 rounded-full border-2 border-[#92A89C]/50 border-t-[#73877C] animate-spin"/>
        </div>

        <template v-else>

            <!-- ── Template prompt banner ────────────────────────── -->
            <div v-if="!hasTemplate && guests.length > 0"
                 class="mb-4 flex items-start gap-3 px-4 py-3 rounded-xl bg-[#92A89C]/10 border border-[#B8C7BF]/50 text-sm text-[#73877C]">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="flex-1">{{ t('dashboard.guests.templateBannerText') }}</p>
                <button @click="openTemplateEditor"
                        class="text-xs font-semibold text-[#2C2417] underline whitespace-nowrap">{{ t('dashboard.guests.setTemplate') }}</button>
            </div>

            <!-- ── Summary Cards (clickable quick filters) ───────── -->
            <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3 mb-5">
                <!-- Total -->
                <button @click="applyStatFilter('reset','')"
                        class="text-left rounded-xl border p-3 sm:p-4 transition-all"
                        :class="isStatActive('total','') ? 'bg-[#92A89C]/10 border-[#92A89C]/50 ring-2 ring-[#92A89C]/30' : 'bg-white border-stone-100 hover:border-stone-200 hover:bg-stone-50/50'">
                    <p class="text-xs text-stone-400 mb-1">{{ t('dashboard.guests.statTotal') }}</p>
                    <p class="text-xl sm:text-2xl font-bold text-stone-800">{{ summary.total }}</p>
                </button>
                <!-- Belum Kirim -->
                <button @click="applyStatFilter('send_status','not_sent')"
                        class="text-left rounded-xl border p-3 sm:p-4 transition-all"
                        :class="isStatActive('send_status','not_sent') ? 'bg-stone-100 border-stone-400 ring-2 ring-stone-200' : 'bg-white border-stone-100 hover:border-stone-200 hover:bg-stone-50/50'">
                    <p class="text-xs text-stone-400 mb-1">{{ t('dashboard.guests.statNotSent') }}</p>
                    <p class="text-xl sm:text-2xl font-bold text-stone-500">{{ summary.not_sent }}</p>
                </button>
                <!-- Sudah Kirim -->
                <button @click="applyStatFilter('send_status','sent')"
                        class="text-left rounded-xl border p-3 sm:p-4 transition-all"
                        :class="isStatActive('send_status','sent') ? 'bg-[#92A89C]/10 border-blue-300 ring-2 ring-[#92A89C]/20' : 'bg-white border-stone-100 hover:border-stone-200 hover:bg-stone-50/50'">
                    <p class="text-xs text-stone-400 mb-1">{{ t('dashboard.guests.statSent') }}</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#73877C]">{{ summary.sent + summary.opened }}</p>
                </button>
                <!-- Sudah Dibuka -->
                <button @click="applyStatFilter('send_status','opened')"
                        class="text-left rounded-xl border p-3 sm:p-4 transition-all"
                        :class="isStatActive('send_status','opened') ? 'bg-green-50 border-green-300 ring-2 ring-green-100' : 'bg-white border-stone-100 hover:border-stone-200 hover:bg-stone-50/50'">
                    <p class="text-xs text-stone-400 mb-1">{{ t('dashboard.guests.statOpened') }}</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-600">{{ summary.opened }}</p>
                </button>
                <!-- Hadir -->
                <button @click="applyStatFilter('rsvp_status','attending')"
                        class="text-left rounded-xl border p-3 sm:p-4 transition-all"
                        :class="isStatActive('rsvp_status','attending') ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-100' : 'bg-white border-stone-100 hover:border-stone-200 hover:bg-stone-50/50'">
                    <p class="text-xs text-stone-400 mb-1">{{ t('dashboard.guests.statAttending') }}</p>
                    <p class="text-xl sm:text-2xl font-bold text-emerald-600">{{ summary.attending }}</p>
                </button>
                <!-- Belum Respon -->
                <button @click="applyStatFilter('rsvp_status','pending')"
                        class="text-left rounded-xl border p-3 sm:p-4 transition-all"
                        :class="isStatActive('rsvp_status','pending') ? 'bg-[#92A89C]/10 border-[#92A89C]/50 ring-2 ring-[#92A89C]/20' : 'bg-white border-stone-100 hover:border-stone-200 hover:bg-stone-50/50'">
                    <p class="text-xs text-stone-400 mb-1">{{ t('dashboard.guests.statPending') }}</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#73877C]">{{ summary.pending_rsvp }}</p>
                </button>
            </div>

            <!-- ── Toolbar ───────────────────────────────────────── -->
            <div class="flex flex-col md:flex-row md:items-center gap-2 mb-3">

                <!-- Baris 1: Search + filter (mobile) / Search + semua filter (desktop) -->
                <div class="flex items-center gap-2">
                    <!-- Search -->
                    <div class="relative flex-1 min-w-0">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input v-model="search" type="text" :placeholder="t('dashboard.guests.searchPlaceholder')"
                               class="w-full pl-9 pr-3 py-2 border border-stone-200 rounded-xl text-sm text-stone-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"/>
                    </div>

                    <!-- Mobile: tombol Filter -->
                    <button @click="showFilterSheet = true"
                            class="md:hidden flex items-center gap-1.5 px-3 py-2 rounded-xl border text-sm transition-colors whitespace-nowrap"
                            :class="activeFilterCount > 0 ? 'border-[#92A89C]/50 bg-[#92A89C]/10 text-[#73877C]' : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        {{ t('dashboard.guests.filterButton') }}
                        <span v-if="activeFilterCount > 0"
                              class="w-4 h-4 rounded-full bg-[#73877C] text-white text-xs flex items-center justify-center font-bold">
                            {{ activeFilterCount }}
                        </span>
                    </button>

                    <!-- Desktop: dropdown filters inline -->
                    <div class="hidden md:flex items-center gap-2">
                        <select v-model="filters.send_status"
                                class="text-sm border border-stone-200 rounded-xl pl-3 pr-8 py-2 bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                            <option value="">{{ t('dashboard.guests.filterAllStatus') }}</option>
                            <option value="not_sent">{{ t('dashboard.guests.sendStatusNotSent') }}</option>
                            <option value="sent">{{ t('dashboard.guests.sendStatusSent') }}</option>
                            <option value="opened">{{ t('dashboard.guests.sendStatusOpened') }}</option>
                        </select>

                        <select v-model="filters.rsvp_status"
                                class="text-sm border border-stone-200 rounded-xl pl-3 pr-8 py-2 bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                            <option value="">{{ t('dashboard.guests.filterAllRsvp') }}</option>
                            <option value="pending">{{ t('dashboard.guests.rsvpPending') }}</option>
                            <option value="attending">{{ t('dashboard.guests.rsvpAttending') }}</option>
                            <option value="not_attending">{{ t('dashboard.guests.rsvpNotAttending') }}</option>
                            <option value="maybe">{{ t('dashboard.guests.rsvpMaybe') }}</option>
                        </select>

                        <select v-if="categories.length > 0" v-model="filters.category"
                                class="text-sm border border-stone-200 rounded-xl pl-3 pr-8 py-2 bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                            <option value="">{{ t('dashboard.guests.filterAllCategory') }}</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>

                        <select v-model="sort"
                                class="text-sm border border-stone-200 rounded-xl pl-3 pr-8 py-2 bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                            <option value="newest">{{ t('dashboard.guests.sortNewest') }}</option>
                            <option value="name_asc">{{ t('dashboard.guests.sortNameAsc') }}</option>
                            <option value="not_sent">{{ t('dashboard.guests.sortNotSentFirst') }}</option>
                            <option value="not_rsvp">{{ t('dashboard.guests.sortNotRsvpFirst') }}</option>
                        </select>

                        <button v-if="hasActiveFilter" @click="resetAllFilters"
                                class="text-xs text-stone-500 underline px-2 whitespace-nowrap">{{ t('dashboard.guests.resetButton') }}</button>
                    </div>
                </div>

                <!-- Baris 2 (mobile): tombol aksi full-width / Desktop: lanjut inline -->
                <div class="flex items-center gap-2 md:ml-auto">
                    <button @click="openTemplateEditor"
                            class="flex-none flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 10h16M4 14h8"/>
                        </svg>
                        {{ t('dashboard.guests.templateWaButton') }}
                    </button>
                    <button @click="openImport"
                            class="flex-1 md:flex-none flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50 transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span class="hidden lg:inline">{{ t('dashboard.guests.importButton') }}</span>
                    </button>
                    <button @click="openCreate"
                            class="flex-1 md:flex-none flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium text-white transition-opacity hover:opacity-90"
                            style="background-color:#92A89C">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="hidden lg:inline">{{ t('dashboard.guests.addGuestButton') }}</span>
                    </button>
                </div>

            </div>

            <!-- ── Active filter chips ────────────────────────────── -->
            <div v-if="activeFilterChips.length > 0" class="flex flex-wrap gap-1.5 mb-3">
                <div v-for="chip in activeFilterChips" :key="chip.key"
                     class="flex items-center gap-1 pl-2.5 pr-1.5 py-1 rounded-full bg-[#92A89C]/10 border border-[#B8C7BF]/50 text-xs text-[#73877C]">
                    {{ chip.label }}
                    <button @click="removeFilterChip(chip.key)"
                            class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-[#92A89C]/20 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <button @click="resetAllFilters" class="text-xs text-stone-400 hover:text-stone-600 px-1 underline">
                    {{ t('dashboard.guests.resetAll') }}
                </button>
            </div>

            <!-- ── Bulk action bar ────────────────────────────────── -->
            <Transition name="slide-down">
                <div v-if="selected.length > 0"
                     class="mb-3 flex flex-wrap items-center gap-2 px-4 py-2.5 rounded-xl bg-[#92A89C]/10 border border-[#B8C7BF]/50">
                    <p class="text-sm text-[#2C2417] font-medium mr-1">{{ t('dashboard.guests.bulkSelected', { n: selected.length }) }}</p>

                    <!-- Kirim WA -->
                    <button @click="openSendModal(guests.find(g => g.id === selected[0]))"
                            class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg bg-[#25D366] text-white font-medium hover:opacity-90 transition-opacity">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        {{ t('dashboard.guests.bulkSendWa') }}
                    </button>

                    <!-- Ubah Kategori -->
                    <button @click="openBulkCategoryModal"
                            class="text-xs px-3 py-1.5 rounded-lg bg-white border border-[#B8C7BF] text-[#73877C] font-medium hover:bg-[#92A89C]/10 transition-colors">
                        {{ t('dashboard.guests.bulkChangeCategory') }}
                    </button>

                    <!-- Export -->
                    <button @click="bulkExport"
                            class="text-xs px-3 py-1.5 rounded-lg bg-white border border-[#B8C7BF] text-[#73877C] font-medium hover:bg-[#92A89C]/10 transition-colors">
                        {{ t('dashboard.guests.bulkExport') }}
                    </button>

                    <!-- Hapus -->
                    <button @click="showBulkDeleteConfirm = true"
                            class="text-xs px-3 py-1.5 rounded-lg bg-white border border-red-200 text-red-500 font-medium hover:bg-red-50 transition-colors">
                        {{ t('dashboard.guests.bulkDelete') }}
                    </button>

                    <button @click="selected = []"
                            class="ml-auto text-xs text-[#73877C] hover:underline">{{ t('dashboard.guests.bulkCancel') }}</button>
                </div>
            </Transition>

            <!-- ── Guest loading ──────────────────────────────────── -->
            <div v-if="loadingGuests" class="flex justify-center py-12">
                <div class="w-6 h-6 rounded-full border-2 border-[#92A89C]/50 border-t-[#73877C] animate-spin"/>
            </div>

            <!-- ── Empty state ────────────────────────────────────── -->
            <div v-else-if="guests.length === 0" class="flex flex-col items-center py-20 gap-4">
                <div class="w-16 h-16 rounded-full bg-[#92A89C]/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-[#92A89C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="text-center">
                    <p class="text-stone-700 font-medium">
                        {{ hasActiveFilter ? t('dashboard.guests.emptyNoMatch') : t('dashboard.guests.emptyTitle') }}
                    </p>
                    <p class="text-stone-400 text-sm mt-1">
                        {{ hasActiveFilter ? t('dashboard.guests.emptyNoMatchHint') : t('dashboard.guests.emptyHint') }}
                    </p>
                </div>
                <div v-if="hasActiveFilter">
                    <button @click="resetAllFilters"
                            class="px-4 py-2 rounded-xl text-sm border border-stone-200 text-stone-600 hover:bg-stone-50">
                        {{ t('dashboard.guests.resetFilterButton') }}
                    </button>
                </div>
                <div v-else class="flex gap-2">
                    <button @click="openCreate"
                            class="px-4 py-2 rounded-xl text-sm font-medium text-white"
                            style="background-color:#92A89C">{{ t('dashboard.guests.addGuestButton') }}</button>
                    <button @click="openImport"
                            class="px-4 py-2 rounded-xl text-sm border border-stone-200 text-stone-600 hover:bg-stone-50">{{ t('dashboard.guests.importCsvButton') }}</button>
                </div>
            </div>

            <template v-else>
                <!-- ── Desktop table ──────────────────────────────── -->
                <div class="hidden md:block bg-white border border-stone-100 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="border-b border-stone-100 bg-stone-50/50">
                            <tr>
                                <th class="w-10 px-4 py-3">
                                    <input type="checkbox" :checked="allSelected" @change="toggleSelectAll"
                                           class="rounded border-stone-300 text-[#92A89C] focus:ring-[#92A89C]/30"/>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wide">{{ t('dashboard.guests.tableGuest') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wide">{{ t('dashboard.guests.tableCategory') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wide">{{ t('dashboard.guests.tableSendStatus') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wide">{{ t('dashboard.guests.tableRsvp') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase tracking-wide">{{ t('dashboard.guests.tableLastSent') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-stone-500 uppercase tracking-wide">{{ t('dashboard.guests.tableActions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-50">
                            <tr v-for="guest in guests" :key="guest.id"
                                class="hover:bg-stone-50/30 transition-colors">
                                <td class="px-4 py-3.5">
                                    <input type="checkbox" :checked="selected.includes(guest.id)"
                                           @change="toggleSelect(guest.id)"
                                           class="rounded border-stone-300 text-[#92A89C] focus:ring-[#92A89C]/30"/>
                                </td>
                                <!-- Name + phone -->
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-stone-800 leading-snug">{{ guest.name }}</p>
                                    <p class="text-xs text-stone-400 mt-0.5">
                                        {{ guest.normalized_phone ? formatPhone(guest.normalized_phone) : guest.phone_number }}
                                    </p>
                                    <p v-if="guest.invitation" class="text-xs text-stone-300 mt-0.5 truncate max-w-40">{{ guest.invitation.title }}</p>
                                </td>
                                <!-- Category -->
                                <td class="px-4 py-3.5">
                                    <span v-if="guest.category"
                                          class="text-xs px-2 py-0.5 rounded-full bg-stone-100 text-stone-500 font-medium">
                                        {{ guest.category }}
                                    </span>
                                    <span v-else class="text-stone-300 text-xs">—</span>
                                </td>
                                <!-- Send status -->
                                <td class="px-4 py-3.5">
                                    <span class="text-xs px-2.5 py-1 rounded-md font-medium"
                                          :class="sendStatusBadge(guest.send_status).cls">
                                        {{ sendStatusBadge(guest.send_status).label }}
                                    </span>
                                </td>
                                <!-- RSVP -->
                                <td class="px-4 py-3.5">
                                    <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                                          :class="rsvpBadge(guest.rsvp_status).cls">
                                        {{ rsvpBadge(guest.rsvp_status).label }}
                                    </span>
                                </td>
                                <!-- Last sent -->
                                <td class="px-4 py-3.5">
                                    <span class="text-xs text-stone-400" :title="formatDate(guest.last_sent_at) ?? ''">
                                        {{ relativeTime(guest.last_sent_at) }}
                                    </span>
                                    <span v-if="guest.sent_count > 1" class="ml-1 text-xs text-stone-300">
                                        ({{ guest.sent_count }}x)
                                    </span>
                                </td>
                                <!-- Actions -->
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button v-if="hasValidPhone(guest)"
                                                @click="openSendModal(guest)"
                                                :title="sendLabel(guest)"
                                                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-white transition-opacity hover:opacity-90"
                                                style="background-color:#25D366">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                            {{ sendLabel(guest) }}
                                        </button>
                                        <span v-else
                                              class="text-xs px-2.5 py-1.5 rounded-lg bg-stone-50 text-stone-300 cursor-not-allowed"
                                              :title="t('dashboard.guests.noPhoneNumber')">{{ t('dashboard.guests.bulkSendWa') }}</span>
                                        <button @click="openEdit(guest)"
                                                class="p-1.5 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors" :title="t('dashboard.guests.editButton')">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button @click="showDeleteConfirm = guest"
                                                class="p-1.5 rounded-lg text-stone-400 hover:text-red-500 hover:bg-red-50 transition-colors" :title="t('dashboard.guests.deleteButton')">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ── Mobile card list ───────────────────────────── -->
                <div class="md:hidden space-y-2.5">
                    <div v-for="guest in guests" :key="guest.id"
                         class="bg-white border border-stone-100 rounded-xl p-4">
                        <!-- Row 1: name + send status -->
                        <div class="flex items-start justify-between gap-2 mb-1.5">
                            <p class="font-semibold text-stone-800 leading-snug flex-1 min-w-0 truncate">{{ guest.name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-md font-medium flex-shrink-0"
                                  :class="sendStatusBadge(guest.send_status).cls">
                                {{ sendStatusBadge(guest.send_status).label }}
                            </span>
                        </div>
                        <!-- Row 2: phone -->
                        <p class="text-xs text-stone-400 mb-2">
                            {{ guest.normalized_phone ? formatPhone(guest.normalized_phone) : guest.phone_number }}
                        </p>
                        <!-- Row 3: category + RSVP chips -->
                        <div class="flex flex-wrap gap-1 mb-3">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-stone-100 text-stone-500 font-medium">
                                {{ guest.category || t('dashboard.guests.categoryOther') }}
                            </span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-medium"
                                  :class="rsvpBadge(guest.rsvp_status).cls">
                                {{ rsvpBadge(guest.rsvp_status).label }}
                            </span>
                        </div>
                        <!-- Row 4: Kirim WA (primary CTA) -->
                        <button v-if="hasValidPhone(guest)"
                                @click="openSendModal(guest)"
                                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold text-white mb-2 transition-opacity hover:opacity-90"
                                style="background-color:#25D366">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            {{ sendLabel(guest) }}
                        </button>
                        <div v-else class="w-full flex items-center justify-center py-2.5 rounded-xl bg-stone-50 text-xs text-stone-300 mb-2 cursor-not-allowed">
                            {{ t('dashboard.guests.noPhoneNumber') }}
                        </div>
                        <!-- Row 5: secondary actions -->
                        <div class="flex items-center gap-2 pt-2 border-t border-stone-50">
                            <button @click="openEdit(guest)"
                                    class="flex-1 py-1.5 rounded-lg border border-stone-200 text-stone-500 hover:bg-stone-50 text-xs font-medium transition-colors">
                                {{ t('dashboard.guests.editButton') }}
                            </button>
                            <button @click="showDeleteConfirm = guest"
                                    class="flex-1 py-1.5 rounded-lg border border-red-100 text-red-400 hover:bg-red-50 hover:text-red-500 text-xs font-medium transition-colors">
                                {{ t('dashboard.guests.deleteButton') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ── Pagination ─────────────────────────────────────── -->
            <div v-if="guestsMeta.last_page > 1"
                 class="flex items-center justify-between mt-4 pt-4 border-t border-stone-100">
                <p class="text-xs text-stone-400">
                    {{ (guestsMeta.current_page - 1) * guestsMeta.per_page + 1 }}–{{ Math.min(guestsMeta.current_page * guestsMeta.per_page, guestsMeta.total) }}
                    {{ t('dashboard.guests.paginationOf', { total: guestsMeta.total }) }}
                </p>
                <div class="flex gap-1">
                    <button v-for="p in guestsMeta.last_page" :key="p"
                            @click="changePage(p)"
                            class="w-8 h-8 rounded-lg text-sm transition-colors"
                            :class="p === guestsMeta.current_page
                                ? 'text-white'
                                : 'border border-stone-200 text-stone-600 hover:bg-stone-50'"
                            :style="p === guestsMeta.current_page ? 'background-color:#92A89C' : ''">
                        {{ p }}
                    </button>
                </div>
            </div>

        </template><!-- end v-else -->

        <!-- ═══════════════════════════════════════════════════════ -->
        <!--  MOBILE FILTER BOTTOM SHEET                            -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showFilterSheet"
                 class="fixed inset-0 z-50 flex items-end md:hidden bg-black/40"
                 @click.self="showFilterSheet = false">
                <div class="bg-white w-full rounded-t-2xl shadow-xl p-5 max-h-[80vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-stone-800">{{ t('dashboard.guests.filterSheetTitle') }}</h2>
                        <button @click="showFilterSheet = false" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Status Kirim -->
                        <div>
                            <label class="text-xs font-medium text-stone-500 uppercase tracking-wide mb-2 block">{{ t('dashboard.guests.filterSendStatus') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="opt in [
                                    {v:'',l:t('dashboard.guests.filterAll')},
                                    {v:'not_sent',l:t('dashboard.guests.sendStatusNotSent')},
                                    {v:'sent',l:t('dashboard.guests.sendStatusSent')},
                                    {v:'opened',l:t('dashboard.guests.sendStatusOpened')}
                                ]"
                                        :key="opt.v"
                                        @click="filters.send_status = opt.v"
                                        class="py-2 px-3 rounded-xl text-sm border transition-colors"
                                        :class="filters.send_status === opt.v
                                            ? 'border-[#92A89C] bg-[#92A89C]/10 text-[#73877C] font-medium'
                                            : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                                    {{ opt.l }}
                                </button>
                            </div>
                        </div>

                        <!-- RSVP -->
                        <div>
                            <label class="text-xs font-medium text-stone-500 uppercase tracking-wide mb-2 block">{{ t('dashboard.guests.filterRsvpLabel') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="opt in [
                                    {v:'',l:t('dashboard.guests.filterAll')},
                                    {v:'pending',l:t('dashboard.guests.rsvpPending')},
                                    {v:'attending',l:t('dashboard.guests.rsvpAttending')},
                                    {v:'not_attending',l:t('dashboard.guests.rsvpNotAttending')},
                                    {v:'maybe',l:t('dashboard.guests.rsvpMaybe')}
                                ]"
                                        :key="opt.v"
                                        @click="filters.rsvp_status = opt.v"
                                        class="py-2 px-3 rounded-xl text-sm border transition-colors"
                                        :class="filters.rsvp_status === opt.v
                                            ? 'border-[#92A89C] bg-[#92A89C]/10 text-[#73877C] font-medium'
                                            : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                                    {{ opt.l }}
                                </button>
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div v-if="categories.length > 0">
                            <label class="text-xs font-medium text-stone-500 uppercase tracking-wide mb-2 block">{{ t('dashboard.guests.filterCategoryLabel') }}</label>
                            <div class="flex flex-wrap gap-2">
                                <button @click="filters.category = ''"
                                        class="py-1.5 px-3 rounded-xl text-sm border transition-colors"
                                        :class="filters.category === '' ? 'border-[#92A89C] bg-[#92A89C]/10 text-[#73877C] font-medium' : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                                    {{ t('dashboard.guests.filterAll') }}
                                </button>
                                <button v-for="cat in categories" :key="cat"
                                        @click="filters.category = cat"
                                        class="py-1.5 px-3 rounded-xl text-sm border transition-colors"
                                        :class="filters.category === cat ? 'border-[#92A89C] bg-[#92A89C]/10 text-[#73877C] font-medium' : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                                    {{ cat }}
                                </button>
                            </div>
                        </div>

                        <!-- Urutan -->
                        <div>
                            <label class="text-xs font-medium text-stone-500 uppercase tracking-wide mb-2 block">{{ t('dashboard.guests.filterSortLabel') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="opt in [
                                    {v:'newest',l:t('dashboard.guests.sortNewest')},
                                    {v:'name_asc',l:t('dashboard.guests.sortNameAsc')},
                                    {v:'not_sent',l:t('dashboard.guests.sortNotSentFirst')},
                                    {v:'not_rsvp',l:t('dashboard.guests.sortNotRsvpFirst')}
                                ]"
                                        :key="opt.v"
                                        @click="sort = opt.v"
                                        class="py-2 px-3 rounded-xl text-sm border transition-colors"
                                        :class="sort === opt.v
                                            ? 'border-[#92A89C] bg-[#92A89C]/10 text-[#73877C] font-medium'
                                            : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                                    {{ opt.l }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-5 pt-4 border-t border-stone-100">
                        <button @click="resetAllFilters; showFilterSheet = false"
                                class="flex-1 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50">
                            {{ t('dashboard.guests.filterReset') }}
                        </button>
                        <button @click="showFilterSheet = false"
                                class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white"
                                style="background-color:#92A89C">
                            {{ t('dashboard.guests.filterApply') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!--  MODAL: Add / Edit Guest                               -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showGuestForm"
                 class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4"
                 @click.self="closeGuestForm">
                <div class="bg-white w-full max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl p-6">
                    <h2 class="text-base font-semibold text-stone-800 mb-4">
                        {{ editingGuest ? t('dashboard.guests.modalEditTitle') : t('dashboard.guests.modalAddTitle') }}
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.formNameLabel') }} <span class="text-red-400">*</span></label>
                            <input v-model="guestForm.name" type="text" :placeholder="t('dashboard.guests.formNamePlaceholder')"
                                   class="w-full border rounded-xl px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"
                                   :class="guestErrors.name ? 'border-red-300' : 'border-stone-200'"/>
                            <p v-if="guestErrors.name" class="text-xs text-red-500 mt-1">{{ guestErrors.name[0] }}</p>
                        </div>

                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.formPhoneLabel') }} <span class="text-red-400">*</span></label>
                            <input v-model="guestForm.phone_number" type="tel" placeholder="08123456789"
                                   class="w-full border rounded-xl px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"
                                   :class="guestErrors.phone_number ? 'border-red-300' : 'border-stone-200'"/>
                            <p v-if="guestErrors.phone_number" class="text-xs text-red-500 mt-1">{{ guestErrors.phone_number[0] }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.formCategoryLabel') }}</label>
                                <input v-model="guestForm.category" type="text" :placeholder="t('dashboard.guests.formCategoryPlaceholder')"
                                       list="category-list"
                                       class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"/>
                                <datalist id="category-list">
                                    <option v-for="cat in suggestedCategories" :key="cat" :value="cat"/>
                                </datalist>
                            </div>
                            <div>
                                <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.formGreetingLabel') }}</label>
                                <input v-model="guestForm.greeting" type="text" :placeholder="t('dashboard.guests.formGreetingPlaceholder')"
                                       class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"/>
                            </div>
                        </div>

                        <div v-if="invitations.length > 0">
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.formInvitationLabel') }}</label>
                            <select v-model="guestForm.invitation_id"
                                    class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                                <option value="">{{ t('dashboard.guests.formInvitationNone') }}</option>
                                <option v-for="inv in invitations" :key="inv.id" :value="inv.id">{{ inv.title }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.formNoteLabel') }}</label>
                            <textarea v-model="guestForm.note" rows="2" :placeholder="t('dashboard.guests.formNotePlaceholder')"
                                      class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 resize-none focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"/>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button @click="closeGuestForm"
                                class="flex-1 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50 transition-colors">
                            {{ t('dashboard.guests.cancelButton') }}
                        </button>
                        <button @click="saveGuest" :disabled="savingGuest"
                                class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                                style="background-color:#92A89C">
                            {{ savingGuest ? t('dashboard.guests.savingButton') : (editingGuest ? t('dashboard.guests.saveButton') : t('dashboard.guests.addButton')) }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!--  MODAL: Delete Confirm                                  -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showDeleteConfirm"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                 @click.self="showDeleteConfirm = null">
                <div class="bg-white w-full max-w-sm rounded-2xl shadow-xl p-6">
                    <h2 class="text-base font-semibold text-stone-800 mb-2">{{ t('dashboard.guests.deleteModalTitle') }}</h2>
                    <p class="text-sm text-stone-500 mb-5">
                        {{ t('dashboard.guests.deleteModalBody', { name: showDeleteConfirm.name }) }}
                    </p>
                    <div class="flex gap-3">
                        <button @click="showDeleteConfirm = null"
                                class="flex-1 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50">{{ t('dashboard.guests.cancelButton') }}</button>
                        <button @click="deleteGuest(showDeleteConfirm)"
                                class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white bg-red-500 hover:bg-red-600 transition-colors">{{ t('dashboard.guests.deleteConfirmButton') }}</button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!--  MODAL: Bulk Delete Confirm                            -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showBulkDeleteConfirm"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                 @click.self="showBulkDeleteConfirm = false">
                <div class="bg-white w-full max-w-sm rounded-2xl shadow-xl p-6">
                    <h2 class="text-base font-semibold text-stone-800 mb-2">{{ t('dashboard.guests.bulkDeleteModalTitle', { n: selected.length }) }}</h2>
                    <p class="text-sm text-stone-500 mb-5">
                        {{ t('dashboard.guests.bulkDeleteModalBody') }}
                    </p>
                    <div class="flex gap-3">
                        <button @click="showBulkDeleteConfirm = false"
                                class="flex-1 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50">{{ t('dashboard.guests.cancelButton') }}</button>
                        <button @click="bulkDelete" :disabled="bulkDeleteLoading"
                                class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white bg-red-500 hover:bg-red-600 disabled:opacity-50 transition-colors">
                            {{ bulkDeleteLoading ? t('dashboard.guests.deletingButton') : t('dashboard.guests.deleteAllButton') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!--  MODAL: Bulk Update Category                           -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showBulkCategoryModal"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                 @click.self="showBulkCategoryModal = false">
                <div class="bg-white w-full max-w-sm rounded-2xl shadow-xl p-6">
                    <h2 class="text-base font-semibold text-stone-800 mb-1">{{ t('dashboard.guests.bulkCategoryModalTitle') }}</h2>
                    <p class="text-sm text-stone-500 mb-4">{{ t('dashboard.guests.bulkCategoryModalBody', { n: selected.length }) }}</p>

                    <input v-model="bulkCategoryValue" type="text" :placeholder="t('dashboard.guests.formCategoryPlaceholder')"
                           list="bulk-category-list"
                           class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30 mb-4"/>
                    <datalist id="bulk-category-list">
                        <option v-for="cat in suggestedCategories" :key="cat" :value="cat"/>
                    </datalist>

                    <div class="flex gap-3">
                        <button @click="showBulkCategoryModal = false"
                                class="flex-1 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50">{{ t('dashboard.guests.cancelButton') }}</button>
                        <button @click="bulkUpdateCategory" :disabled="!bulkCategoryValue || bulkCategoryLoading"
                                class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white disabled:opacity-50 transition-opacity hover:opacity-90"
                                style="background-color:#92A89C">
                            {{ bulkCategoryLoading ? t('dashboard.guests.savingButton') : t('dashboard.guests.saveButton') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!--  MODAL: WhatsApp Template Editor                       -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showTemplateModal"
                 class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4"
                 @click.self="showTemplateModal = false">
                <div class="bg-white w-full max-w-2xl rounded-t-2xl sm:rounded-2xl shadow-xl p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-stone-800">{{ t('dashboard.guests.templateModalTitle') }}</h2>
                        <button @click="showTemplateModal = false" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.templateNameLabel') }}</label>
                                <input v-model="templateForm.name" type="text"
                                       class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"/>
                            </div>
                            <div>
                                <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.templateContentLabel') }}</label>
                                <textarea v-model="templateForm.content" rows="10"
                                          class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 font-mono resize-none focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"
                                          :class="templateErrors.content ? 'border-red-300' : ''"/>
                                <p v-if="templateErrors.content" class="text-xs text-red-500 mt-1">{{ templateErrors.content[0] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-stone-400 mb-2">{{ t('dashboard.guests.templatePlaceholderHint') }}</p>
                                <div class="flex flex-wrap gap-1.5">
                                    <button v-for="ph in ['guest_name','invitation_url','greeting','bride_name','groom_name','event_date','event_time','event_location']"
                                            :key="ph"
                                            @click="insertPlaceholder(ph)"
                                            class="text-xs px-2.5 py-1 rounded-full bg-[#92A89C]/10 text-[#73877C] border border-[#B8C7BF]/50 hover:bg-[#92A89C]/20 transition-colors font-mono">
                                        {{ phLabel(ph) }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-stone-500">{{ t('dashboard.guests.templatePreviewLabel') }}</p>
                                <button @click="previewTemplate" :disabled="previewLoading"
                                        class="text-xs text-[#73877C] underline disabled:opacity-50">
                                    {{ previewLoading ? t('dashboard.guests.templateLoadingPreview') : t('dashboard.guests.templateRefreshPreview') }}
                                </button>
                            </div>
                            <div v-if="templateWarnings.length > 0" class="space-y-1">
                                <p v-for="w in templateWarnings" :key="w"
                                   class="text-xs text-[#73877C] bg-[#92A89C]/10 px-2.5 py-1.5 rounded-lg">{{ w }}</p>
                            </div>
                            <div class="bg-stone-50 rounded-xl p-4 min-h-40 text-sm text-stone-700 whitespace-pre-wrap font-sans border border-stone-100">
                                <span v-if="!templatePreview" class="text-stone-300 text-xs">{{ t('dashboard.guests.templatePreviewEmpty') }}</span>
                                <span v-else>{{ templatePreview }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 mt-5 pt-4 border-t border-stone-100">
                        <button @click="resetTemplate"
                                class="text-xs text-stone-500 underline hover:text-stone-700">{{ t('dashboard.guests.templateResetDefault') }}</button>
                        <div class="ml-auto flex gap-3">
                            <button @click="showTemplateModal = false"
                                    class="px-4 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50">{{ t('dashboard.guests.cancelButton') }}</button>
                            <button @click="saveTemplate" :disabled="savingTemplate"
                                    class="px-6 py-2.5 rounded-xl text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                                    style="background-color:#92A89C">
                                {{ savingTemplate ? t('dashboard.guests.templateSavingButton') : t('dashboard.guests.templateSaveButton') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!--  MODAL: Send WhatsApp                                  -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showSendModal"
                 class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4"
                 @click.self="showSendModal = false">
                <div class="bg-white w-full max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white flex-shrink-0"
                             style="background-color:#25D366">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-stone-800">
                                {{ sendTarget?.send_status === 'sent' || sendTarget?.send_status === 'opened' ? t('dashboard.guests.sendModalTitleResend') : t('dashboard.guests.sendModalTitleSend') }}
                            </h2>
                            <p class="text-xs text-stone-400">{{ sendTarget?.name }} · {{ sendTarget?.normalized_phone ? formatPhone(sendTarget.normalized_phone) : sendTarget?.phone_number }}</p>
                        </div>
                    </div>

                    <div v-if="generating" class="flex justify-center py-8">
                        <div class="w-6 h-6 rounded-full border-2 border-[#92A89C]/50 border-t-[#73877C] animate-spin"/>
                    </div>

                    <template v-else>
                        <div class="bg-stone-50 rounded-xl p-4 text-sm text-stone-700 whitespace-pre-wrap max-h-48 overflow-y-auto mb-4 border border-stone-100">{{ generatedMsg }}</div>

                        <div class="flex gap-2 mb-3">
                            <button @click="copyMessage"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-700 hover:bg-stone-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                {{ t('dashboard.guests.copyButton') }}
                            </button>
                            <button @click="openWhatsApp"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                    style="background-color:#25D366">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                {{ t('dashboard.guests.openWhatsApp') }}
                            </button>
                        </div>

                        <button @click="confirmSent" :disabled="markingSent"
                                class="w-full py-2 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50 disabled:opacity-50 transition-colors">
                            {{ markingSent ? t('dashboard.guests.markingButton') : t('dashboard.guests.markSentButton') }}
                        </button>
                    </template>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!--  MODAL: Import                                         -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showImportModal"
                 class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 px-4"
                 @click.self="showImportModal = false">
                <div class="bg-white w-full max-w-lg rounded-t-2xl sm:rounded-2xl shadow-xl p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-stone-800">{{ t('dashboard.guests.importModalTitle') }}</h2>
                        <button @click="showImportModal = false" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <template v-if="importStep === 'input'">
                        <div class="flex rounded-xl border border-stone-200 overflow-hidden mb-4">
                            <button @click="importMode = 'paste'"
                                    :class="importMode === 'paste' ? 'text-white' : 'text-stone-600 hover:bg-stone-50'"
                                    :style="importMode === 'paste' ? 'background-color:#92A89C' : ''"
                                    class="flex-1 py-2 text-sm font-medium transition-colors">{{ t('dashboard.guests.importPasteTab') }}</button>
                            <button @click="importMode = 'csv'"
                                    :class="importMode === 'csv' ? 'text-white' : 'text-stone-600 hover:bg-stone-50'"
                                    :style="importMode === 'csv' ? 'background-color:#92A89C' : ''"
                                    class="flex-1 py-2 text-sm font-medium transition-colors">{{ t('dashboard.guests.importCsvTab') }}</button>
                        </div>

                        <div v-if="invitations.length > 0" class="mb-4">
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.importInvitationLabel') }}</label>
                            <select v-model="importInvId"
                                    class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30">
                                <option value="">{{ t('dashboard.guests.formInvitationNone') }}</option>
                                <option v-for="inv in invitations" :key="inv.id" :value="inv.id">{{ inv.title }}</option>
                            </select>
                        </div>

                        <template v-if="importMode === 'paste'">
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.importPasteLabel') }}</label>
                            <p class="text-xs text-stone-300 mb-2">{{ t('dashboard.guests.importPasteFormatHint') }}</p>
                            <textarea v-model="importText" rows="8"
                                      placeholder="Bapak Andi,08123456789,Keluarga&#10;Ibu Rina,08234567890,Teman"
                                      class="w-full border border-stone-200 rounded-xl px-3 py-2 text-sm text-stone-800 font-mono resize-none focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30"/>
                        </template>

                        <template v-else>
                            <label class="text-xs text-stone-500 mb-1 block">{{ t('dashboard.guests.importCsvLabel') }}</label>
                            <p class="text-xs text-stone-300 mb-2">{{ t('dashboard.guests.importCsvFormatHint') }}</p>
                            <input type="file" accept=".csv,.txt" @change="onFileChange"
                                   class="w-full text-sm text-stone-700 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:text-white file:cursor-pointer hover:file:opacity-90"
                                   style="--file-bg:#92A89C"/>
                        </template>

                        <div class="flex gap-3 mt-5">
                            <button @click="showImportModal = false"
                                    class="flex-1 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50">{{ t('dashboard.guests.cancelButton') }}</button>
                            <button @click="previewImport" :disabled="importPreviewing || (!importText && !importFile)"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                                    style="background-color:#92A89C">
                                {{ importPreviewing ? t('dashboard.guests.importProcessingButton') : t('dashboard.guests.importPreviewButton') }}
                            </button>
                        </div>
                    </template>

                    <template v-else-if="importStep === 'preview'">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="text-xs px-2.5 py-1 rounded-full bg-green-50 text-green-700 font-medium">
                                {{ t('dashboard.guests.importPreviewValid', { n: importPreview.filter(r => r.valid).length }) }}
                            </span>
                            <span v-if="importPreview.filter(r => !r.valid).length > 0"
                                  class="text-xs px-2.5 py-1 rounded-full bg-red-50 text-red-600 font-medium">
                                {{ t('dashboard.guests.importPreviewError', { n: importPreview.filter(r => !r.valid).length }) }}
                            </span>
                        </div>

                        <div class="border border-stone-100 rounded-xl overflow-hidden max-h-64 overflow-y-auto mb-4">
                            <table class="w-full text-xs">
                                <thead class="bg-stone-50 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-stone-500 font-medium">{{ t('dashboard.guests.importTableRow') }}</th>
                                        <th class="px-3 py-2 text-left text-stone-500 font-medium">{{ t('dashboard.guests.importTableName') }}</th>
                                        <th class="px-3 py-2 text-left text-stone-500 font-medium">{{ t('dashboard.guests.importTablePhone') }}</th>
                                        <th class="px-3 py-2 text-left text-stone-500 font-medium">{{ t('dashboard.guests.importTableCategory') }}</th>
                                        <th class="px-3 py-2 text-left text-stone-500 font-medium">{{ t('dashboard.guests.importTableStatus') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-50">
                                    <tr v-for="row in importPreview" :key="row.row"
                                        :class="row.valid ? '' : 'bg-red-50'">
                                        <td class="px-3 py-2 text-stone-400">{{ row.row }}</td>
                                        <td class="px-3 py-2 text-stone-700">{{ row.name }}</td>
                                        <td class="px-3 py-2 text-stone-500">{{ row.phone }}</td>
                                        <td class="px-3 py-2 text-stone-500">{{ row.category || '—' }}</td>
                                        <td class="px-3 py-2">
                                            <span v-if="row.valid" class="text-green-600">{{ t('dashboard.guests.importValid') }}</span>
                                            <span v-else class="text-red-500">{{ row.errors.join(', ') }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex gap-3">
                            <button @click="importStep = 'input'"
                                    class="flex-1 py-2.5 rounded-xl border border-stone-200 text-sm text-stone-600 hover:bg-stone-50">{{ t('dashboard.guests.importBackButton') }}</button>
                            <button @click="commitImport"
                                    :disabled="importCommitting || importPreview.filter(r => r.valid).length === 0"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                                    style="background-color:#92A89C">
                                {{ importCommitting ? t('dashboard.guests.importCommittingButton') : t('dashboard.guests.importCommitButton', { n: importPreview.filter(r => r.valid).length }) }}
                            </button>
                        </div>
                    </template>

                    <template v-else>
                        <div class="text-center py-8">
                            <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <p class="font-medium text-stone-800 mb-1">{{ t('dashboard.guests.importSuccessTitle') }}</p>
                            <p class="text-sm text-stone-500">{{ importResult?.message }}</p>
                        </div>
                        <button @click="showImportModal = false"
                                class="w-full py-2.5 rounded-xl text-sm font-medium text-white"
                                style="background-color:#92A89C">{{ t('dashboard.guests.importDoneButton') }}</button>
                    </template>
                </div>
            </div>
        </Transition>

        <!-- ── Toast ──────────────────────────────────────────────── -->
        <Transition name="slide-up">
            <div v-if="toast"
                 class="fixed bottom-20 lg:bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 rounded-xl bg-stone-800 text-white text-sm shadow-lg whitespace-nowrap">
                {{ toast }}
            </div>
        </Transition>

    </DashboardLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.25s; }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateX(-50%) translateY(12px); }

input[type="file"]::file-selector-button {
    background-color: #92A89C;
    color: white;
    border-radius: 12px;
    font-size: 0.875rem;
}
</style>
