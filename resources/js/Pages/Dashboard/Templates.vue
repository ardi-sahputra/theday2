<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    categories:    Array,
    templates:     Array,
    filters:       Object,
    canUsePremium: { type: Boolean, default: false },
});

// ── Filter state ──────────────────────────────────────────────────
const activeCategory = ref(props.filters.category);
const activeTier     = ref(props.filters.tier);

// Debounced router push when filters change
watch([activeCategory, activeTier], ([cat, tier]) => {
    router.get(
        route('dashboard.templates'),
        { category: cat === 'all' ? undefined : cat, tier: tier === 'all' ? undefined : tier },
        { preserveState: true, preserveScroll: true, replace: true }
    );
});

// ── Template selection ────────────────────────────────────────────
const creatingId = ref(null);
const canCreateInvitation = computed(() => usePage().props.can_create_invitation ?? true);

const isPremium = computed(() => usePage().props.auth?.subscription?.plan_slug === 'premium');

// ── Limit modal (invitation quota reached) ────────────────────────
const showLimitModal    = ref(false);
const addonLoading      = ref(false);
const addonError        = ref('');

const buyAddon = async () => {
    addonLoading.value = true;
    addonError.value   = '';
    try {
        const { data } = await axios.post(route('dashboard.addons.checkout'), { quantity: 1 });
        window.location.href = data.payment_url;
    } catch (err) {
        addonError.value = err?.response?.data?.error ?? t('dashboard.templates.errorProcessing');
        addonLoading.value = false;
    }
};

// ── Upgrade modal (premium template clicked by free user) ─────────
const upgradeTemplate = ref(null); // template that triggered the modal

function openUpgradeModal(template) {
    upgradeTemplate.value = template;
}
function closeUpgradeModal() {
    upgradeTemplate.value = null;
}

// ── Preview modal ─────────────────────────────────────────────────
const previewTemplate = ref(null);
const showPreview     = ref(false);

const openPreview = (template) => {
    previewTemplate.value = template;
    showPreview.value = true;
};
const closePreview = () => { showPreview.value = false; };

// Close on Escape
const onKeydown = (e) => { if (e.key === 'Escape') closePreview(); };
if (typeof window !== 'undefined') {
    window.addEventListener('keydown', onKeydown);
}

// ── Helpers ───────────────────────────────────────────────────────
const tierConfig = computed(() => ({
    free:    { label: t('dashboard.templates.tierFree'),    bg: '#D1FAE5', color: '#065F46' },
    premium: { label: t('dashboard.templates.tierPremium'), bg: '#EFF2F0', color: '#2C2417' },
}));

const primaryColor   = (t) => t.default_config?.primary_color   ?? '#92A89C';
const secondaryColor = (t) => t.default_config?.secondary_color ?? '#FEFAE0';
const accentColor    = (t) => t.default_config?.accent_color    ?? '#CCD5AE';
const fontTitle      = (t) => t.default_config?.font_title      ?? 'serif';

const useTemplate = (template) => {
    if (creatingId.value) return;
    // Block if invitation quota reached — show limit modal
    if (!canCreateInvitation.value) {
        showLimitModal.value = true;
        return;
    }
    // Block free users from using premium templates — show upgrade modal instead
    if (template.tier === 'premium' && !props.canUsePremium) {
        openUpgradeModal(template);
        return;
    }
    creatingId.value = template.id;
    router.post(
        route('dashboard.invitations.from-template'),
        { template_id: template.id },
        { onFinish: () => { creatingId.value = null; } }
    );
};

const allCategories = computed(() => [
    { slug: 'all', name: t('dashboard.templates.categoryAll') },
    ...props.categories,
]);

const tiers = computed(() => [
    { value: 'all',     label: t('dashboard.templates.tierAll') },
    { value: 'free',    label: t('dashboard.templates.tierFree') },
    { value: 'premium', label: t('dashboard.templates.tierPremium') },
]);

