<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    currentPlan: { type: Object, required: true },
    premiumPlan: { type: Object, default: null },
});

const formatRupiah = (n) => Number(n ?? 0).toLocaleString('id-ID');

const premiumPriceLabel = computed(() =>
    props.premiumPlan
        ? t('dashboard.paket.premiumPriceLabel', { price: formatRupiah(props.premiumPlan.price) })
        : '—'
);
const premiumPricePeriod = computed(() => t('dashboard.paket.premiumPricePeriod'));
const premiumOriginalPriceLabel = computed(() =>
    props.premiumPlan?.has_discount
        ? t('dashboard.paket.premiumPriceLabel', { price: formatRupiah(props.premiumPlan.original_price) })
        : null
);

// ── Checkout state ────────────────────────────────────────────────────────────
const isCheckingOut = ref(false);
const checkoutError = ref('');

// ── FAQ accordion ─────────────────────────────────────────────────────────────
const openFaq = ref(null);
const faqs = computed(() => [
    {
        q: t('dashboard.paket.faq1Q'),
        a: t('dashboard.paket.faq1A'),
    },
    {
        q: t('dashboard.paket.faq2Q'),
        a: t('dashboard.paket.faq2A'),
    },
    {
        q: t('dashboard.paket.faq3Q'),
        a: t('dashboard.paket.faq3A'),
    },
    {
        q: t('dashboard.paket.faq4Q'),
        a: t('dashboard.paket.faq4A'),
    },
    {
        q: t('dashboard.paket.faq5Q'),
        a: t('dashboard.paket.faq5A'),
    },
]);

// ── Computed ──────────────────────────────────────────────────────────────────
const isPremium    = computed(() => props.currentPlan.is_premium);
const daysLeft     = computed(() => props.currentPlan.days_remaining ?? null);
const expiresAt    = computed(() => props.currentPlan.expires_at);
const expiryWarn   = computed(() => isPremium.value && daysLeft.value !== null && daysLeft.value <= 7);

const premiumCtaLabel = computed(() => {
    if (!isPremium.value) return t('dashboard.paket.ctaUpgrade');
    if (daysLeft.value !== null && daysLeft.value <= 14) return t('dashboard.paket.ctaRenew');
    return t('dashboard.paket.ctaAlreadyPremium');
});
const premiumCtaDisabled = computed(() => isPremium.value && daysLeft.value !== null && daysLeft.value > 14);

// ── Checkout ──────────────────────────────────────────────────────────────────
const startCheckout = async () => {
    if (isCheckingOut.value || premiumCtaDisabled.value) return;

    isCheckingOut.value = true;
    checkoutError.value = '';

    try {
        const { data } = await axios.post(route('dashboard.subscriptions.checkout'));
        window.location.href = data.payment_url;
    } catch (err) {
        checkoutError.value = err?.response?.data?.error ?? t('dashboard.paket.checkoutErrorFallback');
        isCheckingOut.value = false;
    }
};

// ── Feature comparison rows ───────────────────────────────────────────────────
const features = computed(() => [
    { label: t('dashboard.paket.featureLabelInvitation'),      free: '1',  premium: 'Unlimited' },
    { label: t('dashboard.paket.featureLabelPhoto'),           free: '5',  premium: 'Unlimited' },
    { label: t('dashboard.paket.featureLabelTemplatePremium'), free: false, premium: true },
    { label: t('dashboard.paket.featureLabelMusic'),           free: false, premium: true },
    { label: t('dashboard.paket.featureLabelNoWatermark'),     free: false, premium: true },
    { label: t('dashboard.paket.featureLabelSlug'),            free: false, premium: true },
    { label: t('dashboard.paket.featureLabelPassword'),        free: false, premium: true },
    { label: t('dashboard.paket.featureLabelAnalytics'),       free: false, premium: true },
    { label: t('dashboard.paket.featureLabelSupport'),         free: false, premium: true },
]);

