<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({ reminders: { type: Array, default: () => [] } }); // [{ when, title, who, urgent }]
const { t } = useLocale();
const AV = { bride: '#C7D3BC', groom: '#D9B5B0' };
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.rail.reminders.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.checklist.rail.reminders.sub') }}</p>
    </div>
    <div class="px-5 pb-4">
      <template v-if="reminders.length">
        <div v-for="(r, i) in reminders" :key="i" class="flex gap-2.5 py-2.5" :style="i ? 'border-top:1px solid #D8DFD2;' : ''">
          <div class="w-1 self-stretch rounded-full" :style="{ background: r.urgent ? '#C19089' : '#92A89C' }" />
          <div class="flex-1 min-w-0">
            <div class="font-jet text-[10.5px] font-semibold tracking-wide" :style="{ color: r.urgent ? '#C19089' : '#6C7A75' }">{{ r.when }}</div>
            <div class="text-[13px] mt-0.5 font-medium truncate" style="color:#1F2A2E;">{{ r.title }}</div>
          </div>
          <div v-if="r.who" class="w-5 h-5 rounded-full grid place-items-center text-[9px] font-bold font-cormorant flex-shrink-0"
               :style="{ background: AV[r.who] || '#DCE4D3', color:'#1F2A2E' }">{{ r.who === 'groom' ? 'R' : 'A' }}</div>
        </div>
      </template>
      <p v-else class="text-[12.5px] py-2" style="color:#6C7A75;">{{ t('dashboard.checklist.rail.reminders.empty') }}</p>
    </div>
  </div>
</template>
