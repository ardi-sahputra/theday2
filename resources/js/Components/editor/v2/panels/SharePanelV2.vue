<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  slug:   { type: String, default: '' },
  config: { type: Object, default: () => ({}) }, // reactive custom_config; wa_template lives here
});

const emit = defineEmits([
  'save-config',  // (patch) — persist custom_config patch
]);

const DEFAULT_WA = 'Assalamualaikum, dengan hormat kami mengundang Bapak/Ibu {nama} pada pernikahan kami. Detail lengkap: {link}';

const fullUrl  = computed(() => `theday.id/${props.slug}`);
const shareUrl = computed(() => `${window.location.origin}/${props.slug}`);

const copied = ref(false);
async function copyLink() {
  try {
    await navigator.clipboard.writeText(shareUrl.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
  } catch (_) { /* clipboard unavailable */ }
}

// WA template (stored in custom_config.wa_template) ──────────────────────────
const waTemplate = ref(props.config?.wa_template ?? DEFAULT_WA);
let waTimer = null;
function onWaInput() {
  clearTimeout(waTimer);
  waTimer = setTimeout(() => emit('save-config', { wa_template: waTemplate.value }), 1500);
}

// Per-guest personalization (stored in custom_config.guest_per_link) ──────────
const perLink = computed(() => !!props.config?.guest_per_link);
function togglePerLink() { emit('save-config', { guest_per_link: !perLink.value }); }

function shareWa() {
  const msg = waTemplate.value
    .replaceAll('{nama}', '')
    .replaceAll('{link}', shareUrl.value)
    .replaceAll('{tanggal}', '');
  window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
}
</script>

<template>
  <div>
    <!-- Link Undangan -->
    <div class="section-block">
      <h4>Link Undangan</h4>
      <div class="desc">Link unik undangan kamu.</div>
      <div style="display:flex;gap:6px;align-items:center;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:8px 12px;">
        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--ink);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ fullUrl }}</span>
        <button type="button" class="icon-btn" style="width:28px;height:28px;background:transparent;border:none;flex-shrink:0;" @click="copyLink" :title="copied ? 'Tersalin' : 'Salin link'">
          <svg v-if="!copied" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6F8270" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6F8270" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-11"/></svg>
        </button>
      </div>
    </div>

    <!-- Tamu Per Link -->
    <div class="section-block">
      <h4>Tamu Per Link</h4>
      <div class="desc">Setiap tamu dapat link berbeda dengan nama personal.</div>
      <div class="toggle">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6F8270" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"/><circle cx="17" cy="10" r="2.5"/><path d="M14 20c0-2 1-3.5 3-3.5s3 1.5 3 3.5"/></svg>
        <div style="flex:1;">
          <div class="name">Personalisasi otomatis</div>
          <div class="sub">Sisipkan nama tamu pada tiap link</div>
        </div>
        <button type="button" :class="['toggle-sw', perLink ? 'on' : '']"
                role="switch" :aria-checked="perLink" @click="togglePerLink"></button>
      </div>
    </div>

    <!-- Sebar via WhatsApp -->
    <div class="section-block">
      <h4>Sebar via WhatsApp</h4>
      <div class="field">
        <label class="label">Template pesan</label>
        <textarea class="textarea" rows="5" v-model="waTemplate" @input="onWaInput"></textarea>
        <div class="help">Variabel: {nama}, {link}, {tanggal}</div>
      </div>
      <button type="button" class="btn btn-primary" style="width:100%;" @click="shareWa">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg>
        Sebar ke WhatsApp
      </button>
    </div>
  </div>
</template>
