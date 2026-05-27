<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  stats:           { type: Object, required: true },
  budgetWidget:    { type: Object, required: true },
  checklistWidget: { type: Object, required: true },
});
const { t } = useLocale();

// RSVP & Ucapan only make sense once an invitation is published.
const hasPublished = computed(() => (props.stats.published_count ?? 0) > 0);

const cards = computed(() => {
  const rsvp = {
    label: t('dashboard.index.widgets.stats.rsvp'),
    value: String(props.stats.rsvp_attending ?? 0),
    sub:   t('dashboard.index.widgets.stats.rsvpSub', { total: props.stats.rsvp_total ?? 0 }),
    color: '#92A89C', icon: 'guest', demo: false,
  };
  const budget = {
    label: t('dashboard.index.widgets.stats.budget'),
    value: (props.budgetWidget?.usage_percentage ?? 0) + '%',
    sub:   props.budgetWidget?.has_budget ? `${props.budgetWidget.formatted.total_actual} / ${props.budgetWidget.formatted.total_budget}` : t('dashboard.index.widgets.stats.budgetEmpty'),
    color: '#C19089', icon: 'budget', demo: false,
  };
  const checklist = {
    label: t('dashboard.index.widgets.stats.checklist'),
    value: String(props.checklistWidget?.done ?? 0),
    sub:   t('dashboard.index.widgets.stats.checklistSub', { total: props.checklistWidget?.total ?? 0 }),
    color: '#D9A24A', icon: 'check', demo: false,
  };
  const ucapan = {
    label: t('dashboard.index.widgets.stats.ucapan'),
    value: String(props.stats.ucapan_count ?? 0),
    sub:   t('dashboard.index.widgets.stats.ucapanSub'),
    color: '#6F8270', icon: 'gift', demo: false,
  };

  return hasPublished.value
    ? [rsvp, budget, checklist, ucapan]
    : [budget, checklist];
});
</script>

<template>
  <div class="qs-scroll qs-grid flex gap-4 overflow-x-auto snap-x snap-mandatory pb-1
              lg:grid lg:overflow-visible lg:pb-0"
       :style="{ '--qs-cols': cards.length }">
    <div v-for="(s, i) in cards" :key="i"
         class="relative overflow-hidden rounded-[16px] px-4 py-3 snap-start shrink-0 w-[76%] sm:w-[44%] lg:w-auto"
         style="background:#FBFCF9; border:1px solid #D8DFD2;">
      <div class="flex items-center gap-2 mb-2">
        <div class="w-6 h-6 rounded-[7px] grid place-items-center shrink-0" :style="{ background: s.color }">
          <WidgetIcon :name="s.icon" :size="13" stroke="#fff" />
        </div>
        <span class="text-xs font-medium truncate" style="color:#6C7A75;">{{ s.label }}</span>
        <span v-if="s.demo" class="ml-auto text-[9.5px] font-semibold px-1.5 py-0.5 rounded-full shrink-0"
              style="background: rgba(217,162,74,0.16); color:#B07D2A;">{{ t('dashboard.index.widgets.demoBadge') }}</span>
      </div>
      <div class="font-cormorant font-medium leading-none tracking-tight text-[28px]" style="color:#1F2A2E;">{{ s.value }}</div>
      <div class="text-xs mt-1" style="color:#6C7A75;">{{ s.sub }}</div>
    </div>
  </div>
</template>

<style scoped>
.qs-scroll { scrollbar-width: none; -ms-overflow-style: none; }
.qs-scroll::-webkit-scrollbar { display: none; }
@media (min-width: 1024px) {
  .qs-grid { grid-template-columns: repeat(var(--qs-cols, 4), minmax(0, 1fr)); }
}
</style>
