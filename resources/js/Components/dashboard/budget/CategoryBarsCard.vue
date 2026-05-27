<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({ categories: { type: Array, default: () => [] } });
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.bars.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.budget.bars.sub') }}</p>
    </div>
    <div class="px-5 pb-5 flex flex-col gap-3.5">
      <div v-if="!categories.length" class="text-[12.5px] py-2" style="color:#6C7A75;">{{ t('dashboard.budget.bars.empty') }}</div>
      <div v-for="c in categories" :key="c.id ?? c.name">
        <div class="flex items-center gap-2.5 mb-1.5">
          <span class="w-2 h-2 rounded-sm flex-shrink-0" :style="{ background: c.color }" />
          <span class="text-[13px] font-medium flex-1 truncate" style="color:#1F2A2E;">{{ c.name }}</span>
          <span class="font-jet text-[11.5px]" :style="{ color: c.status === 'melebihi' ? '#C19089' : '#6C7A75' }">{{ c.formatted?.actual_total }} / {{ c.formatted?.planned_total }}</span>
          <span class="font-jet text-[11px] font-bold text-right" :style="{ color: c.status === 'melebihi' ? '#C19089' : '#4A5A4C', minWidth: '38px' }">{{ Math.round(c.usage_percentage || 0) }}%</span>
        </div>
        <div class="h-1.5 rounded-full overflow-hidden" style="background:#DCE4D3;">
          <div class="h-full rounded-full" :style="{ width: Math.min(c.usage_percentage || 0, 100) + '%', background: c.color }" />
        </div>
      </div>
    </div>
  </div>
</template>
