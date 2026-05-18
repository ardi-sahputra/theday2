<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    currentScene:   { type: String, default: 'opening' },
    isSectionEnabled: { type: Function, default: () => true },
    ringRadius:     { type: Number, default: 200 },
})

const emit = defineEmits(['select-scene'])

// 12 sections — order matches catalog
const SECTIONS = [
    'opening', 'couple', 'events', 'countdown',
    'love_story', 'gallery', 'rsvp', 'gift',
    'wishes', 'quote', 'music', 'closing',
]

// Distribute across 360° but skip the bottom 60° arc (300°-360°) where base + caption sit.
// Available arc: 300° (from -150° to +150° going clockwise via top).
const items = computed(() => {
    const n = SECTIONS.length
    const startDeg = -150            // top-left-ish
    const arc      = 300
    const step     = arc / (n - 1)
    return SECTIONS.map((key, i) => ({
        key,
        deg:      startDeg + i * step,
        enabled:  props.isSectionEnabled(key),
        active:   props.currentScene === key,
        label:    LABELS[key] || key,
    }))
})

const LABELS = {
    opening:    'Pembuka',
    couple:     'Mempelai',
    events:     'Acara',
    countdown:  'Hitung Mundur',
    love_story: 'Kisah Cinta',
    gallery:    'Galeri',
    rsvp:       'Konfirmasi Kehadiran',
    gift:       'Hadiah',
    wishes:     'Ucapan',
    quote:      'Kutipan',
    music:      'Musik',
    closing:    'Penutup',
}

// Ripple state — map of key → ripple id
const ripples = ref({})
let rippleSeq = 0
function clickIcon(item) {
    if (!item.enabled) return
    const id = ++rippleSeq
    ripples.value[item.key] = id
    setTimeout(() => {
        if (ripples.value[item.key] === id) delete ripples.value[item.key]
    }, 600)
    emit('select-scene', item.key)
}

// Inline icon SVG paths per key (stroked outline, 24×24, lucide-inspired but custom)
const ICONS = {
    opening:    'M4 22V8a8 8 0 0 1 16 0v14M4 14h16',
    couple:     'M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm8 0a3 3 0 1 1 0-6 3 3 0 0 1 0 6ZM4 22v-3a4 4 0 0 1 4-4M16 22v-3a4 4 0 0 1 4-4M12 13l-1.5 2L12 17l1.5-2L12 13Z',
    events:     'M5 4h14v17H5zM5 9h14M9 2v4M15 2v4',
    countdown:  'M8 3h8M8 21h8M9 3v3a3 3 0 0 0 6 0V3M9 21v-3a3 3 0 0 1 6 0v3',
    love_story: 'M3 19c4-6 9-2 11-6 1-3 4-4 7-2',
    gallery:    'M4 5h16v14H4zM4 16l5-5 4 4 3-3 4 4M16 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z',
    rsvp:       'M3 6h18v12H3zM3 6l9 7 9-7',
    gift:       'M3 10h18v11H3zM2 6h20v4H2zM12 6v15M8 6a2 2 0 1 1 4-2 2 2 0 1 1 4 2',
    wishes:     'M5 4h11l3 3v13H5zM9 8h8M9 12h8M9 16h5',
    quote:      'M4 5h16v14H4zM12 5v14M8 9h0M8 13h0M16 9h0M16 13h0',
    music:      'M9 18V5l12-2v13M9 18a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm12-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    closing:    'M4 20V12a8 8 0 0 1 16 0v8M9 20v-4a3 3 0 0 1 6 0v4',
}
</script>

<template>
    <div class="sg-ring" :style="{ '--ring-radius': ringRadius + 'px' }" role="tablist" aria-label="Pilih bagian undangan">
        <button
            v-for="item in items"
            :key="item.key"
            type="button"
            class="sg-ring-icon"
            :class="{ 'sg-ring-icon--active': item.active, 'sg-ring-icon--disabled': !item.enabled }"
            :style="{ transform: `rotate(${item.deg}deg) translateX(var(--ring-radius)) rotate(${-item.deg}deg)` }"
            :aria-label="`Lihat bagian ${item.label}`"
            :aria-pressed="item.active"
            :tabindex="item.enabled ? 0 : -1"
            :disabled="!item.enabled"
            @click="clickIcon(item)"
        >
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none"
                 stroke="currentColor" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path :d="ICONS[item.key]"/>
            </svg>
            <span v-if="ripples[item.key]" :key="ripples[item.key]" class="sg-ring-ripple" aria-hidden="true"/>
        </button>
    </div>
</template>

<style scoped>
.sg-ring {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.sg-ring-icon {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 44px;
    height: 44px;
    margin: -22px 0 0 -22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(5, 8, 19, 0.55);
    border: 1px solid rgba(201, 169, 97, 0.45);
    border-radius: 999px;
    color: var(--sg-gold-dim, #8C7338);
    cursor: pointer;
    pointer-events: auto;
    transition: transform 0.3s ease-out, filter 0.3s ease-out, color 0.3s ease-out, border-color 0.3s ease-out;
    overflow: visible;
}
.sg-ring-icon:hover,
.sg-ring-icon:focus-visible {
    color: var(--sg-snow, #FAFAF5);
    border-color: var(--sg-gold, #C9A961);
    filter: drop-shadow(0 0 8px var(--sg-gold, #C9A961));
    outline: none;
}
.sg-ring-icon--active {
    color: var(--sg-snow, #FAFAF5);
    border-color: var(--sg-gold, #C9A961);
    filter: drop-shadow(0 0 12px var(--sg-gold, #C9A961));
}
.sg-ring-icon--disabled {
    opacity: 0.25;
    cursor: not-allowed;
    pointer-events: none;
}
.sg-ring-ripple {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: radial-gradient(circle, var(--sg-gold, #C9A961) 0%, transparent 70%);
    transform: scale(0);
    opacity: 0.6;
    pointer-events: none;
    animation: sg-ripple 0.6s ease-out forwards;
}
@keyframes sg-ripple {
    0%   { transform: scale(0); opacity: 0.6; }
    100% { transform: scale(4); opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .sg-ring-icon { transition: filter 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
    .sg-ring-icon:hover,
    .sg-ring-icon:focus-visible,
    .sg-ring-icon--active {
        transform: none;
    }
    .sg-ring-ripple { animation: none; display: none; }
}
</style>
