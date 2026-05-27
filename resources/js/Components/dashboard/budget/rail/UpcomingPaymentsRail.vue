<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
defineProps({ payments: { type: Array, default: () => [] } });
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.rail.upcoming.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.budget.rail.upcoming.sub') }}</p>
    </div>
    <div class="px-5 pb-4">
      <template v-if="payments.length">
        <div v-for="(p, i) in payments" :key="p.id" class="flex gap-3 py-2.5" :style="i ? 'border-top:1px solid #D8DFD2;' : ''">
          <div class="w-11 h-11 rounded-[10px] grid place-items-center flex-shrink-0" style="background:#F4EDDC; color:#8E6515;">
            <WidgetIcon name="cal" :size="18" stroke="#8E6515" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-[13px] font-semibold truncate" style="color:#1F2A2E;">{{ p.title }}</div>
            <div class="text-[11px] mt-0.5 truncate" style="color:#6C7A75;">{{ p.vendor_name || p.due_date_label }}</div>
          </div>
          <div class="font-cormorant text-[18px] font-medium" style="color:#1F2A2E;">{{ p.formatted?.terpakai }}</div>
        </div>
      </template>
      <p v-else class="text-[12.5px] py-2" style="color:#6C7A75;">{{ t('dashboard.budget.rail.upcoming.empty') }}</p>
    </div>
  </div>
</template>
