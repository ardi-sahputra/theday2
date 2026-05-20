<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  urgentCount:   { type: Number, default: 0 },
  upcoming7d:    { type: Number, default: 0 },
  doneThisMonth: { type: Number, default: 0 },
  picSplit:      { type: Object, default: () => ({ bridePct: 0, groomPct: 0 }) },
});
const { t } = useLocale();

const cards = computed(() => [
  { icon: 'flag',  bg: 'rgba(217,181,176,0.25)', fg: '#C19089', value: String(props.urgentCount),   label: t('dashboard.checklist.stat.urgent') },
  { icon: 'cal',   bg: 'rgba(217,162,74,0.18)',  fg: '#D9A24A', value: String(props.upcoming7d),    label: t('dashboard.checklist.stat.due7d') },
  { icon: 'check', bg: 'rgba(156,171,142,0.2)',  fg: '#4A5A4C', value: String(props.doneThisMonth), label: t('dashboard.checklist.stat.doneMonth') },
  { icon: 'guest', bg: 'rgba(74,90,76,0.12)',    fg: '#3D4A4D', value: `${props.picSplit.bridePct}% / ${props.picSplit.groomPct}%`, label: t('dashboard.checklist.stat.pic') },
]);
</script>

<template>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div v-for="(c, i) in cards" :key="i"
         class="flex items-center gap-3 rounded-[16px] p-4" style="background:#FBFCF9; border:1px solid #D8DFD2;">
      <div class="w-9 h-9 rounded-[10px] grid place-items-center flex-shrink-0" :style="{ background: c.bg }">
        <WidgetIcon :name="c.icon" :size="16" :stroke="c.fg" />
      </div>
      <div class="min-w-0">
        <div class="font-cormorant font-medium text-[24px] leading-none" style="color:#1F2A2E;">{{ c.value }}</div>
        <div class="text-[11.5px] mt-1 truncate" style="color:#6C7A75;">{{ c.label }}</div>
      </div>
    </div>
  </div>
</template>
