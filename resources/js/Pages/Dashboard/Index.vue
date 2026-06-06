<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TemplatePicker from '@/Components/Wizard/TemplatePicker.vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import { useLocale } from '@/Composables/useLocale';
import CountdownHero    from '@/Components/dashboard/widgets/CountdownHero.vue';
import NextActionHero    from '@/Components/dashboard/widgets/NextActionHero.vue';
import QuickStats        from '@/Components/dashboard/widgets/QuickStats.vue';
import ChecklistCard     from '@/Components/dashboard/widgets/ChecklistCard.vue';
import InviteShareCard   from '@/Components/dashboard/widgets/InviteShareCard.vue';
import BudgetDonutCard   from '@/Components/dashboard/widgets/BudgetDonutCard.vue';
import RecentRsvpCard    from '@/Components/dashboard/widgets/RecentRsvpCard.vue';
import VendorLineupCard  from '@/Components/dashboard/widgets/VendorLineupCard.vue';
import BeyondPeekCard    from '@/Components/dashboard/widgets/BeyondPeekCard.vue';
import ActivityFeedCard  from '@/Components/dashboard/widgets/ActivityFeedCard.vue';

const { t, locale } = useLocale();

const props = defineProps({
    stats:             Object,
    recentInvitations: Array,
    activePlan:        Object,
    budgetWidget:      Object,
    checklistWidget:   Object,
    templates:         { type: Array,   default: () => [] },
    canUsePremium:     { type: Boolean, default: false },
    countdown:         { type: Object,  default: null },
    hasWeddingDate:    { type: Boolean, default: false },
    couple:            { type: Object,  default: null },
    recentRsvps:       { type: Array,   default: () => [] },
    inviteShare:       { type: Object,  default: null },
    nextAction:        { type: Object,  default: null },
    vendorWidget:      { type: Array,   default: () => [] },
    activityFeed:      { type: Array,   default: () => [] },
});

// ── Next-action hero "share" action: copy invite link + toast ──────────
const copyToast = ref(false);
async function copyInviteLink() {
    if (!props.inviteShare?.url) return;
    try {
        await navigator.clipboard.writeText(props.inviteShare.url);
        copyToast.value = true;
        setTimeout(() => { copyToast.value = false; }, 2500);
    } catch (_) {}
}

// ── Delete ────────────────────────────────────────────────────────────
const confirmTarget = ref(null);
function confirmDelete(inv) { confirmTarget.value = inv; }
function cancelDelete()     { confirmTarget.value = null; }
function doDelete() {
    if (!confirmTarget.value) return;
    router.delete(route('dashboard.invitations.destroy', confirmTarget.value.id), {
        onFinish: () => { confirmTarget.value = null; },
    });
}

// ── Template picker ───────────────────────────────────────────────────
const pickerTarget = ref(null);
function openPicker(inv) { pickerTarget.value = inv; }
function onTemplateChanged(newTemplate) {
    if (!pickerTarget.value) return;
    pickerTarget.value.template = {
        id:             newTemplate.id,
        name:           newTemplate.name,
        thumbnail_url:  newTemplate.thumbnail_url,
        default_config: newTemplate.default_config ?? {},
    };
    pickerTarget.value = null;
}

// ── Duplicate ─────────────────────────────────────────────────────────
const duplicateTarget  = ref(null);
const isDuplicating    = ref(false);
const duplicateSuccess = ref(null);
const duplicateError   = ref(null);
function confirmDuplicate(inv) { duplicateTarget.value = inv; }
function cancelDuplicate()     { duplicateTarget.value = null; }
async function doDuplicate() {
    if (!duplicateTarget.value) return;
    isDuplicating.value = true;
    try {
        const { data } = await axios.post(
            route('dashboard.invitations.duplicate', duplicateTarget.value.id)
        );
        duplicateTarget.value = null;
        duplicateSuccess.value = { title: data.title, editUrl: data.edit_url };
        router.reload({ only: ['recentInvitations'] });
        setTimeout(() => { duplicateSuccess.value = null; }, 6000);
    } catch (err) {
        const error = err.response?.data?.error;
        duplicateError.value = error === 'invitation_limit_reached'
            ? t('dashboard.index.duplicateError.limitReached')
            : t('dashboard.index.duplicateError.generic');
        duplicateTarget.value = null;
        setTimeout(() => { duplicateError.value = null; }, 5000);
    } finally {
        isDuplicating.value = false;
    }
}


