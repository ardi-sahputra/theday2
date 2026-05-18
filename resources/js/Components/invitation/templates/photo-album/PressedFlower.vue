<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant:  { type: String, default: 'rose' },          // rose|leaf|petal|full-bouquet
    position: { type: String, default: 'bottom-right' },  // top-left|top-right|bottom-left|bottom-right
    size:     { type: Number, default: null },
    rotate:   { type: Number, default: null },            // default randomized via seed if null
    seed:     { type: Number, default: 0 },
})

const DEFAULT_SIZE = { rose: 140, leaf: 120, petal: 80, 'full-bouquet': 240 }

const flowerUrl = computed(() => `/images/templates/photo-album/pressed-flower-${props.variant}.svg`)

const finalSize = computed(() => props.size ?? DEFAULT_SIZE[props.variant] ?? 140)

// Stable pseudo-random rotation based on seed (-8..+8)
const finalRotate = computed(() => {
    if (props.rotate !== null) return props.rotate
    const hash = (props.seed * 2654435761) & 0xffff
    return ((hash % 17) - 8) // -8..+8 inclusive
})

const flowerStyle = computed(() => {
    const base = {
        width:  `${finalSize.value}px`,
        height: 'auto',
        '--pa-flower-rotate': `${finalRotate.value}deg`,
    }
    switch (props.position) {
        case 'top-left':     return { ...base, top: '-20px',    left: '-20px' }
        case 'top-right':    return { ...base, top: '-20px',    right: '-20px' }
        case 'bottom-left':  return { ...base, bottom: '-20px', left: '-20px' }
        case 'bottom-right': return { ...base, bottom: '-20px', right: '-20px' }
        default:             return base
    }
})
</script>

<template>
    <img
        :src="flowerUrl"
        :alt="`Pressed flower ${variant}`"
        class="pa-pressed-flower pa-reveal"
        :style="flowerStyle"
        aria-hidden="true"
        draggable="false"
    />
</template>

<style scoped>
.pa-pressed-flower {
    position: absolute;
    pointer-events: none;
    z-index: 30;
    transform: translateY(8px) rotate(var(--pa-flower-rotate, 0deg));
    opacity: 0;
    transition: transform 0.8s ease-out, opacity 0.8s ease-out;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.45));
}
.pa-pressed-flower.pa-visible {
    transform: translateY(0) rotate(var(--pa-flower-rotate, 0deg));
    opacity: 1;
}
@media (prefers-reduced-motion: reduce) {
    .pa-pressed-flower,
    .pa-pressed-flower.pa-visible {
        opacity: 1;
        transform: rotate(var(--pa-flower-rotate, 0deg)) !important;
        transition: opacity 0.2s ease !important;
    }
}
</style>
