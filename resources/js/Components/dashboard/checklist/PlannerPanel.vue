<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  // { facts, enabled, insights, fresh, limited }
  initial: { type: Object, default: () => ({ facts: {}, enabled: true, insights: [], fresh: true }) },
});

const { t } = useLocale();

const facts    = computed(() => props.initial.facts ?? {});
const loading  = ref(false);
const enabled  = ref(props.initial.enabled !== false);
const limited  = ref(props.initial.limited === true);
const insights = ref(props.initial.insights ?? []);

const SEVERITY = {
  alert:   { dot: '#B4524A', text: '#7A2E27' },
  warning: { dot: '#A77B1E', text: '#5A4B1A' },
  info:    { dot: '#5E6F64', text: '#3C4A41' },
};
const sev = (s) => SEVERITY[s] || SEVERITY.info;

const TARGET_ROUTE = {
  budget:    'dashboard.budget-planner.index',
  vendor:    'dashboard.vendor.index',
  checklist: 'dashboard.checklist.index',
};
const targetHref = (tg) => (tg && TARGET_ROUTE[tg] ? route(TARGET_ROUTE[tg]) : null);

const c = computed(() => facts.value.checklist ?? {});
const b = computed(() => facts.value.budget ?? {});

// Collapse state persists per-browser so the couple can tuck the panel away.
const collapsed = ref(typeof localStorage !== 'undefined' && localStorage.getItem('plannerPanelCollapsed') === '1');
function toggleCollapse() {
  collapsed.value = !collapsed.value;
  try { localStorage.setItem('plannerPanelCollapsed', collapsed.value ? '1' : '0'); } catch { /* ignore */ }
}

async function refresh() {
  loading.value = true;
  try {
    const { data } = await window.axios.get(route('dashboard.checklist.planner-insights'));
    enabled.value  = data.enabled !== false;
    limited.value  = data.limited === true;
    insights.value = data.insights ?? [];
  } catch {
    // keep existing on transient error
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  if (enabled.value && props.initial.fresh === false) refresh();
});
</script>

<template>
  <section class="rounded-[18px] p-5 mb-5" style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); color:#FBFCF9;">
    <div class="flex items-center gap-2" :class="collapsed ? '' : 'mb-3'">
      <WidgetIcon name="sparkle" :size="16" stroke="#C7D3BC" />
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight">{{ t('dashboard.planner.title') }}</h3>
      <div class="ml-auto flex items-center gap-3">
        <button v-if="enabled && !loading && !collapsed" @click="refresh" class="text-[11px] font-medium opacity-90 hover:opacity-100" style="color:#C7D3BC;">{{ t('dashboard.planner.refresh') }}</button>
        <button @click="toggleCollapse" class="text-[11px] font-medium opacity-90 hover:opacity-100" style="color:#C7D3BC;">{{ collapsed ? t('dashboard.planner.show') : t('dashboard.planner.hide') }}</button>
      </div>
    </div>

    <template v-if="!collapsed">
    <!-- Momentum strip (deterministic, positive framing) -->
    <p class="text-[12.5px] mb-3" style="color:rgba(251,252,249,0.7);">
      <template v-if="facts.has_event_date">{{ t('dashboard.planner.strip.daysToGo', { days: facts.days_to_go }) }} · </template>
      {{ t('dashboard.planner.strip.done', { done: c.done ?? 0 }) }}
      <template v-if="b.has_budget"> · {{ t('dashboard.planner.strip.forecast', { amount: b.formatted?.forecast_total }) }}</template>
    </p>

    <!-- Deterministic facts -->
    <div class="flex flex-wrap gap-2 mb-4 text-[11.5px]">
      <span v-if="(c.due_this_week ?? 0) > 0" class="px-2.5 py-1 rounded-full" style="background:rgba(156,171,142,0.22); color:#DCE4D3;">{{ t('dashboard.planner.facts.dueThisWeek', { n: c.due_this_week }) }}</span>
      <span v-if="(c.overdue ?? 0) > 0" class="px-2.5 py-1 rounded-full" style="background:rgba(217,181,176,0.22); color:#E8C4B8;">{{ t('dashboard.planner.facts.overdue', { n: c.overdue }) }}</span>
      <span v-if="(facts.payments_due?.length ?? 0) > 0" class="px-2.5 py-1 rounded-full" style="background:rgba(217,181,176,0.22); color:#E8C4B8;">{{ t('dashboard.planner.facts.paymentsDue', { n: facts.payments_due.length }) }}</span>
    </div>

    <!-- AI cards -->
    <div v-if="enabled">
      <div v-if="loading" class="space-y-2">
        <div class="h-3 rounded-full bg-white/10 animate-pulse w-3/4"></div>
        <div class="h-3 rounded-full bg-white/10 animate-pulse w-full"></div>
      </div>
      <p v-else-if="limited && !insights.length" class="text-[12px]" style="color:rgba(251,252,249,0.6);">{{ t('dashboard.planner.limited') }}</p>
      <p v-else-if="!insights.length" class="text-[12px]" style="color:rgba(251,252,249,0.6);">{{ t('dashboard.planner.empty') }}</p>
      <div v-else class="space-y-2.5">
        <div v-for="(ins, i) in insights" :key="i" class="flex items-start gap-2.5">
          <span class="flex-shrink-0 mt-1.5 w-1.5 h-1.5 rounded-full" :style="{ background: sev(ins.severity).dot }"></span>
          <div class="flex-1">
            <p class="text-[12.5px] font-semibold">{{ ins.title }}</p>
            <p class="text-[12px] mt-0.5" style="color:rgba(251,252,249,0.72);">{{ ins.body }}</p>
            <a v-if="targetHref(ins.target)" :href="targetHref(ins.target)" class="inline-block mt-1 text-[11px] underline" style="color:#C7D3BC;">{{ t('dashboard.planner.open') }}</a>
          </div>
        </div>
        <p class="text-[10.5px] pt-1 opacity-60">{{ t('dashboard.planner.disclaimer') }}</p>
      </div>
    </div>
    </template>
  </section>
</template>
