<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  checklistWidget: { type: Object, required: true },
  countdown:       { type: Object, default: null },
});
const { t } = useLocale();

const initialized = computed(() => !!props.checklistWidget?.initialized);
const tasks = computed(() => props.checklistWidget?.upcoming_tasks ?? []);
const isAllDone = computed(() =>
    (props.checklistWidget?.total ?? 0) > 0 &&
    props.checklistWidget?.done === props.checklistWidget?.total
);

function hLabel(task) {
  if (!props.countdown?.target || !task.due_date) return '';
  const wd  = new Date(props.countdown.target).getTime();
  const due = new Date(task.due_date).getTime();
  const days = Math.round((wd - due) / 86400000);
  return days >= 0 ? `H-${days}` : `H+${Math.abs(days)}`;
}
</script>

<template>
  <div class="rounded-[18px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">

    <!-- Header — title + progress only, no action buttons -->
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div>
        <h3 class="font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">
          {{ initialized ? t('dashboard.index.widgets.checklist.title') : t('dashboard.index.widgets.checklist.titlePlain') }}
        </h3>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">
          <template v-if="initialized">{{ t('dashboard.index.widgets.checklist.sub', { done: checklistWidget.done, total: checklistWidget.total }) }}</template>
          <template v-else>{{ t('dashboard.index.widgets.checklist.subNotStarted') }}</template>
        </div>
      </div>
      <!-- Lihat semua — only once there's something to see -->
      <Link v-if="initialized" :href="route('dashboard.checklist.index')"
            class="text-xs font-semibold"
            style="color:#92A89C;">
        Lihat semua →
      </Link>
    </div>

    <!-- Task list -->
    <div v-if="tasks.length" class="px-0 py-0">
      <div v-for="(it, i) in tasks" :key="it.id"
           class="flex items-start gap-3.5 px-5 py-3"
           :style="i < tasks.length - 1 ? 'border-bottom:1px solid #D8DFD2;' : ''">
        <span class="w-5 h-5 rounded-md grid place-items-center flex-shrink-0 mt-0.5"
              style="border:2px solid #C7D0BE;" />
        <div class="flex-1 min-w-0">
          <div class="text-[13.5px] leading-snug" style="color:#1F2A2E;">{{ it.title }}</div>
          <div class="text-[11px] mt-0.5" style="color:#9AA69F;">
            {{ t('dashboard.index.widgets.checklist.subNotStarted') }}<template v-if="hLabel(it)"> · {{ hLabel(it) }}</template>
          </div>
        </div>
        <span v-if="it.is_overdue" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full shrink-0"
              style="color:#C19089; background: rgba(217,181,176,0.2);">{{ t('dashboard.index.widgets.checklist.urgent') }}</span>
      </div>

      <!-- Footer actions when tasks are visible -->
      <div class="flex items-center gap-2 px-5 py-4" style="border-top:1px solid #D8DFD2;">
        <Link :href="route('dashboard.checklist.index')"
              class="inline-flex min-h-[44px] items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold"
              style="background:#FBFCF9; color:#4A5A4C; border:1.5px solid #92A89C;">
          <WidgetIcon name="plus" :size="12" stroke="#4A5A4C" /> {{ t('dashboard.index.widgets.checklist.add') }}
        </Link>
        <Link :href="route('dashboard.checklist.index') + '?tab=dokumen'"
              class="text-xs font-semibold"
              style="color:#6C7A75;">
          {{ t('dashboard.documents.title') }} →
        </Link>
      </div>
    </div>

    <!-- All-done: brief celebratory line, no CTA needed -->
    <div v-else-if="isAllDone" class="px-5 py-5 text-center text-sm" style="color:#6C7A75;">
      {{ t('dashboard.index.widgets.checklist.allDone') }}
    </div>

    <!-- Not started yet: compact empty state, not a big block -->
    <div v-else class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4">
      <p class="flex-1 min-w-0 text-[13px] leading-snug" style="color:#6C7A75;">
        {{ t('dashboard.index.widgets.checklist.emptyBody') }}
      </p>
      <Link :href="route('dashboard.checklist.index')"
            class="shrink-0 inline-flex min-h-[44px] items-center justify-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold"
            style="background:#FBFCF9; color:#4A5A4C; border:1.5px solid #92A89C;">
        <WidgetIcon name="plus" :size="12" stroke="#4A5A4C" /> {{ t('dashboard.index.widgets.checklist.add') }}
      </Link>
    </div>

  </div>
</template>