const filteredCount = computed(() => props.templates.length);
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h1 class="text-base font-semibold text-stone-800">{{ t('dashboard.templates.pageTitle') }}</h1>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">

            <!-- ── Page heading ─────────────────────────────────── -->
            <div>
                <h2 class="text-xl font-semibold text-stone-800">{{ t('dashboard.templates.heading') }}</h2>
                <p class="text-sm text-stone-400 mt-1">
                    {{ t('dashboard.templates.subheading') }}
                </p>
            </div>

            <!-- ── Filters ──────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">

                <!-- Category tabs -->
                <div class="flex items-center gap-1 bg-stone-100 rounded-xl p-1">
                    <button
                        v-for="cat in allCategories"
                        :key="cat.slug"
                        @click="activeCategory = cat.slug"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150',
                            activeCategory === cat.slug
                                ? 'bg-white text-stone-800 shadow-sm'
                                : 'text-stone-500 hover:text-stone-700',
                        ]"
                    >
                        {{ cat.name }}
                    </button>
                </div>

                <!-- Tier pills -->
                <div class="flex items-center gap-2 sm:ml-auto">
                    <span class="text-xs text-stone-400 font-medium">{{ t('dashboard.templates.tierLabel') }}</span>
                    <div class="flex items-center gap-1.5">
                        <button
                            v-for="tier in tiers"
                            :key="tier.value"
                            @click="activeTier = tier.value"
                            :class="[
                                'px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 border',
                                activeTier === tier.value
                                    ? 'text-white border-transparent shadow-sm'
                                    : 'bg-white text-stone-500 border-stone-200 hover:border-stone-300',
                            ]"
                            :style="activeTier === tier.value ? 'background-color: #92A89C; border-color: #92A89C' : ''"
                        >
                            {{ tier.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Result count -->
            <p class="text-xs text-stone-400">
                {{ t('dashboard.templates.showingCount', { count: filteredCount }) }}
            </p>

            <!-- ── Template Grid ─────────────────────────────────── -->
            <div v-if="templates.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="template in templates"
                    :key="template.id"
                    class="bg-white rounded-2xl border border-stone-100 shadow-sm overflow-hidden group hover:shadow-lg hover:-translate-y-1 transition-all duration-200"
                >
                    <!-- Thumbnail / Color preview -->
                    <div
                        class="relative overflow-hidden"
                        style="height: 200px"
                        :style="`background: linear-gradient(160deg, ${secondaryColor(template)}, ${primaryColor(template)}33)`"
                    >
                        <!-- Thumbnail image if available -->
                        <img
                            v-if="template.thumbnail_url"
                            :src="template.thumbnail_url"
                            :alt="template.name"
                            class="w-full h-full object-cover"
                        />

                        <!-- Generated preview (no thumbnail) -->
                        <template v-else>
                            <!-- Decorative circles -->
                            <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full opacity-20"
                                 :style="`background-color: ${primaryColor(template)}`"/>
                            <div class="absolute -bottom-6 -left-6 w-24 h-24 rounded-full opacity-15"
                                 :style="`background-color: ${accentColor(template)}`"/>

                            <!-- Mock invitation content -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6">
                                <div class="w-10 h-px mb-3" :style="`background-color: ${primaryColor(template)}`"/>
                                <p class="text-xs tracking-widest uppercase font-medium mb-1"
                                   :style="`color: ${primaryColor(template)}; font-family: '${fontTitle(template)}', serif`">
                                    {{ template.category.name }}
                                </p>
                                <p class="text-lg font-semibold text-stone-700 leading-tight"
                                   :style="`font-family: '${fontTitle(template)}', serif`">
                                    {{ t('dashboard.templates.mockNames') }}
                                </p>
                                <div class="flex items-center gap-2 my-2">
                                    <div class="w-6 h-px" :style="`background-color: ${primaryColor(template)}`"/>
                                    <div class="w-1.5 h-1.5 rounded-full" :style="`background-color: ${primaryColor(template)}`"/>
                                    <div class="w-6 h-px" :style="`background-color: ${primaryColor(template)}`"/>
                                </div>
                                <p class="text-xs text-stone-500">{{ t('dashboard.templates.mockDate') }}</p>
                                <div class="w-10 h-px mt-3" :style="`background-color: ${primaryColor(template)}`"/>
                            </div>
                        </template>

                        <!-- Tier badge -->
                        <span
                            class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-xs font-semibold"
                            :style="`background-color: ${tierConfig[template.tier].bg}; color: ${tierConfig[template.tier].color}`"
                        >
                            {{ tierConfig[template.tier].label }}
                        </span>

                        <!-- Color swatches -->
                        <div class="absolute top-3 right-3 flex gap-1.5">
                            <div class="w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm"
                                 :style="`background-color: ${primaryColor(template)}`"/>
                            <div class="w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm"
                                 :style="`background-color: ${accentColor(template)}`"/>
                        </div>

                        <!-- Hover overlay -->
                        <div class="absolute inset-0 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-200"
                             style="background: rgba(0,0,0,0.4); backdrop-filter: blur(2px)">
                            <button
                                @click="openPreview(template)"
                                class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white text-stone-800 text-xs font-semibold shadow-md hover:bg-stone-50 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{ t('dashboard.templates.preview') }}
                            </button>
                            <!-- Gunakan / lock button -->
                            <button
                                v-if="template.tier === 'free' || canUsePremium"
                                @click="useTemplate(template)"
                                :disabled="!!creatingId"
                                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-xs font-semibold shadow-md transition-all hover:opacity-90 disabled:opacity-60"
                                :style="`background-color: ${primaryColor(template)}`"
                            >
                                <svg v-if="creatingId === template.id" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ t('dashboard.templates.use') }}
                            </button>
                            <!-- Free user + premium template: show upgrade button -->
                            <button
                                v-else
                                @click="openUpgradeModal(template)"
                                class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-stone-800 text-[#C4D0C9] text-xs font-semibold shadow-md hover:bg-stone-700 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ t('dashboard.templates.premiumBadge') }}
                            </button>
                        </div>
                    </div>

                    <!-- Card footer -->
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <p class="text-sm font-semibold text-stone-800 leading-tight">{{ template.name }}</p>
                            <span class="text-xs text-stone-400 flex-shrink-0">{{ template.category.name }}</span>
                        </div>
                        <p v-if="template.description" class="text-xs text-stone-400 leading-relaxed line-clamp-2 mb-3">
                            {{ template.description }}
                        </p>

                        <div class="flex gap-2">
                            <button
                                @click="openPreview(template)"
                                class="flex-1 py-2 rounded-xl text-xs font-medium border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors"
                            >
                                {{ t('dashboard.templates.preview') }}
                            </button>
                            <!-- Free template OR premium user: direct use -->
                            <button
                                v-if="template.tier === 'free' || canUsePremium"
                                @click="useTemplate(template)"
                                :disabled="!!creatingId"
                                class="flex-1 py-2 rounded-xl text-xs font-semibold text-white transition-all hover:opacity-90 disabled:opacity-60 flex items-center justify-center gap-1.5"
                                :style="`background-color: ${primaryColor(template)}`"
                            >
                                <svg v-if="creatingId === template.id" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                {{ creatingId === template.id ? t('dashboard.templates.creating') : t('dashboard.templates.useTemplate') }}
                            </button>
                            <!-- Premium template + free user: upgrade CTA -->
                            <button
                                v-else
                                @click="openUpgradeModal(template)"
                                class="flex-1 py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 bg-stone-800 text-[#C4D0C9] hover:bg-stone-700 transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ t('dashboard.templates.upgrade') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Empty state ───────────────────────────────────── -->
            <div v-else class="py-24 text-center">
                <div class="text-5xl mb-4">🎨</div>
                <p class="text-sm font-medium text-stone-600 mb-1">{{ t('dashboard.templates.emptyTitle') }}</p>
                <p class="text-xs text-stone-400">{{ t('dashboard.templates.emptySubtitle') }}</p>
                <button
                    @click="activeCategory = 'all'; activeTier = 'all'"
                    class="mt-5 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
                    style="background-color: #92A89C"
                >
                    {{ t('dashboard.templates.resetFilter') }}
                </button>
            </div>

        </div>

        <!-- ── Preview Modal ──────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showPreview && previewTemplate"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px)"
                    @click.self="closePreview"
                >
                    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">

                        <!-- Modal header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :style="`background-color: ${tierConfig[previewTemplate.tier].bg}; color: ${tierConfig[previewTemplate.tier].color}`"
                                >
                                    {{ tierConfig[previewTemplate.tier].label }}
                                </span>
                                <h3 class="text-base font-semibold text-stone-800">{{ previewTemplate.name }}</h3>
                                <span class="text-xs text-stone-400">· {{ previewTemplate.category.name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- View full demo (opens new tab) -->
                                <a
                                    :href="`/templates/${previewTemplate.slug}/demo`"
                                    target="_blank"
                                    rel="noopener"
                                    class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border-2 transition-all hover:opacity-80"
                                    :style="`color: ${primaryColor(previewTemplate)}; border-color: ${primaryColor(previewTemplate)}40`"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    {{ t('public.templateModal.viewFullDemo') }}
                                </a>
                                <!-- Premium modal header CTA -->
                                <button
                                    v-if="previewTemplate.tier === 'free' || canUsePremium"
                                    @click="useTemplate(previewTemplate)"
                                    :disabled="!!creatingId"
                                    class="flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 disabled:opacity-60"
                                    :style="`background-color: ${primaryColor(previewTemplate)}`"
                                >
                                    <svg v-if="creatingId === previewTemplate.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ creatingId === previewTemplate.id ? t('dashboard.templates.creating') : t('dashboard.templates.useTemplate') }}
                                </button>
                                <button
                                    v-else
                                    @click="closePreview(); openUpgradeModal(previewTemplate)"
                                    class="flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold bg-stone-800 text-[#C4D0C9] hover:bg-stone-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    {{ t('dashboard.templates.upgradeToPremium') }}
                                </button>
                                <button
                                    @click="closePreview"
                                    class="p-2 rounded-xl text-stone-400 hover:text-stone-600 hover:bg-stone-100 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Preview area: two-panel -->
                        <div class="flex-1 overflow-auto flex flex-col md:flex-row">

                            <!-- Phone mockup preview -->
                            <div
                                class="flex-1 flex items-center justify-center p-8"
                                :style="`background: linear-gradient(135deg, ${secondaryColor(previewTemplate)}, ${primaryColor(previewTemplate)}22)`"
                            >
                                <div class="w-64 bg-white rounded-3xl shadow-2xl overflow-hidden border border-stone-100">
                                    <!-- Phone notch -->
                                    <div class="bg-stone-900 h-6 flex items-center justify-center">
                                        <div class="w-16 h-3 bg-stone-800 rounded-full"/>
                                    </div>
                                    <!-- Invitation preview content -->
                                    <div
                                        class="flex flex-col items-center text-center px-5 py-8"
                                        :style="`background: linear-gradient(160deg, ${secondaryColor(previewTemplate)}, ${primaryColor(previewTemplate)}11); min-height: 420px`"
                                    >
                                        <div class="w-12 h-px mb-4" :style="`background-color: ${primaryColor(previewTemplate)}`"/>
                                        <p class="text-xs tracking-widest uppercase font-medium mb-3"
                                           :style="`color: ${primaryColor(previewTemplate)}`">
                                            {{ previewTemplate.category.name }}
                                        </p>
                                        <p class="text-2xl font-semibold text-stone-700 mb-1 leading-tight"
                                           :style="`font-family: '${fontTitle(previewTemplate)}', serif`">
                                            Rini
                                        </p>
                                        <div class="flex items-center gap-2 my-2">
                                            <div class="w-8 h-px" :style="`background-color: ${primaryColor(previewTemplate)}`"/>
                                            <span class="text-xs" :style="`color: ${primaryColor(previewTemplate)}`">&amp;</span>
                                            <div class="w-8 h-px" :style="`background-color: ${primaryColor(previewTemplate)}`"/>
                                        </div>
                                        <p class="text-2xl font-semibold text-stone-700 mb-5 leading-tight"
                                           :style="`font-family: '${fontTitle(previewTemplate)}', serif`">
                                            Budi
                                        </p>
                                        <div class="w-full rounded-xl px-4 py-3 mb-3 text-left"
                                             :style="`background: ${primaryColor(previewTemplate)}15`">
                                            <p class="text-xs text-stone-400 mb-0.5">{{ t('dashboard.templates.mockDateLabel') }}</p>
                                            <p class="text-xs font-semibold text-stone-700">{{ t('dashboard.templates.mockDate') }}</p>
                                        </div>
                                        <div class="w-full rounded-xl px-4 py-3 text-left"
                                             :style="`background: ${primaryColor(previewTemplate)}15`">
                                            <p class="text-xs text-stone-400 mb-0.5">{{ t('dashboard.templates.mockLocationLabel') }}</p>
                                            <p class="text-xs font-semibold text-stone-700">{{ t('dashboard.templates.mockVenue') }}</p>
                                            <p class="text-xs text-stone-400">{{ t('dashboard.templates.mockCity') }}</p>
                                        </div>
                                        <button
                                            class="mt-5 w-full py-2.5 rounded-xl text-xs font-semibold text-white"
                                            :style="`background-color: ${primaryColor(previewTemplate)}`"
                                        >
                                            {{ t('dashboard.templates.mockConfirmAttendance') }}
                                        </button>
                                        <div class="w-12 h-px mt-5" :style="`background-color: ${primaryColor(previewTemplate)}`"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Template info panel -->
                            <div class="w-full md:w-72 flex-shrink-0 border-t md:border-t-0 md:border-l border-stone-100 p-6 space-y-5">
                                <div>
                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-2">{{ t('dashboard.templates.infoDescription') }}</p>
                                    <p class="text-sm text-stone-600 leading-relaxed">
                                        {{ previewTemplate.description ?? t('dashboard.templates.infoDescriptionFallback') }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-2">{{ t('dashboard.templates.infoPalette') }}</p>
                                    <div class="flex gap-2">
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-10 h-10 rounded-xl shadow-sm border border-stone-100"
                                                 :style="`background-color: ${primaryColor(previewTemplate)}`"/>
                                            <span class="text-xs text-stone-400">{{ t('dashboard.templates.colorPrimary') }}</span>
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-10 h-10 rounded-xl shadow-sm border border-stone-100"
                                                 :style="`background-color: ${secondaryColor(previewTemplate)}`"/>
                                            <span class="text-xs text-stone-400">{{ t('dashboard.templates.colorSecondary') }}</span>
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="w-10 h-10 rounded-xl shadow-sm border border-stone-100"
                                                 :style="`background-color: ${accentColor(previewTemplate)}`"/>
                                            <span class="text-xs text-stone-400">{{ t('dashboard.templates.colorAccent') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-2">{{ t('dashboard.templates.infoTypography') }}</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-stone-400">{{ t('dashboard.templates.typographyTitle') }}</span>
                                            <span class="font-medium text-stone-700">{{ previewTemplate.default_config?.font_title }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-stone-400">{{ t('dashboard.templates.typographyBody') }}</span>
                                            <span class="font-medium text-stone-700">{{ previewTemplate.default_config?.font_body }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-stone-400">{{ t('dashboard.templates.typographyAnimation') }}</span>
                                            <span class="font-medium text-stone-700">{{ previewTemplate.default_config?.animation }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-2">{{ t('dashboard.templates.infoFeatures') }}</p>
                                    <ul class="space-y-1.5">
                                        <li v-for="feat in [
                                                t('dashboard.templates.featureMobile'),
                                                t('dashboard.templates.featureMusic'),
                                                t('dashboard.templates.featureRsvp'),
                                                t('dashboard.templates.featureMap'),
                                                t('dashboard.templates.featureGallery'),
                                            ]"
                                            :key="feat"
                                            class="flex items-center gap-2 text-xs text-stone-600">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" :style="`color: ${primaryColor(previewTemplate)}`"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ feat }}
                                        </li>
                                    </ul>
                                </div>

                                <!-- View full demo (mobile-friendly fallback, header hidden on sm:) -->
                                <a
                                    :href="`/templates/${previewTemplate.slug}/demo`"
                                    target="_blank"
                                    rel="noopener"
                                    class="sm:hidden w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold border-2 transition-all hover:opacity-80 mb-2"
                                    :style="`color: ${primaryColor(previewTemplate)}; border-color: ${primaryColor(previewTemplate)}40`"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    {{ t('public.templateModal.viewFullDemo') }}
                                </a>
                                <!-- Sidebar CTA -->
                                <button
                                    v-if="previewTemplate.tier === 'free' || canUsePremium"
                                    @click="useTemplate(previewTemplate)"
                                    :disabled="!!creatingId"
                                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 disabled:opacity-60"
                                    :style="`background-color: ${primaryColor(previewTemplate)}`"
                                >
                                    <svg v-if="creatingId === previewTemplate.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ creatingId === previewTemplate.id ? t('dashboard.templates.creating') : t('dashboard.templates.useTemplateThis') }}
                                </button>
                                <button
                                    v-else
                                    @click="closePreview(); openUpgradeModal(previewTemplate)"
                                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold bg-stone-800 text-[#C4D0C9] hover:bg-stone-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    {{ t('dashboard.templates.upgradeToPremium') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        <!-- ── Upgrade Modal ──────────────────────────────────────── -->
        <Transition name="modal">
            <div
                v-if="upgradeTemplate"
                class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-4"
                style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px)"
                @click.self="closeUpgradeModal"
            >
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">

                    <!-- Template thumbnail strip -->
                    <div
                        class="relative h-36 overflow-hidden"
                        :style="`background: linear-gradient(160deg, ${secondaryColor(upgradeTemplate)}, ${primaryColor(upgradeTemplate)}55)`"
                    >
                        <img
                            v-if="upgradeTemplate.thumbnail_url"
                            :src="upgradeTemplate.thumbnail_url"
                            :alt="upgradeTemplate.name"
                            class="w-full h-full object-cover object-top opacity-80"
                        />
                        <!-- Blur overlay -->
                        <div class="absolute inset-0" style="background: rgba(0,0,0,0.25); backdrop-filter: blur(1px)"/>
                        <!-- Crown badge center -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 16L3 5l5.5 5L12 2l3.5 8L21 5l-2 11H5zm2 4h10v-2H7v2z"/>
                                </svg>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-semibold tracking-wide">
                                {{ t('dashboard.templates.premiumBadge') }}
                            </span>
                        </div>
                        <!-- Close button -->
                        <button
                            @click="closeUpgradeModal"
                            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/30 text-white flex items-center justify-center hover:bg-black/50 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-stone-800 mb-1">
                            {{ t('dashboard.templates.upgradeModalTitle', { name: upgradeTemplate.name }) }}
                        </h3>
                        <p class="text-sm text-stone-500 leading-relaxed mb-5">
                            {{ t('dashboard.templates.upgradeModalDesc') }}
                        </p>

                        <!-- Benefit pills -->
                        <div class="flex flex-wrap gap-2 mb-5">
                            <span v-for="feat in [
                                    t('dashboard.templates.upgradeBenefitAllTemplates'),
                                    t('dashboard.templates.upgradeBenefitLiveStreaming'),
                                    t('dashboard.templates.upgradeBenefitLoveStory'),
                                    t('dashboard.templates.upgradeBenefitDigitalEnvelope'),
                                    t('dashboard.templates.upgradeBenefitMusicUpload'),
                                ]"
                                  :key="feat"
                                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-stone-100 text-stone-600 text-xs font-medium">
                                <svg class="w-3 h-3 flex-shrink-0" :style="`color: ${primaryColor(upgradeTemplate)}`"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ feat }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2">
                            <a
                                href="/upgrade"
                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                                :style="`background-color: ${primaryColor(upgradeTemplate)}`"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 16L3 5l5.5 5L12 2l3.5 8L21 5l-2 11H5zm2 4h10v-2H7v2z"/>
                                </svg>
                                {{ t('dashboard.templates.upgradeToPremium') }}
                            </a>
                            <button
                                @click="closeUpgradeModal(); activeTier = 'free'"
                                class="w-full py-2.5 rounded-xl text-sm font-medium text-stone-600 border border-stone-200 hover:bg-stone-50 transition-colors"
                            >
                                {{ t('dashboard.templates.upgradeSeeFreeTpl') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Limit Modal ─────────────────────────────────────────── -->
        <Transition name="modal">
            <div
                v-if="showLimitModal"
                class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-4"
                style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px)"
                @click.self="showLimitModal = false"
            >
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">

                    <!-- Header strip -->
                    <div class="relative h-28 overflow-hidden bg-gradient-to-br from-[#EFF2F0] to-[#B8C7BF]/40 flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 rounded-2xl bg-[#92A89C]/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#73877C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-[#92A89C]/20 text-[#73877C] text-xs font-semibold">
                                {{ t('dashboard.templates.limitModalBadge') }}
                            </span>
                        </div>
                        <button
                            @click="showLimitModal = false"
                            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/10 text-stone-600 flex items-center justify-center hover:bg-black/20 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-stone-800 mb-1">
                            {{ t('dashboard.templates.limitModalTitle') }}
                        </h3>

                        <!-- Premium user: offer addon -->
                        <template v-if="isPremium">
                            <p class="text-sm text-stone-500 leading-relaxed mb-5">
                                {{ t('dashboard.templates.limitPremiumDesc') }}
                            </p>
                            <p v-if="addonError" class="text-xs text-red-500 mb-3">{{ addonError }}</p>
                            <div class="flex flex-col gap-2">
                                <button
                                    @click="buyAddon"
                                    :disabled="addonLoading"
                                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold text-white bg-[#92A89C] hover:bg-[#73877C] transition-colors disabled:opacity-60"
                                >
                                    {{ addonLoading ? t('dashboard.templates.limitProcessing') : t('dashboard.templates.limitAddonBtn') }}
                                </button>
                                <button @click="showLimitModal = false"
                                        class="w-full py-2.5 rounded-xl text-sm font-semibold border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors">
                                    {{ t('dashboard.templates.close') }}
                                </button>
                            </div>
                        </template>

                        <!-- Free user: offer upgrade -->
                        <template v-else>
                            <p class="text-sm text-stone-500 leading-relaxed mb-5">
                                {{ t('dashboard.templates.limitFreeDesc') }}
                            </p>
                            <div class="flex flex-wrap gap-2 mb-5">
                                <span v-for="feat in [
                                        t('dashboard.templates.limitBenefitUnlimited'),
                                        t('dashboard.templates.upgradeBenefitAllTemplates'),
                                        t('dashboard.templates.upgradeBenefitLiveStreaming'),
                                        t('dashboard.templates.upgradeBenefitDigitalEnvelope'),
                                        t('dashboard.templates.upgradeBenefitMusicUpload'),
                                    ]"
                                      :key="feat"
                                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-stone-100 text-stone-600 text-xs font-medium">
                                    <svg class="w-3 h-3 flex-shrink-0 text-[#92A89C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ feat }}
                                </span>
                            </div>
                            <div class="flex flex-col gap-2">
                                <a
                                    href="/upgrade"
                                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold text-white bg-[#92A89C] hover:bg-[#73877C] transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 16L3 5l5.5 5L12 2l3.5 8L21 5l-2 11H5zm2 4h10v-2H7v2z"/>
                                    </svg>
                                    {{ t('dashboard.templates.upgradeToPremium') }}
                                </a>
                                <button
                                    @click="showLimitModal = false"
                                    class="w-full py-2.5 rounded-xl text-sm font-medium text-stone-600 border border-stone-200 hover:bg-stone-50 transition-colors"
                                >
                                    {{ t('dashboard.templates.close') }}
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </Transition>

        </Teleport>

    </DashboardLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
    transform: scale(0.96);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