// ── Free card feature list ────────────────────────────────────────────────────
const freeFeatures = computed(() => [
    t('dashboard.paket.freeFeatureInvitation'),
    t('dashboard.paket.freeFeaturePhoto'),
    t('dashboard.paket.freeFeatureTemplate'),
    t('dashboard.paket.freeFeatureMusic'),
]);

// ── Premium card feature list ─────────────────────────────────────────────────
const premiumFeatures = computed(() => [
    t('dashboard.paket.premiumFeatureInvitation'),
    t('dashboard.paket.premiumFeaturePhoto'),
    t('dashboard.paket.premiumFeatureTemplate'),
    t('dashboard.paket.premiumFeatureMusic'),
    t('dashboard.paket.premiumFeatureNoWatermark'),
    t('dashboard.paket.premiumFeatureSlug'),
    t('dashboard.paket.premiumFeaturePassword'),
    t('dashboard.paket.premiumFeatureAnalytics'),
    t('dashboard.paket.premiumFeatureSupport'),
]);

// ── Payment methods ───────────────────────────────────────────────────────────
const paymentMethods = computed(() => [
    t('dashboard.paket.paymentMethodGopay'),
    t('dashboard.paket.paymentMethodOvo'),
    t('dashboard.paket.paymentMethodDana'),
    t('dashboard.paket.paymentMethodQris'),
    t('dashboard.paket.paymentMethodBank'),
    t('dashboard.paket.paymentMethodCreditCard'),
]);
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-stone-800">{{ t('dashboard.paket.pageTitle') }}</h1>
        </template>

        <div class="max-w-3xl mx-auto space-y-6 sm:space-y-8 px-4 sm:px-0">

            <!-- ── 1. Current Plan Status ──────────────────────────────── -->
            <div class="rounded-2xl border p-4 sm:p-6"
                 :class="isPremium
                    ? 'bg-gradient-to-br from-[#EFF2F0] to-[#F4F7F5] border-[#92A89C]/40'
                    : 'bg-white border-stone-100'">

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest mb-1"
                           :class="isPremium ? 'text-[#73877C]' : 'text-stone-400'">
                            {{ t('dashboard.paket.currentPlanLabel') }}
                        </p>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#2C2417]">
                            {{ isPremium ? t('dashboard.paket.planNamePremium') : t('dashboard.paket.planNameFree') }}
                        </h2>

                        <!-- Premium: expiry info -->
                        <template v-if="isPremium && expiresAt">
                            <p class="text-sm mt-1" :class="expiryWarn ? 'text-[#2C2417]' : 'text-stone-500'">
                                {{ t('dashboard.paket.activeUntil') }} <strong>{{ expiresAt }}</strong>
                            </p>
                            <span v-if="expiryWarn"
                                  class="inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-800">
                                {{ t('dashboard.paket.expiryWarn', { days: daysLeft }) }}
                            </span>
                            <p v-else class="text-xs mt-1 text-stone-400">
                                {{ t('dashboard.paket.daysLeft', { days: daysLeft }) }}
                            </p>
                        </template>

                        <!-- Free: limits summary -->
                        <template v-else-if="!isPremium">
                            <p class="text-sm mt-1 text-stone-500">
                                {{ t('dashboard.paket.freeDescription') }}
                            </p>
                            <ul class="mt-2 space-y-0.5 text-xs text-stone-400">
                                <li>{{ t('dashboard.paket.freeLimitInvitation') }}</li>
                                <li>{{ t('dashboard.paket.freeLimitPhoto') }}</li>
                                <li>{{ t('dashboard.paket.freeLimitTemplate') }}</li>
                                <li>{{ t('dashboard.paket.freeLimitWatermark') }}</li>
                            </ul>
                        </template>
                    </div>

                    <button @click="startCheckout"
                            :disabled="premiumCtaDisabled || isCheckingOut"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-sm font-semibold transition-all sm:flex-shrink-0"
                            :class="premiumCtaDisabled
                                ? 'bg-stone-100 text-stone-400 cursor-not-allowed'
                                : 'bg-brand-primary hover:bg-brand-primary-hover text-white'">
                        {{ isCheckingOut ? t('dashboard.paket.ctaRedirecting') : premiumCtaLabel }}
                    </button>
                </div>
            </div>

            <!-- Checkout error notice -->
            <div v-if="checkoutError"
                 class="rounded-xl p-4 text-sm bg-red-50 text-red-700 border border-red-100">
                {{ checkoutError }}
            </div>

            <!-- ── 2. Pricing Cards ──────────────────────────────────────── -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

                <!-- Free Card -->
                <div class="rounded-2xl border border-stone-100 p-5 sm:p-6 bg-white">
                    <div class="flex items-center gap-2 mb-4">
                        <h3 class="text-base font-semibold text-stone-800">{{ t('dashboard.paket.planNameFree') }}</h3>
                        <span v-if="!isPremium"
                              class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#92A89C]/15 text-[#73877C]">
                            {{ t('dashboard.paket.badgeYourPlan') }}
                        </span>
                    </div>
                    <div class="mb-1">
                        <span class="text-3xl font-bold text-stone-800">{{ t('dashboard.paket.freePriceLabel') }}</span>
                    </div>
                    <p class="text-xs text-stone-400 mb-5">{{ t('dashboard.paket.freePricePeriod') }}</p>
                    <p class="text-sm text-stone-500 mb-5">{{ t('dashboard.paket.freePriceCta') }}</p>
                    <ul class="space-y-2.5 mb-6 text-sm text-stone-600">
                        <li v-for="feat in freeFeatures"
                            :key="feat"
                            class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            {{ feat }}
                        </li>
                        <li class="flex items-center gap-2 text-stone-400">
                            <span class="w-4 h-4 rounded-full bg-stone-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            {{ t('dashboard.paket.freeFeatureWatermark') }}
                        </li>
                    </ul>
                    <button disabled
                            class="w-full py-2.5 rounded-xl text-sm font-semibold border border-stone-100 text-stone-400 cursor-not-allowed">
                        {{ isPremium ? t('dashboard.paket.planNameFree') : t('dashboard.paket.badgeYourPlan') }}
                    </button>
                </div>

                <!-- Premium Card -->
                <div class="rounded-2xl p-5 sm:p-6 relative bg-gradient-to-br from-[#EFF2F0] to-[#F4F7F5] border-2 border-[#92A89C]/50 shadow-[0_8px_32px_rgba(146,168,156,0.18)]">
                    <div class="absolute -top-3 left-6">
                        <span class="text-xs font-bold px-3 py-1 rounded-full text-white"
                              style="background:linear-gradient(90deg,#73877C,#92A89C)">
                            {{ t('dashboard.paket.badgeMostPopular') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 mt-2 mb-4">
                        <h3 class="text-base font-semibold text-[#2C2417]">{{ t('dashboard.paket.planNamePremium') }}</h3>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#92A89C]/20 text-[#73877C]" v-if="isPremium">
                            {{ t('dashboard.paket.badgeYourPlan') }}
                        </span>
                    </div>

                    <div class="mb-1 flex items-baseline gap-2 flex-wrap">
                        <span class="text-3xl font-bold text-[#2C2417]">{{ premiumPriceLabel }}</span>
                        <span v-if="premiumOriginalPriceLabel"
                              class="text-sm text-stone-400 line-through">{{ premiumOriginalPriceLabel }}</span>
                        <span v-if="premiumPlan?.has_discount"
                              class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                            -{{ premiumPlan.discount_percent }}%
                        </span>
                    </div>
                    <p class="text-xs mb-2 text-[#73877C] font-medium">{{ premiumPricePeriod }}</p>
                    <p class="text-sm mb-5 text-stone-500">{{ t('dashboard.paket.premiumPriceCta') }}</p>

                    <ul class="space-y-2.5 mb-6 text-sm text-stone-700">
                        <li v-for="feat in premiumFeatures"
                            :key="feat"
                            class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full bg-[#92A89C]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-2.5 h-2.5 text-[#73877C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            {{ feat }}
                        </li>
                    </ul>

                    <button @click="startCheckout"
                            :disabled="premiumCtaDisabled || isCheckingOut"
                            class="w-full py-3 rounded-xl text-sm font-bold transition-all"
                            :class="premiumCtaDisabled
                                ? 'cursor-not-allowed bg-stone-100 text-stone-400'
                                : 'bg-brand-primary hover:bg-brand-primary-hover text-white'">
                        {{ isCheckingOut ? t('dashboard.paket.ctaRedirectingShort') : premiumCtaLabel }}
                    </button>

                    <p class="text-center text-xs mt-3 text-stone-400">
                        {{ t('dashboard.paket.safePayment') }}
                    </p>
                </div>
            </div>

            <!-- ── 3. Feature Comparison Table ─────────────────────────── -->
            <div class="bg-white rounded-2xl border border-stone-100 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-stone-100">
                    <h3 class="text-base font-semibold text-stone-800">{{ t('dashboard.paket.comparisonTitle') }}</h3>
                </div>
                <table class="w-full text-sm table-fixed">
                    <thead>
                        <tr class="border-b border-stone-100">
                            <th class="text-left px-3 sm:px-6 py-3 text-[10px] sm:text-xs font-semibold text-stone-500 uppercase tracking-wider w-1/2">{{ t('dashboard.paket.comparisonColFeature') }}</th>
                            <th class="text-center px-2 sm:px-4 py-3 text-[10px] sm:text-xs font-semibold text-stone-500 uppercase tracking-wider">{{ t('dashboard.paket.comparisonColFree') }}</th>
                            <th class="text-center px-2 sm:px-4 py-3 text-[10px] sm:text-xs font-semibold text-[#73877C] uppercase tracking-wider">{{ t('dashboard.paket.comparisonColPremium') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, i) in features" :key="row.label"
                            :class="i % 2 === 0 ? 'bg-white' : 'bg-stone-50'">
                            <td class="px-3 sm:px-6 py-3 text-xs sm:text-sm text-stone-700 break-words">{{ row.label }}</td>
                            <td class="px-2 sm:px-4 py-3 text-center">
                                <span v-if="row.free === true" class="text-emerald-600 font-bold">✓</span>
                                <span v-else-if="row.free === false" class="text-stone-300">✗</span>
                                <span v-else class="text-stone-600 font-medium text-[11px] sm:text-xs">{{ row.free }}</span>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-center">
                                <span v-if="row.premium === true" class="text-[#73877C] font-bold">✓</span>
                                <span v-else-if="row.premium === false" class="text-stone-300">✗</span>
                                <span v-else class="text-[#73877C] font-semibold text-[11px] sm:text-xs">{{ row.premium }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── 4. Payment Methods ──────────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-stone-100 p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-stone-700 mb-3 sm:mb-4">{{ t('dashboard.paket.paymentMethodsTitle') }}</h3>
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <span v-for="m in paymentMethods" :key="m"
                          class="px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-stone-100 text-[11px] sm:text-xs font-semibold text-stone-600 bg-stone-50">
                        {{ m }}
                    </span>
                </div>
            </div>

            <!-- ── 5. FAQ ──────────────────────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-stone-100 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-stone-100">
                    <h3 class="text-base font-semibold text-stone-800">{{ t('dashboard.paket.faqTitle') }}</h3>
                </div>
                <div class="divide-y divide-stone-100">
                    <div v-for="(faq, i) in faqs" :key="i">
                        <button @click="openFaq = openFaq === i ? null : i"
                                class="w-full flex items-center justify-between gap-3 px-4 sm:px-6 py-3 sm:py-4 text-left text-sm font-medium text-stone-700 hover:bg-stone-50 transition-colors">
                            <span class="min-w-0">{{ faq.q }}</span>
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-400"
                                 :class="openFaq === i ? 'rotate-180' : ''"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <Transition name="expand">
                            <div v-if="openFaq === i" class="px-4 sm:px-6 pb-3 sm:pb-4 text-sm text-stone-500 leading-relaxed">
                                {{ faq.a }}
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>

<style scoped>
.expand-enter-active, .expand-leave-active { transition: all 0.2s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 200px; }
</style>
