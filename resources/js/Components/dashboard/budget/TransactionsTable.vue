<script setup>
import { useLocale } from '@/Composables/useLocale';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';

defineProps({ items: { type: Array, default: () => [] } });
const emit = defineEmits(['edit']);
const { t } = useLocale();

const STATUS = {
  paid:   { bg: 'rgba(156,171,142,0.18)', fg: '#4A5A4C', dot: '#9CAB8E' },
  dp:     { bg: 'rgba(217,162,74,0.18)',  fg: '#8E6515', dot: '#D9A24A' },
  unpaid: { bg: 'rgba(108,122,117,0.15)', fg: '#3D4A4D', dot: '#6C7A75' },
};
const isUpcoming = (it) => it.payment_status !== 'paid' && !!it.due_date;
</script>

<template>
  <div class="rounded-[16px] overflow-hidden" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="hidden md:grid items-center gap-3 px-5 py-3 text-[11px] font-semibold uppercase tracking-wide"
         style="grid-template-columns: 2fr 1fr 1.2fr 0.9fr 0.9fr 1fr; color:#6C7A75; border-bottom:1px solid #D8DFD2;">
      <div>{{ t('dashboard.budget.table.expense') }}</div>
      <div>{{ t('dashboard.budget.table.category') }}</div>
      <div>{{ t('dashboard.budget.table.vendor') }}</div>
      <div>{{ t('dashboard.budget.table.date') }}</div>
      <div class="text-right">{{ t('dashboard.budget.table.amount') }}</div>
      <div class="text-right">{{ t('dashboard.budget.table.status') }}</div>
    </div>
    <div v-if="items.length">
      <div v-for="it in items" :key="it.id"
           class="grid items-center gap-3 px-5 py-3.5 cursor-pointer hover:bg-[#F6F8F3] transition-colors"
           :style="{
             gridTemplateColumns: '2fr 1fr 1.2fr 0.9fr 0.9fr 1fr',
             borderBottom: '1px solid #EEF2EA',
             background: isUpcoming(it) ? 'rgba(244,237,220,0.4)' : '',
           }"
           @click="emit('edit', it)">
        <div class="text-[13.5px] font-medium min-w-0 truncate" style="color:#1F2A2E;">{{ it.title }}</div>
        <div><span class="text-[11px] px-2 py-0.5 rounded-full" style="background:rgba(74,90,76,0.1); color:#3D4A4D;">{{ it.category?.name }}</span></div>
        <div class="text-[13px] inline-flex items-center gap-1.5 min-w-0" style="color:#3D4A4D;">
          <WidgetIcon v-if="it.vendor_name" name="vendor" :size="12" stroke="#6F8270" /> <span class="truncate">{{ it.vendor_name || '—' }}</span>
        </div>
        <div class="font-jet text-[11.5px]" :style="{ color: isUpcoming(it) ? '#D9A24A' : '#6C7A75' }">{{ it.due_date_label || it.payment_date_label || '—' }}</div>
        <div class="text-right font-cormorant text-[18px] font-medium" style="color:#1F2A2E;">{{ it.formatted?.terpakai }}</div>
        <div class="text-right">
          <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                :style="{ background: (STATUS[it.payment_status] || STATUS.unpaid).bg, color: (STATUS[it.payment_status] || STATUS.unpaid).fg }">
            <span class="w-1.5 h-1.5 rounded-full" :style="{ background: (STATUS[it.payment_status] || STATUS.unpaid).dot }" />
            {{ it.payment_status_label }}
          </span>
        </div>
      </div>
    </div>
    <p v-else class="px-5 py-10 text-center text-[13px]" style="color:#6C7A75;">{{ t('dashboard.budget.table.empty') }}</p>
  </div>
</template>
