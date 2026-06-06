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
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.checklist.title') }}</h3>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">
          {{ t('dashboard.index.widgets.checklist.sub', { done: checklistWidget.done, total: checklistWidget.total }) }}
        </div>
      </div>
      <!-- Lihat semua — subtle link -->
      <Link :href="route('dashboard.checklist.index')"
            class="text-xs font-semibold"
            style="color:#92A89C;">
        Lihat semua →
      </Link>
    </div>

    <!-- Task list -->
    <div v-if="tasks.length" class="px-0 py-0">
      <div v-for="(it, i) in tasks" :key="it.id"
           class="flex items-center gap-3.5 px-5 py-3.5"
           :style="i < tasks.length - 1 ? 'border-bottom:1px solid #D8DFD2;' : ''">
        <span class="w-5 h-5 rounded-md grid place-items-center flex-shrink-0"
              style="border:2px solid #C7D0BE;" />
        <div class="font-jet text-[11px] min-w-[44px]" style="color:#6C7A75;">{{ hLabel(it) }}</div>
        <div class="flex-1 text-[13.5px]" style="color:#1F2A2E;">{{ it.title }}</div>
        <span v-if="it.is_overdue" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
              style="color:#C19089; background: rgba(217,181,176,0.2);">{{ t('dashboard.index.widgets.checklist.urgent') }}</span>
      </div>

      <!-- Footer actions when tasks are visible -->
      <div class="flex items-center gap-2 px-5 py-4" style="border-top:1px solid #D8DFD2;">
        <Link :href="route('dashboard.checklist.index')"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold"
              style="background:#1F2A2E; color:#FBFCF9;">
          <WidgetIcon name="plus" :size="12" stroke="#FBFCF9" /> {{ t('dashboard.index.widgets.checklist.add') }}
        </Link>
        <Link :href="route('dashboard.checklist.index') + '?tab=dokumen'"
              class="text-xs font-semibold"
              style="color:#6C7A75;">
          {{ t('dashboard.documents.title') }} →
        </Link>
      </div>
    </div>

    <!-- Empty / all-done state -->
    <div v-else class="px-5 py-7 text-center">
      <p class="text-sm mb-5" style="color:#6C7A75;">
        {{ isAllDone ? t('dashboard.index.widgets.checklist.allDone') : t('dashboard.index.widgets.checklist.empty') }}
      </p>
      <div class="flex items-center justify-center gap-3">
        <Link :href="route('dashboard.checklist.index')"
              class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-full text-xs font-semibold"
              style="background:#1F2A2E; color:#FBFCF9;">
          <WidgetIcon name="plus" :size="12" stroke="#FBFCF9" /> {{ t('dashboard.index.widgets.checklist.add') }}
        </Link>
        <Link :href="route('dashboard.checklist.index') + '?tab=dokumen'"
              class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-full text-xs font-semibold"
              style="color:#4A5A4C; border:1px solid #C7D0BE;">
          {{ t('dashboard.documents.title') }}
        </Link>
      </div>
    </div>

  </div>
</template>
