<script setup>
import { computed } from 'vue';

const props = defineProps({
  events: { type: Array,  required: true }, // [{ id, event_name, event_date, start_time, end_time, venue_name, venue_address, maps_url }]
  config: { type: Object, default: () => ({}) }, // custom_config; livestream flag lives here
  caps:   { type: Object, default: () => ({}) }, // template capability flags
});

const emit = defineEmits([
  'add-event',    // ()
  'save-event',   // (event) — debounced per-field
  'delete-event', // (event)
  'save-config',  // (patch) — Live Streaming flag
]);

function onField(ev) { emit('save-event', ev); }

// Live Streaming is a custom_config flag (no dedicated backend section key).
const livestreamOn = computed(() => !!props.config?.livestream_enabled);
function toggleLivestream() { emit('save-config', { livestream_enabled: !livestreamOn.value }); }
</script>

<template>
  <div>
    <div class="section-block">
      <h4>Acara</h4>
      <div class="desc">Tambah sebanyak acara yang kamu butuhkan.</div>

      <div v-for="ev in events" :key="ev.id" class="ev-card">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6C7A75" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="9" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="18" r="1"/></svg>
          <input class="input" style="font-weight:600;" v-model="ev.event_name" @input="onField(ev)" placeholder="Nama acara (mis. Akad Nikah)" />
          <button type="button" class="icon-btn" style="width:30px;height:30px;flex-shrink:0;" @click="emit('delete-event', ev)" title="Hapus">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#C19089" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
          </button>
        </div>
        <div class="field-row" style="margin-bottom:8px;">
          <div class="field" style="margin-bottom:0;">
            <label class="label">Tanggal</label>
            <input class="input" type="date" v-model="ev.event_date" @input="onField(ev)" />
          </div>
          <div class="field" style="margin-bottom:0;">
            <label class="label">Waktu</label>
            <div style="display:flex;gap:6px;">
              <input class="input" type="time" v-model="ev.start_time" @input="onField(ev)" />
              <input class="input" type="time" v-model="ev.end_time" @input="onField(ev)" />
            </div>
          </div>
        </div>
        <div class="field" style="margin-bottom:8px;">
          <label class="label">Lokasi</label>
          <input class="input" v-model="ev.venue_name" @input="onField(ev)" placeholder="Nama venue" />
        </div>
        <div class="field" style="margin-bottom:8px;">
          <label class="label">Alamat</label>
          <input class="input" v-model="ev.venue_address" @input="onField(ev)" placeholder="Alamat lengkap" />
        </div>
        <div class="field" style="margin-bottom:0;">
          <label class="label">Google Maps</label>
          <input class="input" type="url" v-model="ev.maps_url" @input="onField(ev)" placeholder="https://maps.google.com/…" />
        </div>
      </div>

      <p v-if="!events.length" class="help" style="text-align:center;padding:8px 0 14px;">Belum ada acara.</p>

      <button type="button" class="btn btn-ghost" style="width:100%;justify-content:center;" @click="emit('add-event')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Tambah acara
      </button>
    </div>

    <div v-if="caps.liveStreaming" class="section-block">
      <h4>Live Streaming</h4>
      <div class="toggle">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6F8270" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/></svg>
        <div style="flex:1;">
          <div class="name">YouTube Live</div>
          <div class="sub">Untuk tamu yang tidak bisa hadir</div>
        </div>
        <button type="button" :class="['toggle-sw', livestreamOn ? 'on' : '']"
                role="switch" :aria-checked="livestreamOn"
                @click="toggleLivestream"></button>
      </div>
    </div>
  </div>
</template>