const statusConfig = computed(() => ({
    draft:    { label: t('dashboard.index.status.draft'),    bg: '#F3F4F6', color: '#6B7280' },
    published:{ label: t('dashboard.index.status.published'), bg: '#D1FAE5', color: '#059669' },
    archived: { label: t('dashboard.index.status.archived'), bg: '#FEE2E2', color: '#DC2626' },
}));

const eventTypeLabel = computed(() => ({
    pernikahan: t('dashboard.index.eventType.pernikahan'),
}));

const templateColor = (inv) => inv.template?.default_config?.primary_color ?? '#92A89C';

// ── Countdown ─────────────────────────────────────────────────────────
const showDateModal    = ref(false);
const weddingDateInput = ref(props.countdown?.date ?? '');
const savingDate       = ref(false);

// ── Date picker (mirror of StepAcara calendar) ────────────────────────
const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const MONTHS_EN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DAYS_ID   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
const DAYS_EN   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const calMonths    = computed(() => locale.value === 'id' ? MONTHS_ID : MONTHS_EN);
const calDayNames  = computed(() => locale.value === 'id' ? DAYS_ID : DAYS_EN);

const calToday  = new Date();
const calYear   = ref(calToday.getFullYear());
const calMonth  = ref(calToday.getMonth());

function openDateModal() {
    if (weddingDateInput.value) {
        const [y, m] = weddingDateInput.value.split('-').map(Number);
        calYear.value = y; calMonth.value = m - 1;
    } else {
        calYear.value = calToday.getFullYear(); calMonth.value = calToday.getMonth();
    }
    showDateModal.value = true;
}
function prevCalMonth() {
    if (calMonth.value === 0) { calMonth.value = 11; calYear.value--; } else calMonth.value--;
}
function nextCalMonth() {
    if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; } else calMonth.value++;
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
    const m = String(calMonth.value + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    weddingDateInput.value = `${calYear.value}-${m}-${d}`;
}
function isPickedDay(day) {
    if (!day || !weddingDateInput.value) return false;
    const [y, m, d] = weddingDateInput.value.split('-').map(Number);
    return y === calYear.value && m === calMonth.value + 1 && d === day;
}
function calDisplayDate(dateStr) {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'long', year: 'numeric' });
}

function saveWeddingDate() {
    if (!weddingDateInput.value) return;
    savingDate.value = true;
    router.patch(route('dashboard.wedding-date.update'),
        { wedding_date: weddingDateInput.value },
        {
            preserveScroll: true,
            onFinish: () => {
                savingDate.value = false;
                showDateModal.value = false;
            },
        }
    );
}

</script>

