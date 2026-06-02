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
  urgent:   { accent: '#C2410C', chipBg: '#FDEEE4', barBg: '#C2410C', btnBg: '#C2410C' },
  info:     { accent: '#4A5A4C', chipBg: '#EFF2F0', barBg: '#92A89C', btnBg: '#6F8270' },
  progress: { accent: '#4A5A4C', chipBg: '#EFF2F0', barBg: '#92A89C', btnBg: '#92A89C' },
  start:    { accent: '#4A5A4C', chipBg: '#EFF2F0', barBg: '#92A89C', btnBg: '#92A89C' },
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
           class="relative overflow-hidden rounded-2xl flex items-center gap-4 px-4 py-4 sm:px-5"
           style="background:#FBFCF9; border:1px solid #D8DFD2; font-family: 'Inter','Figtree',system-ui,sans-serif;">
    <!-- Level accent bar -->
    <span aria-hidden="true" class="absolute left-0 top-0 bottom-0 w-1.5" :style="`background:${level.barBg}`" />

    <!-- Icon chip -->
    <div class="shrink-0 w-11 h-11 rounded-xl grid place-items-center" :style="`background:${level.chipBg}`">
      <WidgetIcon :name="na.icon" :size="20" :stroke="level.accent" :sw="1.9" />
    </div>

    <!-- Text -->
    <div class="min-w-0 flex-1">
      <p class="text-[10.5px] font-semibold uppercase tracking-[0.14em] mb-0.5" :style="`color:${level.barBg}`">
        {{ eyebrow }}
      </p>
      <p class="text-sm sm:text-[15px] font-semibold leading-snug" style="color:#1F2A2E;">{{ title }}</p>
      <p class="text-xs mt-0.5 leading-snug hidden sm:block" style="color:#6C7A75;">{{ body }}</p>
    </div>

    <!-- CTA -->
    <Link v-if="isRoute" :href="ctaHref"
          class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs sm:text-[13px] font-semibold text-white transition-transform active:scale-95 hover:opacity-90"
          :style="`background:${level.btnBg}`">
      <span class="whitespace-nowrap">{{ ctaText }}</span>
      <WidgetIcon name="arrow" :size="14" stroke="#fff" :sw="2" class="hidden sm:block" />
    </Link>
    <button v-else type="button" @click="onActionClick"
            class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs sm:text-[13px] font-semibold text-white transition-transform active:scale-95 hover:opacity-90"
            :style="`background:${level.btnBg}`">
      <span class="whitespace-nowrap">{{ ctaText }}</span>
    </button>
  </section>
</template>
