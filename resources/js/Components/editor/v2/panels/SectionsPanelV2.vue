<script setup>
const props = defineProps({
  sectionsData: { type: Object, required: true }, // reactive: { [key]: { data, is_enabled } }
});

const emit = defineEmits(['toggle-section']); // (key)

// [key, name, sub] — keys match real backend section_key values (see SectionVariantSeeder).
// Required sections (cover/opening/events/closing) are intentionally omitted —
// the toggle endpoint rejects them with 422.
const ROWS = [
  ['love_story', 'Kisah Kami',     'Timeline cerita perjalanan kalian'],
  ['quote',      'Quote / Salam',  'Kutipan pembuka di undangan'],
  ['gallery',    'Galeri Foto',    'Foto pre-wedding'],
  ['rsvp',       'Formulir RSVP',  'Konfirmasi kehadiran tamu'],
  ['envelope',   'Amplop Digital', 'QRIS & nomor rekening'],
  ['gift',       'Hadiah',         'Daftar kado / gift registry'],
  ['wishes',     'Ucapan & Doa',   'Buku tamu publik'],
];

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
