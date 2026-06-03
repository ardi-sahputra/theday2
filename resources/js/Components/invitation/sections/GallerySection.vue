<script setup>
// Shared, themeable photo gallery with switchable layouts.
// Add a new layout = add a `.gs--<name>` block here → every template that
// renders <GallerySection> gets it, no per-template edits.
//
//   <GallerySection :galleries="g" layout="masonry" :primary-color="c" />
//
import { ref, watch, nextTick } from 'vue';
import { useStaggerReveal } from '@/Composables/useReveal.js';

const props = defineProps({
  galleries:    { type: Array,  default: () => [] },   // [{ id, image_url|file_url, caption }]
  layout:       { type: String, default: 'grid' },     // grid | masonry | carousel | polaroid
  primaryColor: { type: String, default: '#92A89C' },
});

// Premium templates pass `file_url`; default renderer passes `image_url`.
const src = (p) => p?.image_url || p?.file_url || '';

const track = ref(null);
useStaggerReveal(track, '.gs-item', 55);

// Carousel drag-to-scroll (mouse + touch). The phone-mockup ancestor restricts
// touch-action to pan-y and doesn't drag overflow-x with a mouse, so the
// carousel needs to handle horizontal dragging itself.
const drag = { active: false, startX: 0, startScroll: 0, id: null, moved: false };
function onDown(e) {
  if (props.layout !== 'carousel' || !track.value) return;
  drag.startX = e.clientX; drag.startScroll = track.value.scrollLeft;
  drag.id = e.pointerId; drag.active = false; drag.moved = false;
}
function onMove(e) {
  if (props.layout !== 'carousel' || drag.id !== e.pointerId || !track.value) return;
  const dx = e.clientX - drag.startX;
  if (!drag.active) { if (Math.abs(dx) < 5) return; drag.active = true; drag.moved = true; track.value.setPointerCapture?.(e.pointerId); }
  track.value.scrollLeft = drag.startScroll - dx;
  e.preventDefault(); e.stopPropagation();
}
function onUp(e) {
  if (drag.id !== e.pointerId) return;
  drag.active = false; drag.id = null;
}
// New photos added after the observer fired (live editor) must be revealed too.
watch(() => props.galleries.length, () => {
  nextTick(() => track.value?.querySelectorAll('.gs-item').forEach((el) => el.classList.add('visible')));
});

// ── Lightbox ──────────────────────────────────────────────────────
const idx  = ref(null);
const open = ref(false);
function openLightbox(i) {
  if (drag.moved) { drag.moved = false; return; } // was a carousel drag, not a tap
  idx.value = i; open.value = true; document.body.style.overflow = 'hidden';
}
function close()  { open.value = false; document.body.style.overflow = ''; }
function prev()   { idx.value = (idx.value - 1 + props.galleries.length) % props.galleries.length; }
function next()   { idx.value = (idx.value + 1) % props.galleries.length; }
let touchX = 0;
function onTouchStart(e) { touchX = e.touches[0].clientX; }
function onTouchEnd(e) { const d = touchX - e.changedTouches[0].clientX; if (Math.abs(d) > 50) (d > 0 ? next() : prev()); }
</script>

