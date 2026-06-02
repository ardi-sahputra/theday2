<script setup>
import { computed } from 'vue';

const props = defineProps({
  sectionsData: { type: Object, required: true }, // reactive: { [key]: { data, is_enabled } }
  caps:         { type: Object, default: () => ({}) }, // template capability flags
});

const emit = defineEmits(['toggle-section']); // (key)

// [key, name, sub, capKey] — section_key matches backend (see SectionVariantSeeder).
// Required sections (cover/opening/events/closing) are omitted (toggle 422s them).
const ALL_ROWS = [
  ['love_story',     'Kisah Kami',     'Timeline cerita perjalanan kalian', 'loveStory'],
  ['quote',          'Quote / Salam',  'Kutipan pembuka di undangan',       'quote'],
  ['gallery',        'Galeri Foto',    'Foto pre-wedding',                  'gallery'],
  ['rsvp',           'Formulir RSVP',  'Konfirmasi kehadiran tamu',         'rsvp'],
  ['envelope',       'Amplop Digital', 'QRIS & nomor rekening',             'envelope'],
  ['gift',           'Hadiah',         'Daftar kado / gift registry',       'gift'],
  ['wishes',         'Ucapan & Doa',   'Buku tamu publik',                  'wishes'],
  ['live_streaming', 'Live Streaming', 'YouTube live untuk tamu jauh',      'liveStreaming'],
];

// Show a row only if the template supports it AND the section exists on this invitation.
const ROWS = computed(() => ALL_ROWS.filter(([key, , , capKey]) =>
  props.caps[capKey] !== false && props.sectionsData?.[key] != null
));

function isOn(key) { return !!props.sectionsData?.[key]?.is_enabled; }
</script>

<template>
  <div>
    <div class="section-block">
      <h4>Bagian Aktif</h4>
      <div class="desc">Atur bagian mana yang ingin ditampilkan di undangan.</div>

      <div v-for="[key, name, sub] in ROWS" :key="key" class="toggle">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6C7A75" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="9" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="18" r="1"/></svg>
        <div style="flex:1;">
          <div class="name">{{ name }}</div>
          <div class="sub">{{ sub }}</div>
        </div>
        <button type="button" :class="['toggle-sw', isOn(key) ? 'on' : '']"
                role="switch" :aria-checked="isOn(key)"
                @click="emit('toggle-section', key)"></button>
      </div>
    </div>
  </div>
</template>
