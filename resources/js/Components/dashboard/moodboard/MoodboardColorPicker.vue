<script setup>
// Self-contained on-brand color picker: SV square + hue slider + hex input,
// plus wedding-friendly presets and palette suggestions pulled from the photos.
// Emits 'pick' with a #rrggbb hex when the user confirms a color.
import { ref, computed, watch, onUnmounted } from 'vue';

const props = defineProps({
  suggestions: { type: Array, default: () => [] }, // hex[] from uploaded photos
  initial:     { type: String, default: '#92a89c' },
});
const emit = defineEmits(['pick']);

const PRESETS = [
  '#8FA68E', '#6F8270', '#2C3A30', '#A7B5A0', // sages / greens
  '#D8C3A5', '#E3C9A8', '#C9A27A', '#B8956B', // creams / golds / tan
  '#E8C8C0', '#D9A7A0', '#B98E7A',            // blush / terracotta
  '#A8B8C8', '#6E7F94', '#2E3340',            // dusty blue / navy
];

// ── HSV state ─────────────────────────────────────────────────────────────────
const hue = ref(140), sat = ref(0.3), val = ref(0.66);
const hexText = ref('#92a89c');

const clamp = (v, lo = 0, hi = 1) => Math.min(hi, Math.max(lo, v));

function hsvToRgb(h, s, v) {
  const c = v * s, x = c * (1 - Math.abs(((h / 60) % 2) - 1)), m = v - c;
  let r = 0, g = 0, b = 0;
  if (h < 60)       { r = c; g = x; }
  else if (h < 120) { r = x; g = c; }
  else if (h < 180) { g = c; b = x; }
  else if (h < 240) { g = x; b = c; }
  else if (h < 300) { r = x; b = c; }
  else              { r = c; b = x; }
  return [Math.round((r + m) * 255), Math.round((g + m) * 255), Math.round((b + m) * 255)];
}
function rgbToHsv(r, g, b) {
  r /= 255; g /= 255; b /= 255;
  const max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min;
  let h = 0;
  if (d) {
    if (max === r)      h = 60 * (((g - b) / d) % 6);
    else if (max === g) h = 60 * ((b - r) / d + 2);
    else                h = 60 * ((r - g) / d + 4);
  }
  if (h < 0) h += 360;
  return [h, max ? d / max : 0, max];
}
const toHex = (r, g, b) => '#' + [r, g, b].map(n => n.toString(16).padStart(2, '0')).join('');

const currentHex = computed(() => {
  const [r, g, b] = hsvToRgb(hue.value, sat.value, val.value);
  return toHex(r, g, b);
});
watch(currentHex, h => { hexText.value = h; });

const hueColor = computed(() => {
  const [r, g, b] = hsvToRgb(hue.value, 1, 1);
  return toHex(r, g, b);
});

