<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: { type: String, default: 'single' },
        // single | strand-horizontal | strand-vertical | corner-cluster
    count:   { type: Number, default: 0 },
    size:    { type: Number, default: 8 },
    color:   { type: String, default: 'var(--sv-pearl, #F2E9DC)' },
})

const effectiveCount = computed(() => {
    if (props.count > 0) return props.count
    if (props.variant === 'strand-horizontal') return 12
    if (props.variant === 'strand-vertical')   return 10
    if (props.variant === 'corner-cluster')    return 4
    return 1
})

const pearls = computed(() => {
    const n = effectiveCount.value
    return Array.from({ length: n }, (_, i) => ({
        id: i,
        delay: ((i * 0.13) % 2).toFixed(2),
    }))
})

const dim = computed(() => {
    if (props.variant === 'strand-horizontal') {
        return { width: 240, height: props.size * 2 }
    }
    if (props.variant === 'strand-vertical') {
        return { width: props.size * 2, height: 400 }
    }
    if (props.variant === 'corner-cluster') {
        return { width: props.size * 4, height: props.size * 4 }
    }
    return { width: props.size * 1.4, height: props.size * 1.4 }
})

function pearlCx(i) {
    if (props.variant === 'strand-horizontal') {
        const gap = 240 / (effectiveCount.value + 1)
        return gap * (i + 1)
    }
    if (props.variant === 'strand-vertical') {
        return dim.value.width / 2
    }
    if (props.variant === 'corner-cluster') {
        const positions = [
            { x: props.size * 1, y: props.size * 1 },
            { x: props.size * 3, y: props.size * 1 },
            { x: props.size * 1, y: props.size * 3 },
            { x: props.size * 3, y: props.size * 3 },
        ]
        return positions[i]?.x ?? props.size
    }
    return dim.value.width / 2
}

function pearlCy(i) {
    if (props.variant === 'strand-horizontal') return dim.value.height / 2
    if (props.variant === 'strand-vertical') {
        const gap = 400 / (effectiveCount.value + 1)
        return gap * (i + 1)
    }
    if (props.variant === 'corner-cluster') {
        const positions = [
            { x: props.size * 1, y: props.size * 1 },
            { x: props.size * 3, y: props.size * 1 },
            { x: props.size * 1, y: props.size * 3 },
            { x: props.size * 3, y: props.size * 3 },
        ]
        return positions[i]?.y ?? props.size
    }
    return dim.value.height / 2
}

const variantClass = computed(() => `sv-pearl-decor--${props.variant}`)
</script>

<template>
    <svg
        class="sv-pearl-decor"
        :class="variantClass"
        :width="dim.width"
        :height="dim.height"
        :viewBox="`0 0 ${dim.width} ${dim.height}`"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <defs>
            <radialGradient id="sv-pearl-shine" cx="35%" cy="35%" r="65%">
                <stop offset="0%"  stop-color="#FFFFFF" stop-opacity="0.95"/>
                <stop offset="40%" :stop-color="color" stop-opacity="1"/>
                <stop offset="100%" stop-color="#C9C2B3" stop-opacity="0.85"/>
            </radialGradient>
        </defs>
        <!-- Strand connecting line (horizontal / vertical only) -->
        <line
            v-if="variant === 'strand-horizontal'"
            x1="8" :y1="dim.height/2" :x2="dim.width-8" :y2="dim.height/2"
            stroke="#C9C2B3" stroke-width="0.5" stroke-opacity="0.5"
        />
        <line
            v-if="variant === 'strand-vertical'"
            :x1="dim.width/2" y1="8" :x2="dim.width/2" :y2="dim.height-8"
            stroke="#C9C2B3" stroke-width="0.5" stroke-opacity="0.5"
        />
        <g>
            <circle
                v-for="(p, i) in pearls"
                :key="p.id"
                class="sv-pearl"
                :style="{ '--sv-pearl-delay': `${p.delay}s` }"
                :cx="pearlCx(i)"
                :cy="pearlCy(i)"
                :r="size / 2"
                fill="url(#sv-pearl-shine)"
                stroke="#C9C2B3"
                stroke-width="0.3"
                stroke-opacity="0.6"
            />
        </g>
    </svg>
</template>

<style scoped>
.sv-pearl-decor { display: inline-block; }

@keyframes sv-pearl-twinkle {
    0%   { opacity: 0.78; transform: scale(0.95); }
    100% { opacity: 1;    transform: scale(1); }
}
.sv-pearl {
    transform-origin: center center;
    transform-box: fill-box;
    animation: sv-pearl-twinkle 2s ease-in-out infinite alternate;
    animation-delay: var(--sv-pearl-delay, 0s);
    will-change: transform, opacity;
}
@media (prefers-reduced-motion: reduce) {
    .sv-pearl { animation: none; opacity: 1; transform: scale(1); }
}
</style>
