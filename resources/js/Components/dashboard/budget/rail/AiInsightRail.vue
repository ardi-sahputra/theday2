<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background: linear-gradient(135deg, #F4EDDC, #E9DFC4); border:1px solid #E0D2BD;">
    <div class="px-5 pt-4 pb-3 flex items-center gap-2">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.rail.ai.title') }}</h3>
      <DemoBadge />
    </div>
    <div class="px-5 pb-5">
      <div class="flex items-start gap-2.5 mb-3">
        <WidgetIcon name="sparkle" :size="16" stroke="#8E6515" class="flex-shrink-0 mt-0.5" />
        <p class="text-[12.5px] leading-relaxed" style="color:#5A4B1A;">{{ t('dashboard.budget.rail.ai.sample') }}</p>
      </div>
      <span class="block w-full text-center px-3 py-2 rounded-full text-[12px] font-semibold cursor-default" style="background:#1F2A2E; color:#FBFCF9; opacity:0.85;">{{ t('dashboard.budget.rail.ai.cta') }}</span>
    </div>
  </div>
</template>
