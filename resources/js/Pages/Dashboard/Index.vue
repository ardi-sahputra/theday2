<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TemplatePicker from '@/Components/Wizard/TemplatePicker.vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    stats:             Object,
    recentInvitations: Array,
    activePlan:        Object,
    budgetWidget:      Object,
    checklistWidget:   Object,
    templates:         { type: Array,   default: () => [] },
    canUsePremium:     { type: Boolean, default: false },
});

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

const tips = computed(() => [
    { icon: '🎨', title: t('dashboard.index.tips.tip1.title'), desc: t('dashboard.index.tips.tip1.desc') },
    { icon: '✏️', title: t('dashboard.index.tips.tip2.title'), desc: t('dashboard.index.tips.tip2.desc') },
    { icon: '🚀', title: t('dashboard.index.tips.tip3.title'), desc: t('dashboard.index.tips.tip3.desc') },
]);

const templateColor = (inv) => inv.template?.default_config?.primary_color ?? '#92A89C';

const priorityDot = {
    high:   'bg-red-400',
    medium: 'bg-[#92A89C]',
    low:    'bg-stone-300',
};
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h1 class="text-base font-semibold text-stone-800 truncate">{{ t('dashboard.index.pageTitle') }}</h1>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">

            <!-- ── Hero Summary ─────────────────────────────────── -->
            <section class="relative overflow-hidden rounded-3xl border border-brand-primary-soft/40 bg-gradient-to-br from-brand-primary-soft/[0.15] via-brand-bg to-white p-5 sm:p-8">

                <!-- decorative ring -->
                <span aria-hidden="true"
                      class="absolute -top-16 -right-16 w-48 h-48 rounded-full border border-brand-primary/15"/>
                <span aria-hidden="true"
                      class="absolute -bottom-20 -left-10 w-40 h-40 rounded-full border border-brand-primary/10"/>

                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5 sm:mb-6">
                    <div class="min-w-0">
                        <!-- Plan chip: Free = upgrade nudge, Premium = subtle gold badge -->
                        <Link v-if="activePlan.slug !== 'premium'"
                              :href="route('dashboard.paket')"
                              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/80 border border-brand-primary-soft/40 mb-2 hover:border-brand-premium/40 transition-colors group cursor-pointer">
                            <span class="w-1.5 h-1.5 rounded-full bg-stone-400 group-hover:bg-brand-premium transition-colors"/>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-stone-500 group-hover:text-brand-premium transition-colors">
                                {{ activePlan.name }} · {{ t('dashboard.index.upgradeNudge') }}
                            </span>
                            <svg class="w-3 h-3 text-stone-400 group-hover:text-brand-premium transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                        <div v-else
                             class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-premium/12 border border-brand-premium/30 mb-2">
                            <svg class="w-3 h-3 text-brand-premium" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l2.39 7.36H22l-6.18 4.49L18.21 22 12 17.27 5.79 22l2.39-8.15L2 9.36h7.61z"/>
                            </svg>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-brand-premium">
                                {{ activePlan.name }}
                            </span>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-semibold text-brand-text leading-tight"
                            style="font-family: 'Playfair Display', serif">
                            {{ t('dashboard.index.greeting') }}
                        </h2>
                        <p class="text-sm text-stone-500 mt-1">{{ t('dashboard.index.greetingSubtitle') }}</p>
                    </div>
                </div>

                <!-- inline stats -->
                <div class="relative grid grid-cols-3 gap-px rounded-2xl bg-brand-primary-soft/30 overflow-hidden">
                    <div class="bg-white/90 backdrop-blur-sm p-3 sm:p-4 text-center">
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-brand-primary-hover/80">
                            {{ t('dashboard.index.stats.totalInvitations') }}
                        </p>
                        <p class="text-xl sm:text-2xl font-bold text-brand-text mt-1 tabular-nums">{{ stats.total_invitations }}</p>
                        <p class="text-[10px] text-stone-400 mt-0.5 hidden sm:block">
                            {{ t('dashboard.index.stats.totalInvitationsSub', { draft: stats.draft_count, published: stats.published_count }) }}
                        </p>
                    </div>
                    <div class="bg-white/90 backdrop-blur-sm p-3 sm:p-4 text-center">
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-brand-primary-hover/80">
                            {{ t('dashboard.index.stats.totalViews') }}
                        </p>
                        <p class="text-xl sm:text-2xl font-bold text-brand-text mt-1 tabular-nums">{{ stats.total_views.toLocaleString('id-ID') }}</p>
                        <p class="text-[10px] text-stone-400 mt-0.5 hidden sm:block">
                            {{ t('dashboard.index.stats.totalViewsSub') }}
                        </p>
                    </div>
                    <div class="bg-white/90 backdrop-blur-sm p-3 sm:p-4 text-center">
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-brand-primary-hover/80">
                            {{ t('dashboard.index.stats.totalRsvp') }}
                        </p>
                        <p class="text-xl sm:text-2xl font-bold text-brand-text mt-1 tabular-nums">{{ stats.total_rsvps.toLocaleString('id-ID') }}</p>
                        <p class="text-[10px] text-stone-400 mt-0.5 hidden sm:block">
                            {{ t('dashboard.index.stats.totalRsvpSub') }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ── Budget Widget ──────────────────────────────────── -->
            <Link
                v-if="budgetWidget"
                :href="route('dashboard.budget-planner.index')"
                class="block bg-white rounded-2xl border border-stone-100 shadow-sm p-5 hover:shadow-md transition-shadow"
            >
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background-color: #EFF2F0">
                            <svg class="w-4 h-4" style="color: #73877C" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-stone-700">{{ t('dashboard.index.budget.title') }}</p>
                    </div>
                    <span class="text-xs font-medium" style="color: #92A89C">{{ t('dashboard.index.budget.viewLink') }}</span>
                </div>

                <template v-if="budgetWidget.has_budget">
                    <div class="flex items-end justify-between mb-2">
                        <div>
                            <p class="text-xs text-stone-400">{{ t('dashboard.index.budget.used') }}</p>
                            <p class="text-xl font-bold text-stone-800">{{ budgetWidget.formatted.total_actual }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-stone-400">{{ t('dashboard.index.budget.from') }}</p>
                            <p class="text-sm font-semibold text-stone-600">{{ budgetWidget.formatted.total_budget }}</p>
                        </div>
                    </div>
                    <div class="h-2 bg-stone-100 rounded-full overflow-hidden mb-2">
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            :style="{
                                width: (budgetWidget.usage_percentage ?? 0) + '%',
                                backgroundColor: budgetWidget.is_total_overbudget ? '#F87171' : (budgetWidget.usage_percentage >= 80 ? '#92A89C' : '#92A89C'),
                            }"
                        />
                    </div>
                    <div class="flex items-center justify-between text-xs text-stone-400">
                        <span>{{ t('dashboard.index.budget.usagePercent', { percent: budgetWidget.usage_percentage ?? 0 }) }}</span>
                        <span v-if="budgetWidget.overbudget_categories_count > 0" class="text-[#73877C] font-medium">
                            {{ t('dashboard.index.budget.overbudgetCategories', { count: budgetWidget.overbudget_categories_count }) }}
                        </span>
                    </div>
                </template>
                <template v-else>
                    <p class="text-sm text-stone-500">{{ t('dashboard.index.budget.empty') }}</p>
                </template>
            </Link>

            <!-- ── Checklist Widget ───────────────────────────────── -->
            <Link
                v-if="checklistWidget"
                :href="route('dashboard.checklist.index')"
                class="block bg-white rounded-2xl border border-stone-100 shadow-sm p-5 hover:shadow-md transition-shadow"
            >
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background-color: #F0FDF4">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-stone-700">{{ t('dashboard.index.checklist.title') }}</p>
                    </div>
                    <span class="text-xs font-medium" style="color: #92A89C">{{ t('dashboard.index.checklist.viewLink') }}</span>
                </div>

                <template v-if="!checklistWidget.initialized">
                    <p class="text-sm text-stone-500">{{ t('dashboard.index.checklist.notInitialized') }}</p>
                </template>
                <template v-else-if="checklistWidget.total === 0">
                    <p class="text-sm text-stone-500">{{ t('dashboard.index.checklist.noTasks') }}</p>
                </template>
                <template v-else>
                    <!-- Progress bar -->
                    <div class="flex items-end justify-between mb-2">
                        <div>
                            <p class="text-xs text-stone-400">{{ t('dashboard.index.checklist.done') }}</p>
                            <p class="text-xl font-bold text-stone-800">
                                {{ checklistWidget.done }}
                                <span class="text-sm font-normal text-stone-400">/ {{ checklistWidget.total }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold" :class="checklistWidget.progress === 100 ? 'text-emerald-500' : 'text-stone-700'">
                                {{ checklistWidget.progress }}%
                            </p>
                        </div>
                    </div>
                    <div class="h-2 bg-stone-100 rounded-full overflow-hidden mb-3">
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            :style="{
                                width: checklistWidget.progress + '%',
                                backgroundColor: checklistWidget.progress === 100 ? '#34D399' : '#92A89C',
                            }"
                        />
                    </div>

                    <!-- Upcoming tasks -->
                    <div v-if="checklistWidget.upcoming_tasks?.length" class="space-y-1.5">
                        <p class="text-xs font-medium text-stone-400 mb-2">{{ t('dashboard.index.checklist.upcomingTasks') }}</p>
                        <div
                            v-for="task in checklistWidget.upcoming_tasks"
                            :key="task.id"
                            class="flex items-center gap-2 text-xs"
                        >
                            <span :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', priorityDot[task.priority]]"/>
                            <span class="flex-1 text-stone-600 truncate">{{ task.title }}</span>
                            <span :class="['flex-shrink-0 font-medium', task.is_overdue ? 'text-rose-500' : 'text-stone-400']">
                                {{ task.due_date_label }}
                            </span>
                        </div>
                    </div>
                    <p v-else-if="checklistWidget.todo === 0" class="text-xs text-emerald-600 font-medium">
                        {{ t('dashboard.index.checklist.allDone') }}
                    </p>
                    <p v-else class="text-xs text-stone-400">
                        {{ t('dashboard.index.checklist.todoRemaining', { count: checklistWidget.todo }) }}
                    </p>
                </template>
            </Link>

            <!-- ── Recent Invitations ─────────────────────────────── -->
            <div>
                <div class="flex items-center justify-between mb-4 px-5">
                    <h3 class="text-sm font-semibold text-stone-700">{{ t('dashboard.index.recentInvitations.title') }}</h3>
                    <Link :href="route('dashboard.invitations.index')"
                          class="text-xs font-medium transition-colors hover:opacity-80"
                          style="color: #92A89C">
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
                                        :href="route('dashboard.invitations.edit', inv.id)"
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
                                        :href="route('dashboard.invitations.customize', inv.id)"
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

            <!-- ── Quick tips (only shown when 0 invitations) ─────── -->
            <div v-if="!recentInvitations.length"
                 class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    v-for="(tip, i) in tips"
                    :key="i"
                    class="bg-white rounded-2xl p-5 border border-stone-100 shadow-sm"
                >
                    <div class="text-2xl mb-3">{{ tip.icon }}</div>
                    <p class="text-sm font-semibold text-stone-700 mb-1">{{ i + 1 }}. {{ tip.title }}</p>
                    <p class="text-xs text-stone-400 leading-relaxed">{{ tip.desc }}</p>
                </div>
            </div>

        </div>

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
</style>
