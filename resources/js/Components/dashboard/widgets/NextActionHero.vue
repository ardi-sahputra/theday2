<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  nextAction: { type: Object, default: null },
});
const emit = defineEmits(['set-date', 'share']);
const { t } = useLocale();

// Per-level palette. `start`/`progress` share the calm brand green; `urgent`
// goes warm amber; `info` a softer sage.
const LEVELS = {
  urgent:   { accent: '#C2410C', chipBg: '#FDEEE4', barBg: '#C2410C', btnBg: '#C2410C', cardBg: '#FFF8F5' },
  info:     { accent: '#4A5A4C', chipBg: '#EFF2F0', barBg: '#92A89C', btnBg: '#6F8270', cardBg: '#F6F8F3' },
  progress: { accent: '#4A5A4C', chipBg: '#EFF2F0', barBg: '#92A89C', btnBg: '#92A89C', cardBg: '#F6F8F3' },
  start:    { accent: '#4A5A4C', chipBg: '#EFF2F0', barBg: '#92A89C', btnBg: '#92A89C', cardBg: '#F0F4EE' },
};

const na    = computed(() => props.nextAction);
const level = computed(() => LEVELS[na.value?.level] ?? LEVELS.progress);
const params = computed(() => na.value?.params ?? {});

const eyebrow = computed(() => t(`dashboard.index.nextAction.eyebrow.${na.value?.level ?? 'progress'}`));
const title   = computed(() => t(na.value.title, params.value));
const body    = computed(() => t(na.value.body, params.value));
const ctaText = computed(() => t(na.value.cta, params.value));

const action = computed(() => na.value?.action ?? {});
const isRoute = computed(() => action.value.kind === 'route');
const ctaHref = computed(() => {
  if (!isRoute.value) return '#';
  return action.value.param != null
    ? route(action.value.route, action.value.param)
    : route(action.value.route);
});
function onActionClick() {
  if (action.value.kind === 'action') emit(action.value.action);
}
</script>

<template>
  <section v-if="na"
           class="relative overflow-hidden rounded-2xl px-5 py-5 sm:px-7 sm:py-6"
           style="font-family: 'Inter','Figtree',system-ui,sans-serif;"
           :style="`background:${level.cardBg}; border:1px solid ${level.barBg}33;`">
    <!-- Level accent bar -->
    <span aria-hidden="true" class="absolute left-0 top-0 bottom-0 w-1.5" :style="`background:${level.barBg}`" />

    <div class="flex items-start gap-3.5 sm:gap-4">
      <!-- Icon chip -->
      <div class="shrink-0 w-11 h-11 sm:w-12 sm:h-12 rounded-xl grid place-items-center" :style="`background:${level.chipBg}`">
        <WidgetIcon :name="na.icon" :size="22" :stroke="level.accent" :sw="1.9" />
      </div>

      <!-- Text -->
      <div class="min-w-0 flex-1">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] mb-1" :style="`color:${level.barBg}`">
          {{ eyebrow }}
        </p>
        <p class="text-lg sm:text-xl font-semibold leading-snug tracking-tight" style="color:#1F2A2E;">{{ title }}</p>
        <p class="text-[13px] sm:text-sm mt-1 leading-relaxed" style="color:#6C7A75;">{{ body }}</p>
      </div>
    </div>

    <!-- CTA — full width on mobile, sits below text; own row keeps the tap target generous -->
    <div class="mt-4 sm:mt-5 sm:pl-[60px]">
      <Link v-if="isRoute" :href="ctaHref"
            class="inline-flex min-h-[44px] items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-transform active:scale-95 hover:opacity-90"
            :style="`background:${level.btnBg}`">
        <span class="whitespace-nowrap">{{ ctaText }}</span>
        <WidgetIcon name="arrow" :size="15" stroke="#fff" :sw="2" />
      </Link>
      <button v-else type="button" @click="onActionClick"
              class="inline-flex min-h-[44px] items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-transform active:scale-95 hover:opacity-90"
              :style="`background:${level.btnBg}`">
        <span class="whitespace-nowrap">{{ ctaText }}</span>
      </button>
    </div>
  </section>
</template>
