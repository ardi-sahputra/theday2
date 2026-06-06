<script setup>
import { useLocale } from '@/Composables/useLocale';

defineProps({
  progress:     { type: Number, default: 0 },
  done:         { type: Number, default: 0 },
  total:        { type: Number, default: 0 },
  remaining:    { type: Number, default: 0 },
  urgentCount:  { type: Number, default: 0 },
  daysUntil:    { type: Number, default: null },
  hasEventDate: { type: Boolean, default: false },
});
const { t } = useLocale();
</script>

<template>
  <section class="relative overflow-hidden rounded-[20px] p-6 sm:p-7 mb-4"
           style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); box-shadow: 0 20px 50px -28px rgba(31,42,46,0.45);">
    <span aria-hidden="true" class="absolute -top-20 -right-16 w-64 h-64 rounded-full"
          style="background: radial-gradient(circle, rgba(146,168,156,0.35), transparent 70%);" />
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5">
      <div>
        <div class="text-[11.5px] tracking-[0.18em] uppercase font-semibold" style="color: rgba(251,252,249,0.6);">
          {{ t('dashboard.checklist.hero.overall') }}
        </div>
        <div class="flex items-baseline gap-4 mt-2.5">
          <div class="font-cormorant font-medium leading-none tracking-tight text-[58px] text-white">{{ progress }}%</div>
          <div class="text-sm" style="color: rgba(251,252,249,0.7);">
            {{ t('dashboard.checklist.hero.doneOfTotal', { done, total }) }}
          </div>
        </div>
        <div class="mt-4 h-2 rounded-full overflow-hidden max-w-[480px]" style="background: rgba(251,252,249,0.1);">
          <div class="h-full rounded-full transition-all duration-500"
               :style="{ width: progress + '%', background: 'linear-gradient(90deg, #92A89C, #C7D3BC)' }" />
        </div>
        <div class="flex gap-6 mt-4 text-[12.5px]" style="color: rgba(251,252,249,0.7);">
          <span>✓ <strong class="text-white font-bold mx-1">{{ done }}</strong> {{ t('dashboard.checklist.hero.done') }}</span>
          <span>⏱ <strong class="text-white font-bold mx-1">{{ remaining }}</strong> {{ t('dashboard.checklist.hero.remaining') }}</span>
          <span>⚠ <strong class="text-white font-bold mx-1">{{ urgentCount }}</strong> {{ t('dashboard.checklist.hero.urgent') }}</span>
        </div>
      </div>
      <div class="flex flex-col items-start sm:items-end gap-4 sm:min-w-[220px]">
        <div class="text-left sm:text-right">
          <div class="text-[11.5px] tracking-[0.18em] uppercase font-semibold" style="color: rgba(251,252,249,0.6);">
            {{ t('dashboard.checklist.hero.toTheDay') }}
          </div>
          <div v-if="hasEventDate && daysUntil !== null" class="font-cormorant italic text-[28px] text-white mt-1.5 font-medium">
            {{ t('dashboard.checklist.hero.daysLeft', { days: daysUntil }) }}
          </div>
          <div v-else class="font-cormorant italic text-[22px] mt-1.5 font-medium" style="color: rgba(251,252,249,0.6);">
            {{ t('dashboard.checklist.hero.noDate') }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
