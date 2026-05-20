<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();

const items = [
  { time: 'sekarang', who: 'Sistem',     what: 'auto-reminder ke 12 tamu yang belum RSVP terkirim', icon: 'msg',     color: '#92A89C' },
  { time: '15m',      who: 'Rizki',      what: 'menandai "Booking MUA & hairdo" selesai',           icon: 'check',   color: '#92A89C' },
  { time: '1j',       who: 'Bp. Surya',  what: 'mengirim amplop · Rp 500.000',                       icon: 'gift',    color: '#D9B5B0' },
  { time: '2j',       who: 'Ayu',        what: 'mengganti palet undangan ke "Daun"',                 icon: 'invite',  color: '#D9A24A' },
  { time: 'kemarin',  who: 'Tim TheDay', what: 'menjadwalkan konsultasi vendor 24 Jun',              icon: 'compass', color: '#6F8270' },
];
</script>

<template>
  <div class="rounded-[18px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div class="flex items-center gap-2">
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.activity.title') }}</h3>
        <DemoBadge />
      </div>
    </div>
    <div class="py-3">
      <div v-for="(it, i) in items" :key="i" class="flex items-start gap-3 px-5 py-2.5">
        <div class="w-7 h-7 rounded-lg grid place-items-center flex-shrink-0" :style="{ background: it.color + '2E', color: it.color }">
          <WidgetIcon :name="it.icon" :size="13" :stroke="it.color" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[12.5px] leading-snug" style="color:#3D4A4D;"><strong style="color:#1F2A2E;">{{ it.who }}</strong> {{ it.what }}</div>
          <div class="text-[10.5px] font-jet mt-0.5" style="color:#6C7A75;">{{ it.time }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
