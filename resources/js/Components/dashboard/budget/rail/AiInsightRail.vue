<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
import { ref, onMounted } from 'vue';

const props = defineProps({
  // Insights delivered with the page payload (from DB) — { enabled, insights, fresh, limited }.
  initial: { type: Object, default: () => ({ enabled: true, insights: [], fresh: true }) },
});

const { t } = useLocale();

const loading  = ref(false);
const enabled  = ref(props.initial.enabled !== false);
const limited  = ref(props.initial.limited === true);
const insights = ref(props.initial.insights ?? []);

const SEVERITY = {
  alert:   { dot: '#B4524A', text: '#7A2E27' },
  warning: { dot: '#A77B1E', text: '#5A4B1A' },
  info:    { dot: '#5E6F64', text: '#3C4A41' },
};

function sev(s) {
  return SEVERITY[s] || SEVERITY.info;
}

// Only hits the API. Used when the stored insights are stale (data changed) or
// on an explicit manual refresh. Unchanged data never reaches here.
async function refresh() {
  loading.value = true;
  try {
    const { data } = await window.axios.get(route('dashboard.budget-planner.insights'));
    enabled.value  = data.enabled !== false;
    limited.value  = data.limited === true;
    insights.value = data.insights ?? [];
  } catch {
    // Keep whatever we already had; don't blank the rail on a transient error.
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  // Data changed since last generation → regenerate once in the background.
  // Same data → use what the page already gave us, no request, no 429.
  if (enabled.value && props.initial.fresh === false) {
    refresh();
  }
});
</script>

<template>
  <div v-if="enabled" class="rounded-[16px]" style="background: linear-gradient(135deg, #F4EDDC, #E9DFC4); border:1px solid #E0D2BD;">
    <div class="px-5 pt-4 pb-3 flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <WidgetIcon name="sparkle" :size="16" stroke="#8E6515" class="flex-shrink-0" />
        <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.rail.ai.title') }}</h3>
      </div>
      <button v-if="!loading" @click="refresh" class="text-[11px] font-medium opacity-60 hover:opacity-100 transition-opacity" style="color:#5A4B1A;">
        {{ t('dashboard.budget.rail.ai.refresh') }}
      </button>
    </div>

    <div class="px-5 pb-5">
      <!-- Loading -->
      <div v-if="loading" class="space-y-2.5">
        <div class="h-3 rounded-full bg-[#D9C9A8]/60 animate-pulse w-3/4"></div>
        <div class="h-3 rounded-full bg-[#D9C9A8]/60 animate-pulse w-full"></div>
        <div class="h-3 rounded-full bg-[#D9C9A8]/60 animate-pulse w-2/3"></div>
        <p class="text-[11px] mt-2" style="color:#8A7A4A;">{{ t('dashboard.budget.rail.ai.loading') }}</p>
      </div>

      <!-- Daily cap reached, nothing cached to show -->
      <p v-else-if="limited && !insights.length" class="text-[12.5px] leading-relaxed" style="color:#5A4B1A;">
        {{ t('dashboard.budget.rail.ai.limited') }}
      </p>

      <!-- Empty -->
      <p v-else-if="!insights.length" class="text-[12.5px] leading-relaxed" style="color:#5A4B1A;">
        {{ t('dashboard.budget.rail.ai.empty') }}
      </p>

      <!-- Insights -->
      <div v-else class="space-y-3">
        <div v-for="(ins, idx) in insights" :key="idx" class="flex items-start gap-2.5">
          <span class="flex-shrink-0 mt-1.5 w-1.5 h-1.5 rounded-full" :style="{ background: sev(ins.severity).dot }"></span>
          <div>
            <p class="text-[12.5px] font-semibold leading-snug" :style="{ color: sev(ins.severity).text }">{{ ins.title }}</p>
            <p class="text-[12px] leading-relaxed mt-0.5" style="color:#5A4B1A;">{{ ins.body }}</p>
          </div>
        </div>
        <p class="text-[10.5px] pt-1 opacity-70" style="color:#8A7A4A;">{{ t('dashboard.budget.rail.ai.disclaimer') }}</p>
      </div>
    </div>
  </div>
</template>
