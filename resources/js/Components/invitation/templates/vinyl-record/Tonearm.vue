<script setup>
import { computed } from 'vue'

const props = defineProps({
    trackIndex: { type: Number, default: -1 }, // -1 rest, 0..5 active within side
    side:       { type: String, default: 'A' },
})

// -1 (rest)   -> +8deg  (lifted, off record on right)
// 0  (outer)  -> -22deg
// 5  (inner)  -> -12deg
// linear interpolation index 0..5
const angle = computed(() => {
    if (props.trackIndex < 0) return 8
    const i = Math.max(0, Math.min(5, props.trackIndex))
    return -22 + i * 2
})

const styleTransform = computed(() => ({ transform: `rotate(${angle.value}deg)` }))
</script>

<template>
    <div class="vr-tonearm-host" aria-hidden="true">
        <svg viewBox="0 0 200 200" class="vr-tonearm" :style="styleTransform">
            <!-- pivot mount cylinder -->
            <circle cx="170" cy="30" r="12" fill="#B8902F"/>
            <circle cx="170" cy="30" r="9"  fill="#8e6f24"/>
            <circle cx="170" cy="30" r="4"  fill="#D4AA42"/>
            <!-- counter weight -->
            <rect x="178" y="22" width="14" height="16" rx="2" fill="#5C3A21"/>
            <!-- tube (rotates around pivot 170,30) -->
            <rect x="38" y="28"  width="132" height="4"  rx="2"
                  fill="#B8902F"/>
            <rect x="38" y="28"  width="132" height="1"  fill="#D4AA42"/>
            <!-- bend / S-arm hint -->
            <rect x="36" y="32"  width="6"   height="6"  rx="1" fill="#8e6f24"/>
            <!-- cartridge head -->
            <rect x="20" y="34"  width="22"  height="12" rx="1" fill="#5C3A21"/>
            <rect x="22" y="36"  width="18"  height="2"  fill="#B8902F"/>
            <!-- stylus needle -->
            <rect x="29" y="46"  width="1.5" height="6"  fill="#D4AA42"/>
        </svg>
    </div>
</template>

<style scoped>
.vr-tonearm-host {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    z-index: 3;
}
.vr-tonearm {
    width: 100%;
    height: 100%;
    transform-origin: 170px 30px; /* pivot mount in SVG coords */
    transition: transform 1.2s cubic-bezier(0.65, 0, 0.35, 1);
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));
    will-change: transform;
}
@media (prefers-reduced-motion: reduce) {
    .vr-tonearm { transition: none; }
}
</style>
