<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({
  bridePct:   { type: Number, default: 0 },
  groomPct:   { type: Number, default: 0 },
  brideCount: { type: Number, default: 0 },
  groomCount: { type: Number, default: 0 },
});
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.rail.pic.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.checklist.rail.pic.sub') }}</p>
    </div>
    <div class="px-5 pb-4">
      <div v-for="row in [
            { label: t('dashboard.checklist.assignee.bride'), k:'A', pct: bridePct, c:'#C7D3BC' },
            { label: t('dashboard.checklist.assignee.groom'), k:'R', pct: groomPct, c:'#D9B5B0' }]"
           :key="row.k" class="mb-3 last:mb-0">
        <div class="flex justify-between items-center mb-1.5">
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full grid place-items-center text-[11px] font-bold font-cormorant" :style="{ background: row.c, color:'#1F2A2E' }">{{ row.k }}</span>
            <span class="text-[13px] font-medium" style="color:#1F2A2E;">{{ row.label }}</span>
          </div>
          <span class="font-jet text-[11.5px]" style="color:#6C7A75;">{{ row.pct }}%</span>
        </div>
        <div class="h-1.5 rounded-full overflow-hidden" style="background:#DCE4D3;">
          <div class="h-full rounded-full" :style="{ width: row.pct + '%', background: row.c }" />
        </div>
      </div>
    </div>
  </div>
</template>
