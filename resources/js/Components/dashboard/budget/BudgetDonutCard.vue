<script setup>
import { computed } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ categories: { type: Array, default: () => [] } });
const { t } = useLocale();

const totalActual = computed(() => props.categories.reduce((a, c) => a + (c.actual_total || 0), 0));
const totalPlanned = computed(() => props.categories.reduce((a, c) => a + (c.planned_total || 0), 0));
const pct = computed(() => totalPlanned.value > 0 ? Math.round(totalActual.value / totalPlanned.value * 100) : 0);

const R = 42;
const C = 2 * Math.PI * R;
const arcs = computed(() => {
  let cum = 0;
  return props.categories.map((c) => {
    const p = totalPlanned.value > 0 ? (c.actual_total / totalPlanned.value) * 100 : 0;
    const dash = (p / 100) * C;
    const off = -(cum / 100) * C;
    cum += p;
    return { color: c.color, dash, off };
  });
});
function jt(n) { return 'Rp ' + Math.round((n || 0) / 1_000_000) + 'jt'; }
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.donut.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.budget.donut.sub') }}</p>
    </div>
    <div class="px-5 pb-5">
      <div v-if="categories.length" class="relative w-[200px] h-[200px] mx-auto">
        <svg viewBox="0 0 100 100" class="w-full h-full" style="transform: rotate(-90deg);">
          <circle cx="50" cy="50" :r="R" fill="none" stroke="#DCE4D3" stroke-width="13" />
          <circle v-for="(a, i) in arcs" :key="i" cx="50" cy="50" :r="R" fill="none"
                  :stroke="a.color" stroke-width="13"
                  :stroke-dasharray="`${a.dash} ${C - a.dash}`" :stroke-dashoffset="a.off" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <div class="text-[10.5px] uppercase tracking-wide font-semibold" style="color:#6C7A75;">{{ t('dashboard.budget.donut.used') }}</div>
          <div class="font-cormorant font-medium text-[38px] leading-none" style="color:#1F2A2E;">{{ pct }}%</div>
          <div class="text-[12px] mt-1" style="color:#6C7A75;">{{ jt(totalActual) }} / {{ jt(totalPlanned) }}</div>
        </div>
      </div>
      <p v-else class="text-[12.5px] py-6 text-center" style="color:#6C7A75;">{{ t('dashboard.budget.donut.empty') }}</p>
    </div>
  </div>
</template>
