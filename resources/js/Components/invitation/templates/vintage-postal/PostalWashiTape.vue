<script setup>
import { computed } from 'vue'

const props = defineProps({
    pattern:  { type: String, default: 'striped' },   // 'striped' | 'polka-dot' | 'floral'
    position: { type: String, default: 'top' },       // 'top' | 'bottom' | 'free'
    length:   { type: Number, default: 240 },
    rotate:   { type: Number, default: -2 },
})

const VALID = ['striped','polka-dot','floral']
const patternUrl = computed(() => {
    const slug = VALID.includes(props.pattern) ? props.pattern.replace('-dot','') : 'striped'
    const map = {
        'striped': '/images/templates/vintage-postal/washi-tape-striped.svg',
        'polka':   '/images/templates/vintage-postal/washi-tape-polka.svg',
        'floral':  '/images/templates/vintage-postal/washi-tape-floral.svg',
    }
    return map[slug] ?? map.striped
})

const wrapStyle = computed(() => ({
    width: `${props.length}px`,
    transform: `rotate(${props.rotate}deg)`,
}))
</script>

<template>
    <span class="vp-washi" :class="`vp-washi--${position}`" :style="wrapStyle" aria-hidden="true">
        <img :src="patternUrl" :alt="''" draggable="false"/>
    </span>
</template>

<style scoped>
.vp-washi {
    display: inline-block;
    height: 28px;
    opacity: 0.85;
    clip-path: inset(0 100% 0 0);
    animation: vp-washi-unfold 0.4s ease-out 0.15s forwards;
    will-change: clip-path;
}
.vp-washi img {
    width: 100%; height: 100%; object-fit: cover;
    pointer-events: none;
    user-select: none;
}
.vp-washi--top    { /* positioned by parent */ }
.vp-washi--bottom { /* positioned by parent */ }
.vp-washi--free   { /* positioned by parent */ }
@keyframes vp-washi-unfold {
    0%   { clip-path: inset(0 100% 0 0); }
    100% { clip-path: inset(0 0 0 0); }
}
@media (prefers-reduced-motion: reduce) {
    .vp-washi { animation: none; clip-path: inset(0 0 0 0); }
}
</style>
