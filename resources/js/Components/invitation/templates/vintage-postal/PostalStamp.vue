<script setup>
import { computed } from 'vue'

const props = defineProps({
    city:         { type: String, default: null },
    theme:        { type: String, default: null },   // 'love' | 'wedding' | 'forever'
    date:         { type: String, default: null },
    denomination: { type: String, default: null },
    rotate:       { type: Number, default: -3 },
    size:         { type: String, default: 'normal' },   // 'tiny' | 'small' | 'normal'
})

const CITY_ASSETS = ['paris','jakarta','tokyo','bali','rome']
const THEME_ASSETS = ['love','wedding','forever']

const assetUrl = computed(() => {
    const base = '/images/templates/vintage-postal'
    if (props.city) {
        const slug = props.city.toLowerCase().trim()
        if (CITY_ASSETS.includes(slug)) return `${base}/stamp-${slug}.png`
    }
    if (props.theme && THEME_ASSETS.includes(props.theme)) {
        return `${base}/stamp-${props.theme}.png`
    }
    return `${base}/stamp-wedding.png`
})

const altText = computed(() => {
    if (props.city)  return `Prangko ${props.city}`
    if (props.theme) return `Prangko bertema ${props.theme}`
    return 'Prangko vintage'
})

const sizeClass = computed(() => `vp-stamp--${props.size}`)
const wrapStyle = computed(() => ({ '--rot-final': `${props.rotate}deg`, '--rot-start': `${props.rotate + 8}deg` }))
</script>

<template>
    <span class="vp-stamp" :class="sizeClass" :style="wrapStyle">
        <img :src="assetUrl" :alt="altText" draggable="false"/>
        <span v-if="date || city" class="vp-stamp-caption">
            <span v-if="city" class="vp-stamp-city">{{ city }}</span>
            <span v-if="date" class="vp-stamp-date">{{ date }}</span>
            <span v-if="denomination" class="vp-stamp-denom">{{ denomination }}</span>
        </span>
    </span>
</template>

<style scoped>
.vp-stamp {
    display: inline-block;
    width: 96px; height: 112px;
    position: relative;
    filter: drop-shadow(0 2px 4px rgba(58, 45, 31, 0.35));
    transform: rotate(var(--rot-final, -3deg));
    opacity: 0;
    animation: vp-stamp-stick 0.6s ease-out 0.1s forwards;
    will-change: transform, opacity;
}
.vp-stamp img {
    width: 100%; height: 100%; object-fit: contain;
    pointer-events: none;
    user-select: none;
}
.vp-stamp--tiny   { width: 56px;  height: 66px; }
.vp-stamp--small  { width: 72px;  height: 84px; }
.vp-stamp--normal { width: 96px;  height: 112px; }
.vp-stamp-caption {
    position: absolute;
    bottom: 4px; left: 0; right: 0;
    text-align: center;
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 9px;
    color: #3a2d1f;
    letter-spacing: 1px;
    text-shadow: 0 1px 0 rgba(232, 220, 196, 0.6);
}
.vp-stamp-city { display: block; font-weight: 700; text-transform: uppercase; }
.vp-stamp-date { display: block; font-size: 8px; }
@keyframes vp-stamp-stick {
    0%   { transform: translateY(-24px) rotate(var(--rot-start, 5deg)); opacity: 0; }
    100% { transform: translateY(0)     rotate(var(--rot-final, -3deg)); opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
    .vp-stamp { animation: none; opacity: 1; transform: rotate(var(--rot-final, -3deg)); }
}
</style>