<template>
  <div class="gs" :class="`gs--${layout}`" :style="{ '--gs-primary': primaryColor }">
    <div ref="track" class="gs-track"
         @pointerdown="onDown" @pointermove="onMove" @pointerup="onUp" @pointercancel="onUp">
      <button
        v-for="(photo, i) in galleries" :key="photo.id"
        type="button" class="gs-item reveal-scale" @click="openLightbox(i)"
      >
        <img :src="src(photo)" :alt="photo.caption || `Foto ${i + 1}`" loading="lazy" />
      </button>
    </div>

    <Teleport to="body">
      <Transition name="gs-fade">
        <div v-if="open" class="gs-lb" @touchstart="onTouchStart" @touchend="onTouchEnd">
          <button class="gs-lb-btn gs-lb-close" @click="close" aria-label="Tutup">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
          <div class="gs-lb-count">{{ idx + 1 }} / {{ galleries.length }}</div>
          <img v-if="idx !== null" :src="src(galleries[idx])" :alt="galleries[idx].caption || ''" class="gs-lb-img" />
          <p v-if="galleries[idx]?.caption" class="gs-lb-cap">{{ galleries[idx].caption }}</p>
          <template v-if="galleries.length > 1">
            <button class="gs-lb-btn gs-lb-prev" @click="prev" aria-label="Sebelumnya"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg></button>
            <button class="gs-lb-btn gs-lb-next" @click="next" aria-label="Berikutnya"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></button>
          </template>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.gs-item { display:block; padding:0; border:none; background:none; cursor:pointer; width:100%; overflow:hidden; }
.gs-item img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .5s ease; }
.gs-item:hover img { transform:scale(1.06); }

/* ── grid: equal squares, 3 cols ─────────────────────────────── */
.gs--grid .gs-track { display:grid; grid-template-columns:repeat(3,1fr); gap:2px; }
.gs--grid .gs-item { aspect-ratio:1; }

/* ── masonry: natural heights, 2 cols (CSS columns) ──────────── */
.gs--masonry .gs-track { column-count:2; column-gap:8px; padding:0 8px; }
.gs--masonry .gs-item { width:100%; margin:0 0 8px; border-radius:10px; break-inside:avoid; }
.gs--masonry .gs-item img { height:auto; border-radius:10px; }

/* ── carousel: horizontal scroll-snap ────────────────────────── */
.gs--carousel .gs-track { display:flex; gap:10px; overflow-x:auto; scroll-snap-type:x mandatory; padding:0 16px 6px; scrollbar-width:none; touch-action:pan-x; cursor:grab; }
.gs--carousel .gs-track:active { cursor:grabbing; }
.gs--carousel .gs-track::-webkit-scrollbar { display:none; }
.gs--carousel .gs-item { flex:0 0 72%; aspect-ratio:4/5; border-radius:14px; scroll-snap-align:center; box-shadow:0 8px 24px -10px rgba(0,0,0,0.35); }

/* ── polaroid: framed, slightly rotated, 2 cols ──────────────── */
.gs--polaroid .gs-track { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; padding:6px 14px; }
.gs--polaroid .gs-item { background:#fff; padding:7px 7px 26px; border-radius:3px; box-shadow:0 6px 18px -8px rgba(0,0,0,0.4); }
.gs--polaroid .gs-item:nth-child(even) { transform:rotate(2deg); }
.gs--polaroid .gs-item:nth-child(odd)  { transform:rotate(-2deg); }
.gs--polaroid .gs-item img { aspect-ratio:1; }

/* ── lightbox ────────────────────────────────────────────────── */
.gs-lb { position:fixed; inset:0; z-index:60; background:#000; display:flex; align-items:center; justify-content:center; }
.gs-lb-img { max-height:100vh; max-width:100%; object-fit:contain; }
.gs-lb-btn { position:absolute; width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,0.1); color:#fff; border:none; display:grid; place-items:center; cursor:pointer; }
.gs-lb-close { top:16px; right:16px; z-index:10; }
.gs-lb-prev { left:8px; top:50%; transform:translateY(-50%); }
.gs-lb-next { right:8px; top:50%; transform:translateY(-50%); }
.gs-lb-count { position:absolute; top:18px; left:50%; transform:translateX(-50%); color:rgba(255,255,255,0.6); font-size:13px; }
.gs-lb-cap { position:absolute; bottom:60px; left:0; right:0; text-align:center; color:rgba(255,255,255,0.8); font-size:13px; padding:0 24px; }
.gs-fade-enter-active, .gs-fade-leave-active { transition:opacity .25s; }
.gs-fade-enter-from, .gs-fade-leave-to { opacity:0; }
</style>
