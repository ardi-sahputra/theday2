<script setup>
import { computed } from 'vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ summary: { type: Object, required: true } });
const { t } = useLocale();

const overBudget  = computed(() => !!props.summary?.is_total_overbudget);
const pct         = computed(() => props.summary?.usage_percentage ?? 0);
const f           = computed(() => props.summary?.formatted ?? {});
</script>

<template>
  <section class="relative overflow-hidden rounded-[18px] p-6 sm:p-7 mb-6 text-white"
           style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%);">
    <span aria-hidden="true" class="absolute -top-20 -right-16 w-56 h-56 rounded-full"
          style="background: radial-gradient(circle, rgba(156,171,142,0.35), transparent 70%);" />
    <div class="relative z-10 grid gap-7 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1.4fr]">
      <div>
        <div class="text-[10.5px] tracking-[0.18em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.budget.hero.total') }}</div>
        <div class="font-cormorant font-medium text-[36px] mt-2 leading-none tracking-tight">{{ f.total_budget ?? '—' }}</div>
      </div>
      <div>
        <div class="text-[10.5px] tracking-[0.18em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.budget.hero.used') }}</div>
        <div class="font-cormorant font-medium text-[36px] mt-2 leading-none tracking-tight" style="color:#C7D3BC;">{{ f.total_actual ?? '—' }}</div>
        <div class="text-[11.5px] mt-1.5" style="color:rgba(251,252,249,0.6);">{{ t('dashboard.budget.hero.ofBudget', { pct }) }}</div>
      </div>
      <div>
        <div class="text-[10.5px] tracking-[0.18em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.budget.hero.remaining') }}</div>
        <div class="font-cormorant font-medium text-[36px] mt-2 leading-none tracking-tight">{{ f.remaining_budget ?? '—' }}</div>
      </div>
      <div class="sm:border-l sm:pl-6" style="border-color: rgba(251,252,249,0.12);">
        <div class="text-[10.5px] tracking-[0.18em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.budget.hero.status') }}</div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full mt-2 text-[12px] font-semibold"
             :style="overBudget ? 'background:rgba(217,181,176,0.25); color:#E8C4B8;' : 'background:rgba(156,171,142,0.25); color:#DCE4D3;'">
          <span class="w-1.5 h-1.5 rounded-full" :style="{ background: overBudget ? '#C19089' : '#9CAB8E' }" />
          {{ overBudget ? t('dashboard.budget.hero.overBudget') : t('dashboard.budget.hero.onTrack') }}
        </div>
        <div class="mt-3.5 h-1.5 rounded-full overflow-hidden" style="background:rgba(251,252,249,0.12);">
          <div class="h-full rounded-full" :style="{ width: Math.min(pct,100) + '%', background: overBudget ? '#C19089' : 'linear-gradient(90deg, #9CAB8E, #C7D3BC)' }" />
        </div>
        <div class="text-[11px] mt-1.5 inline-flex items-center gap-1.5" style="color:rgba(251,252,249,0.55);">
          {{ t('dashboard.budget.hero.forecast') }} <DemoBadge />
        </div>
      </div>
    </div>
  </section>
</template>