<template>
    <DashboardLayout>
        <template #header>
            <h1 class="text-base font-semibold text-stone-800 truncate">{{ t('dashboard.index.pageTitle') }}</h1>
        </template>

        <div class="w-full space-y-5">
          <CountdownHero :couple="couple" :countdown="countdown" :invite-url="inviteShare?.url ?? ''" @set-date="openDateModal" />

          <NextActionHero :next-action="nextAction" @set-date="openDateModal" @share="copyInviteLink" />

          <QuickStats :stats="stats" :budget-widget="budgetWidget" :checklist-widget="checklistWidget" />

          <InviteShareCard :invite-share="inviteShare" />

          <ChecklistCard :checklist-widget="checklistWidget" :countdown="countdown" />

          <div class="grid gap-5 lg:grid-cols-[1.2fr_1fr]">
            <BudgetDonutCard :budget-widget="budgetWidget" />
            <RecentRsvpCard :recent-rsvps="recentRsvps" />
          </div>

          <div class="grid gap-5 lg:grid-cols-3">
            <VendorLineupCard :vendor-widget="vendorWidget" />
            <BeyondPeekCard />
            <ActivityFeedCard :activity-feed="activityFeed" />
          </div>

          <!-- Recent Invitations (kept — real & useful) -->
          <div>
            <div class="flex items-center justify-between mb-4 px-1">
              <h3 class="text-sm font-semibold" style="color:#3D4A4D;">{{ t('dashboard.index.recentInvitations.title') }}</h3>
              <Link :href="route('dashboard.invitations.index')" class="text-xs font-semibold" style="color:#6F8270;">
                {{ t('dashboard.index.recentInvitations.viewAll') }}
              </Link>
            </div>

            <!-- Empty state -->
            <div v-if="!recentInvitations.length"
                 class="bg-white rounded-2xl border border-stone-100 border-dashed p-12 text-center">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     style="background-color: #EFF2F0">
                    <svg class="w-8 h-8" style="color: #73877C" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-stone-600 mb-1">{{ t('dashboard.index.recentInvitations.emptyTitle') }}</p>
                <p class="text-xs text-stone-400 mb-5">{{ t('dashboard.index.recentInvitations.emptySubtitle') }}</p>
                <Link
                    :href="route('dashboard.templates')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
                    style="background-color: #92A89C"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ t('dashboard.index.recentInvitations.createNow') }}
                </Link>
            </div>

            <!-- Invitation cards -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="inv in recentInvitations"
                    :key="inv.id"
                    class="bg-white rounded-2xl border border-stone-100 shadow-sm overflow-hidden hover:shadow-md transition-all hover:-translate-y-0.5 group"
                >
                    <!-- Template color preview -->
                    <div
                        class="h-28 relative flex items-center justify-center"
                        :style="`background: linear-gradient(135deg, ${templateColor(inv)}22, ${templateColor(inv)}44)`"
                    >
                        <!-- Color swatch dots -->
                        <div class="absolute top-3 right-3 flex gap-1.5">
                            <div class="w-3 h-3 rounded-full opacity-60"
                                 :style="`background-color: ${templateColor(inv)}`"/>
                            <div class="w-3 h-3 rounded-full opacity-40"
                                 :style="`background-color: ${inv.template?.default_config?.secondary_color ?? '#FEFAE0'}`"/>
                            <div class="w-3 h-3 rounded-full opacity-40"
                                 :style="`background-color: ${inv.template?.default_config?.accent_color ?? '#CCD5AE'}`"/>
                        </div>

                        <!-- Mini invitation preview -->
                        <div class="text-center px-4">
                            <div class="w-8 h-px mx-auto mb-2" :style="`background-color: ${templateColor(inv)}`"/>
                            <p class="text-xs font-medium text-stone-500">{{ eventTypeLabel[inv.event_type] }}</p>
                            <p class="text-sm font-semibold text-stone-700 mt-0.5 leading-tight line-clamp-2">{{ inv.title }}</p>
                            <div class="w-8 h-px mx-auto mt-2" :style="`background-color: ${templateColor(inv)}`"/>
                        </div>

                        <!-- Status badge -->
                        <span
                            class="absolute top-3 left-3 px-2 py-0.5 rounded-full text-xs font-semibold"
                            :style="`background-color: ${statusConfig[inv.status].bg}; color: ${statusConfig[inv.status].color}`"
                        >
                            {{ statusConfig[inv.status].label }}
                        </span>
                    </div>

                    <!-- Card body -->
                    <div class="p-4">
                        <p class="text-sm font-semibold text-stone-800 truncate mb-1">{{ inv.title }}</p>
                        <p class="text-xs text-stone-400 mb-3" v-if="inv.template">
                            {{ t('dashboard.index.recentInvitations.template', { name: inv.template.name }) }}
                        </p>

                        <!-- Stats row -->
                        <div class="flex items-center gap-4 text-xs text-stone-400 mb-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{ inv.view_count.toLocaleString('id-ID') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ inv.rsvps_count }} RSVP
                            </span>
                            <span v-if="inv.expires_at" class="ml-auto">
                                {{ t('dashboard.index.recentInvitations.exp', { date: inv.expires_at }) }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-2">
                            <!-- Row 1: primary -->
                            <div class="flex gap-2">
                                <Link
                                    :href="route('dashboard.invitations.customize-v2', inv.id)"
                                    class="flex-1 text-center py-2 rounded-xl text-xs font-semibold border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors"
                                >
                                    {{ t('dashboard.index.recentInvitations.edit') }}
                                </Link>
                                <a
                                    :href="inv.status === 'draft'
                                        ? route('dashboard.invitations.preview', inv.id)
                                        : `/${inv.slug}`"
                                    target="_blank"
                                    class="flex-1 text-center py-2 rounded-xl text-xs font-semibold text-white transition-all hover:opacity-90"
                                    style="background-color: #92A89C"
                                >
                                    {{ inv.status === 'draft' ? t('dashboard.index.recentInvitations.preview') : t('dashboard.index.recentInvitations.view') }}
                                </a>
                            </div>
                            <!-- Row 2: secondary -->
                            <div class="flex gap-2">
                                <Link
                                    :href="route('dashboard.invitations.customize-v2', inv.id)"
                                    class="flex-1 text-center py-2 rounded-xl text-xs font-semibold border border-[#92A89C]/50 text-[#73877C] hover:bg-[#92A89C]/10 transition-colors"
                                    :title="t('dashboard.index.recentInvitations.customizeTitle')"
                                >
                                    {{ t('dashboard.index.recentInvitations.customize') }}
                                </Link>
                                <button
                                    @click="openPicker(inv)"
                                    class="flex-1 text-center py-2 rounded-xl text-xs font-semibold border border-[#B8C7BF] text-[#73877C] hover:bg-[#92A89C]/10 transition-colors"
                                    :title="t('dashboard.index.recentInvitations.templateTitle')"
                                >
                                    {{ t('dashboard.index.recentInvitations.template_btn') }}
                                </button>
                                <button
                                    @click="confirmDuplicate(inv)"
                                    class="px-3 py-2 rounded-xl text-xs font-semibold border border-stone-200 text-stone-500 hover:bg-stone-50 hover:text-stone-700 transition-colors"
                                    :title="t('dashboard.index.recentInvitations.duplicateTitle')"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                                <button
                                    @click="confirmDelete(inv)"
                                    class="px-3 py-2 rounded-xl text-xs font-semibold border border-red-100 text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                    :title="t('dashboard.index.recentInvitations.deleteTitle')"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- "Buat baru" placeholder — always shown as the sole add-invitation CTA -->
                <Link
                    :href="route('dashboard.templates')"
                    class="flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-stone-200 p-8 text-center hover:border-[#92A89C]/50 hover:bg-[#92A89C]/8 transition-all group min-h-[220px]"
                >
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 transition-colors group-hover:bg-[#92A89C]/20"
                         style="background-color: #EFF2F0">
                        <svg class="w-6 h-6" style="color: #92A89C" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-stone-500 group-hover:text-stone-700 transition-colors">
                        {{ t('dashboard.index.recentInvitations.createNewCard') }}
                    </p>
                </Link>
            </div>
          </div>
        </div>

        <!-- Set Wedding Date Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDateModal"
                     class="fixed inset-0 z-[70] flex items-center justify-center p-4"
                     style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px)"
                     @click.self="showDateModal = false">
                    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6">
                        <h3 class="text-base font-semibold text-stone-800 mb-1">{{ t('dashboard.index.countdown.setDate') }}</h3>
                        <p class="text-sm text-stone-500 mb-4">{{ t('dashboard.index.countdown.setDateHint') }}</p>

                        <!-- Calendar picker -->
                        <div class="rounded-2xl border border-stone-200 overflow-hidden mb-4">
                            <div class="flex items-center justify-between px-3 py-2.5">
                                <button type="button" @click="prevCalMonth"
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-stone-500 hover:bg-stone-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <span class="text-sm font-semibold text-stone-700">{{ calMonths[calMonth] }} {{ calYear }}</span>
                                <button type="button" @click="nextCalMonth"
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-stone-500 hover:bg-stone-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-7 px-2">
                                <div v-for="d in calDayNames" :key="d"
                                     class="text-center text-xs font-semibold py-1"
                                     :class="(locale === 'id' ? d === 'Min' : d === 'Sun') ? 'text-rose-400' : 'text-stone-400'">{{ d }}</div>
                            </div>
                            <div class="grid grid-cols-7 px-2 pb-2 gap-y-1">
                                <div v-for="(day, i) in calDays" :key="i" class="flex items-center justify-center aspect-square">
                                    <button v-if="day" type="button" @click="pickDay(day)"
                                            class="w-9 h-9 rounded-full text-sm font-medium transition-all"
                                            :class="isPickedDay(day) ? 'text-white font-bold shadow-sm' : 'text-stone-700 hover:bg-[#92A89C]/10 active:bg-[#92A89C]/20'"
                                            :style="isPickedDay(day) ? 'background-color:#92A89C' : ''">
                                        {{ day }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button @click="showDateModal = false"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-medium border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors cursor-pointer">
                                {{ t('common.cancel') }}
                            </button>
                            <button @click="saveWeddingDate"
                                    :disabled="savingDate || !weddingDateInput"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 disabled:opacity-60 cursor-pointer"
                                    style="background-color: #92A89C">
                                <span v-if="savingDate" class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ t('dashboard.index.countdown.saving') }}
                                </span>
                                <span v-else>{{ t('dashboard.index.countdown.setDateCta') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Template Picker -->
        <Teleport to="body">
            <TemplatePicker
                v-if="pickerTarget"
                :invitation-id="pickerTarget.id"
                :current-template-id="pickerTarget.template?.id ?? ''"
                :templates="templates"
                :can-use-premium="canUsePremium"
                :invitation-status="pickerTarget.status"
                @changed="onTemplateChanged"
                @close="pickerTarget = null"
            />
        </Teleport>

        <!-- Delete confirm modal -->
        <Transition name="fade">
            <div v-if="confirmTarget"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
                 @click.self="cancelDelete">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-800 text-center mb-1">{{ t('dashboard.index.deleteModal.title') }}</h3>
                    <p class="text-sm text-stone-500 text-center mb-6">
                        "<span class="font-medium text-stone-700">{{ confirmTarget.title || t('dashboard.index.deleteModal.untitled') }}</span>"
                        {{ t('dashboard.index.deleteModal.willBeDeleted') }}
                    </p>
                    <div class="flex gap-3">
                        <button @click="cancelDelete"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors">
                            {{ t('dashboard.index.deleteModal.cancel') }}
                        </button>
                        <button @click="doDelete"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold bg-red-500 text-white hover:bg-red-600 transition-colors">
                            {{ t('dashboard.index.deleteModal.confirm') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Duplicate confirm modal -->
        <Transition name="fade">
            <div v-if="duplicateTarget"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
                 @click.self="cancelDuplicate">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <div class="w-12 h-12 rounded-2xl bg-[#92A89C]/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" style="color: #92A89C" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-800 text-center mb-1">{{ t('dashboard.index.duplicateModal.title') }}</h3>
                    <p class="text-sm text-stone-500 text-center mb-6">
                        {{ t('dashboard.index.duplicateModal.body', { title: duplicateTarget.title || t('dashboard.index.deleteModal.untitled') }) }}
                    </p>
                    <div class="flex gap-3">
                        <button @click="cancelDuplicate"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors">
                            {{ t('dashboard.index.duplicateModal.cancel') }}
                        </button>
                        <button @click="doDuplicate" :disabled="isDuplicating"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-60"
                                style="background-color: #92A89C">
                            <span v-if="isDuplicating" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                {{ t('dashboard.index.duplicateModal.duplicating') }}
                            </span>
                            <span v-else>{{ t('dashboard.index.duplicateModal.confirm') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Duplicate success toast -->
        <Transition name="toast">
            <div v-if="duplicateSuccess"
                 class="fixed bottom-20 lg:bottom-6 right-6 z-50 bg-white rounded-2xl shadow-xl border border-stone-100 p-4 flex items-start gap-3 max-w-xs">
                <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-stone-800">{{ t('dashboard.index.duplicateSuccess.message') }}</p>
                    <a :href="duplicateSuccess.editUrl" class="text-xs font-medium hover:underline" style="color: #92A89C">
                        {{ t('dashboard.index.duplicateSuccess.open') }}
                    </a>
                </div>
                <button @click="duplicateSuccess = null" class="text-stone-300 hover:text-stone-500 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </Transition>

        <!-- Invite link copied toast (from NextActionHero share) -->
        <Transition name="toast">
            <div v-if="copyToast"
                 class="fixed bottom-20 lg:bottom-6 right-6 z-50 bg-white rounded-2xl shadow-xl border border-stone-100 p-4 flex items-center gap-3 max-w-xs">
                <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-stone-800">{{ t('dashboard.index.nextAction.shareCopied') }}</p>
            </div>
        </Transition>

        <!-- Duplicate error toast -->
        <Transition name="toast">
            <div v-if="duplicateError"
                 class="fixed bottom-20 lg:bottom-6 right-6 z-50 bg-white rounded-2xl shadow-xl border border-red-100 p-4 flex items-start gap-3 max-w-xs">
                <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-stone-700">{{ duplicateError }}</p>
                </div>
                <button @click="duplicateError = null" class="text-stone-300 hover:text-stone-500 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </Transition>
    </DashboardLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.toast-enter-active, .toast-leave-active { transition: all 0.2s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(8px); }

.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.96); }
</style>
