<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  budgetWidget: { type: Object, required: true },
});
const { t } = useLocale();

const PALETTE = ['#6F8270', '#C19089', '#D9A24A', '#92A89C', '#C7D3BC', '#DCE4D3'];

const cats = computed(() =>
  (props.budgetWidget?.categories ?? []).map((c, i) => ({
    ...c, color: PALETTE[i % PALETTE.length],
  }))
);
const totalActual = computed(() => cats.value.reduce((a, c) => a + c.actual, 0));
const totalBudget = computed(() => cats.value.reduce((a, c) => a + c.planned, 0));
const pct = computed(() => totalBudget.value > 0 ? Math.round(totalActual.value / totalBudget.value * 100) : 0);

const R = 42;
const C = 2 * Math.PI * R;
const arcs = computed(() => {
  let cum = 0;
  return cats.value.map((c) => {
    const p = totalBudget.value > 0 ? (c.actual / totalBudget.value) * 100 : 0;
    const dash = (p / 100) * C;
    const offset = (cum / 100) * C;
    cum += p;
    return { color: c.color, dash, offset };
  });
});
function fmt(n) { return 'Rp ' + (n / 1_000_000).toFixed(1).replace('.0', '') + 'jt'; }
</script>

<template>
  <div class="rounded-[18px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div>
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.budget.title') }}</h3>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">{{ t('dashboard.index.widgets.budget.sub', { total: budgetWidget.formatted?.total_budget ?? '-' }) }}</div>
      </div>
      <Link :href="route('dashboard.budget-planner.index')"
            class="px-3 py-1.5 rounded-full text-xs font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
        {{ t('dashboard.index.widgets.budget.detail') }}
      </Link>
    </div>

    <div v-if="cats.length" class="p-5 grid gap-6 items-center" style="grid-template-columns: 160px 1fr;">
      <div class="relative w-40 h-40">
        <svg viewBox="0 0 100 100" class="w-full h-full" style="transform: rotate(-90deg);">
          <circle cx="50" cy="50" :r="R" fill="none" stroke="#DCE4D3" stroke-width="12" />
          <circle v-for="(a, i) in arcs" :key="i" cx="50" cy="50" :r="R" fill="none"
                  :stroke="a.color" stroke-width="12"
                  :stroke-dasharray="`${a.dash} ${C - a.dash}`" :stroke-dashoffset="-a.offset" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <div class="text-[10px] uppercase tracking-wide font-semibold" style="color:#6C7A75;">{{ t('dashboard.index.widgets.budget.used') }}</div>
          <div class="font-cormorant font-medium text-3xl leading-none" style="color:#1F2A2E;">{{ pct }}%</div>
          <div class="text-[11px] mt-0.5" style="color:#6C7A75;">{{ fmt(totalActual) }}</div>
        </div>
      </div>

      <div class="flex flex-col gap-2">
        <div v-for="(c, i) in cats" :key="i" class="flex items-center gap-2.5">
          <span class="w-2 h-2 rounded-sm flex-shrink-0" :style="{ background: c.color }" />
          <span class="text-[12.5px] flex-1 truncate" style="color:#3D4A4D;">{{ c.name }}</span>
          <span class="font-jet text-[11.5px]" style="color:#6C7A75;">{{ (c.actual/1e6).toFixed(0) }}/{{ (c.planned/1e6).toFixed(0) }}jt</span>
          <div class="w-12 h-1 rounded-full" style="background:#DCE4D3;">
            <div class="h-full rounded-full" :style="{ width: (c.planned ? Math.min(c.actual/c.planned*100,100) : 0) + '%', background: c.color }" />
          </div>
        </div>
      </div>
    </div>
    <div v-else class="p-8 text-center text-sm" style="color:#6C7A75;">
      {{ t('dashboard.index.widgets.budget.empty') }}
    </div>
  </div>
</template>