function applyHex(hex) {
  const h = (hex || '').trim().replace(/^#?/, '#').toLowerCase();
  if (!/^#[0-9a-f]{6}$/.test(h)) return false;
  const r = parseInt(h.slice(1, 3), 16), g = parseInt(h.slice(3, 5), 16), b = parseInt(h.slice(5, 7), 16);
  const [hh, ss, vv] = rgbToHsv(r, g, b);
  hue.value = hh; sat.value = ss; val.value = vv;
  return true;
}
function onHexInput() { applyHex(hexText.value); }

// init
applyHex(props.initial);

// ── SV square dragging ─────────────────────────────────────────────────────────
const svRef = ref(null);
function moveSv(e) {
  const r = svRef.value?.getBoundingClientRect();
  if (!r) return;
  sat.value = clamp((e.clientX - r.left) / r.width);
  val.value = 1 - clamp((e.clientY - r.top) / r.height);
}
function startSv(e) { moveSv(e); window.addEventListener('pointermove', moveSv); window.addEventListener('pointerup', endSv); }
function endSv() { window.removeEventListener('pointermove', moveSv); window.removeEventListener('pointerup', endSv); }

// ── Hue slider dragging ─────────────────────────────────────────────────────────
const hueRef = ref(null);
function moveHue(e) {
  const r = hueRef.value?.getBoundingClientRect();
  if (!r) return;
  hue.value = clamp((e.clientX - r.left) / r.width) * 360;
}
function startHue(e) { moveHue(e); window.addEventListener('pointermove', moveHue); window.addEventListener('pointerup', endHue); }
function endHue() { window.removeEventListener('pointermove', moveHue); window.removeEventListener('pointerup', endHue); }

onUnmounted(() => { endSv(); endHue(); });

function choose(hex) { emit('pick', hex); }
</script>

<template>
  <div class="cp">
    <!-- SV square -->
    <div ref="svRef" class="cp-sv" :style="{ background: hueColor }" @pointerdown.prevent="startSv">
      <div class="cp-sv-white"></div>
      <div class="cp-sv-black"></div>
      <div class="cp-sv-thumb" :style="{ left: (sat * 100) + '%', top: ((1 - val) * 100) + '%', background: currentHex }"></div>
    </div>

    <!-- Hue slider -->
    <div ref="hueRef" class="cp-hue" @pointerdown.prevent="startHue">
      <div class="cp-hue-thumb" :style="{ left: (hue / 360 * 100) + '%' }"></div>
    </div>

    <!-- Hex + preview + add -->
    <div class="cp-row">
      <span class="cp-prev" :style="{ background: currentHex }"></span>
      <input class="cp-hex" v-model="hexText" spellcheck="false" maxlength="7" @input="onHexInput" />
      <button type="button" class="cp-add" @click="choose(currentHex)">Tambah</button>
    </div>

    <!-- Presets -->
    <div class="cp-label">Warna nikah</div>
    <div class="cp-dots">
      <button v-for="c in PRESETS" :key="c" type="button" class="cp-dot" :style="{ background: c }" :title="c" @click="choose(c)"></button>
    </div>

    <!-- From photos -->
    <template v-if="suggestions.length">
      <div class="cp-label">Dari fotomu</div>
      <div class="cp-dots">
        <button v-for="c in suggestions" :key="c" type="button" class="cp-dot" :style="{ background: c }" :title="c" @click="choose(c)"></button>
      </div>
    </template>
  </div>
</template>

<style scoped>
.cp { width: 232px; }
.cp-sv { position: relative; width: 100%; height: 132px; border-radius: 10px; overflow: hidden; cursor: crosshair; touch-action: none; }
.cp-sv-white { position: absolute; inset: 0; background: linear-gradient(to right, #fff, rgba(255,255,255,0)); }
.cp-sv-black { position: absolute; inset: 0; background: linear-gradient(to top, #000, rgba(0,0,0,0)); }
.cp-sv-thumb { position: absolute; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,.35); transform: translate(-50%, -50%); pointer-events: none; }
.cp-hue { position: relative; height: 14px; border-radius: 7px; margin: 10px 0; cursor: pointer; touch-action: none;
  background: linear-gradient(to right, #f00 0%, #ff0 17%, #0f0 33%, #0ff 50%, #00f 67%, #f0f 83%, #f00 100%); }
.cp-hue-thumb { position: absolute; top: 50%; width: 14px; height: 14px; border-radius: 50%; background: #fff; border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,.35); transform: translate(-50%, -50%); pointer-events: none; }
.cp-row { display: flex; align-items: center; gap: 7px; }
.cp-prev { width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0; box-shadow: 0 0 0 1px rgba(0,0,0,.08); }
.cp-hex { flex: 1; min-width: 0; border: 1px solid #e3e7df; border-radius: 8px; padding: 7px 9px; font: 12px 'JetBrains Mono', monospace; text-transform: lowercase; outline: none; }
.cp-hex:focus { border-color: #92A89C; }
.cp-add { font: 600 11px system-ui; color: #fff; background: #1F2A2E; border: none; padding: 8px 11px; border-radius: 8px; cursor: pointer; }
.cp-label { font: 600 10px system-ui; color: #9aa6a0; margin: 11px 0 5px; }
.cp-dots { display: flex; flex-wrap: wrap; gap: 5px; }
.cp-dot { width: 22px; height: 22px; border-radius: 6px; border: none; cursor: pointer; box-shadow: 0 0 0 1px rgba(0,0,0,.08); }
.cp-dot:hover { transform: scale(1.12); }
</style>
