<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();

const props = defineProps({
  activityFeed: { type: Array, default: () => [] },
});

// type → icon, color, i18n keypath, and which named slot holds the bold value
const META = {
  rsvp_attending: { icon: 'msg',    color: '#92A89C', key: 'rsvpAttending', slot: 'name' },
  rsvp_declined:  { icon: 'msg',    color: '#D9A24A', key: 'rsvpDeclined',  slot: 'name' },
  ucapan:         { icon: 'msg',    color: '#6F8270', key: 'ucapan',        slot: 'name' },
  task_done:      { icon: 'check',  color: '#92A89C', key: 'taskDone',      slot: 'title' },
  vendor_booked:  { icon: 'vendor', color: '#D9B5B0', key: 'vendorBooked',  slot: 'name' },
};

const items = computed(() => (props.activityFeed ?? []).map((a) => {
  const meta = META[a.type] ?? { icon: 'msg', color: '#6C7A75', key: 'ucapan', slot: 'name' };
  const bold = a.title ?? a.name ?? t('dashboard.index.widgets.activity.someone');
  return {
    icon:    meta.icon,
    color:   meta.color,
    keypath: `dashboard.index.widgets.activity.${meta.key}`,
    slot:    meta.slot,
    bold,
    time:    a.time,
  };
}));
</script>

<template>
  <div class="rounded-[18px] overflow-hidden" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.activity.title') }}</h3>
    </div>

    <!-- Real activity -->
    <div v-if="items.length" class="py-3">
      <div v-for="(it, i) in items" :key="i" class="flex items-start gap-3 px-5 py-2.5">
        <div class="w-7 h-7 rounded-lg grid place-items-center flex-shrink-0" :style="{ background: it.color + '2E', color: it.color }">
          <WidgetIcon :name="it.icon" :size="13" :stroke="it.color" />
        </div>
        <div class="flex-1 min-w-0">
          <!-- i18n-t auto-escapes the slot content → safe with user-supplied names -->
          <i18n-t :keypath="it.keypath" tag="div" scope="global"
                  class="text-[12.5px] leading-snug" style="color:#3D4A4D;">
            <template #[it.slot]>
              <strong style="color:#1F2A2E;">{{ it.bold }}</strong>
            </template>
          </i18n-t>
          <div class="text-[10.5px] font-jet mt-0.5" style="color:#6C7A75;">{{ it.time }}</div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="px-5 py-8 text-center text-sm" style="color:#6C7A75;">
      {{ t('dashboard.index.widgets.activity.empty') }}
    </div>
  </div>
</template>
