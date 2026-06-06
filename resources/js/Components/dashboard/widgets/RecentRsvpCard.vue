<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  recentRsvps: { type: Array, default: () => [] },
});
const { t } = useLocale();

const AV = ['#C7D3BC', '#D9B5B0', '#E9DFC4', '#C7D3BC', '#E4ECDF'];
const rows = computed(() => props.recentRsvps.map((r, i) => ({
  ...r,
  initials: (r.guest_name || '?').split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase(),
  color: AV[i % AV.length],
  attending: r.attendance === 'hadir',
})));
</script>

<template>
  <div class="rounded-[18px] overflow-hidden" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div>
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.rsvp.title') }}</h3>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">{{ t('dashboard.index.widgets.rsvp.sub') }}</div>
      </div>
      <Link :href="route('dashboard.rsvp.index')" class="text-[12.5px] font-semibold" style="color:#6F8270;">
        {{ t('dashboard.index.widgets.rsvp.all') }} →
      </Link>
    </div>
    <div v-if="rows.length">
      <div v-for="(g, i) in rows" :key="i" class="flex items-center gap-3 px-5 py-3.5"
           :style="i < rows.length - 1 ? 'border-bottom:1px solid #D8DFD2;' : ''">
        <div class="w-9 h-9 rounded-full grid place-items-center text-[11px] font-bold font-cormorant flex-shrink-0"
             :style="{ background: g.color, color:'#1F2A2E' }">{{ g.initials }}</div>
        <div class="flex-1 min-w-0">
          <div class="text-[13.5px] font-semibold truncate" style="color:#1F2A2E;">{{ g.guest_name }}</div>
          <div class="text-[11.5px] truncate" style="color:#6C7A75;">{{ g.invitation_title }} · {{ t('dashboard.index.widgets.rsvp.persons', { n: g.guest_count }) }}</div>
        </div>
        <div class="text-right flex-shrink-0">
          <div class="text-[11px] font-semibold inline-flex items-center gap-1" :style="{ color: g.attending ? '#6F8270' : '#D9A24A' }">
            <span class="w-1.5 h-1.5 rounded-full" :style="{ background: g.attending ? '#92A89C' : '#D9A24A' }" />
            {{ g.attending ? t('dashboard.index.widgets.rsvp.attending') : t('dashboard.index.widgets.rsvp.notAttending') }}
          </div>
          <div class="text-[10.5px] mt-0.5" style="color:#6C7A75;">{{ g.created_at_human }}</div>
        </div>
      </div>
    </div>
    <div v-else class="p-8 text-center text-sm" style="color:#6C7A75;">{{ t('dashboard.index.widgets.rsvp.empty') }}</div>
  </div>
</template>
