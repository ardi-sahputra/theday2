<script setup>
import { computed } from 'vue'

const props = defineProps({
    rays:     { type: Number, default: 24 },
    size:     { type: Number, default: 200 },
    radius:   { type: Number, default: 280 },
    animated: { type: Boolean, default: true },
})

const raysArray = computed(() => Array.from({ length: props.rays }))
function angle(i) { return (360 / props.rays) * i }
function rayX(i) { return Math.cos((angle(i) - 90) * Math.PI / 180) * props.radius }
function rayY(i) { return Math.sin((angle(i) - 90) * Math.PI / 180) * props.radius }
</script>

<template>
    <svg :viewBox="`0 0 ${size * 3} ${size * 3}`" class="deco-sunburst" aria-hidden="true">
        <g :transform="`translate(${size * 1.5}, ${size * 1.5})`">
            <line
                v-for="(_, i) in raysArray"
                :key="i"
                :x1="0" :y1="0"
                :x2="rayX(i)" :y2="rayY(i)"
                stroke="currentColor"
                stroke-width="1.5"
                :class="animated ? 'deco-sunburst-ray' : ''"
                :style="{ '--ray-index': i }"
            />
            <circle r="5" fill="currentColor"/>
        </g>
    </svg>
</template>

<style scoped>
.deco-sunburst { display: block; width: 100%; height: 100%; }
.deco-sunburst-ray {
    stroke-dasharray: 280;
    stroke-dashoffset: 280;
    animation: deco-ray-draw 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    animation-delay: calc(var(--ray-index) * 0.05s);
}
@keyframes deco-ray-draw {
    to { stroke-dashoffset: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .deco-sunburst-ray { animation: none !important; stroke-dashoffset: 0 !important; }
}
</style>
