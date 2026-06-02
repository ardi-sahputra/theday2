<script setup>
import { computed, ref, onMounted } from 'vue'
import PostalStamp from './PostalStamp.vue'

const props = defineProps({
    cities:  { type: Array, default: () => [] },        // ['JAKARTA','BALI',...]
    stories: { type: Array, default: () => [] },
})

// Hand-curated coordinates in 0-100 percentage of the vintage map canvas.
// Spec §14 VP-2: NEVER call geocoding APIs. Unknown city → cluster zone.
const CITY_COORDS = {
    JAKARTA:    { x: 71, y: 64 },
    BALI:       { x: 75, y: 66 },
    BANDUNG:    { x: 70, y: 64 },
    SURABAYA:   { x: 73, y: 65 },
    YOGYAKARTA: { x: 72, y: 65 },
    TOKYO:      { x: 82, y: 38 },
    KYOTO:      { x: 81, y: 39 },
    OSAKA:      { x: 81, y: 40 },
    SEOUL:      { x: 80, y: 36 },
    SINGAPORE:  { x: 70, y: 60 },
    BANGKOK:    { x: 68, y: 54 },
    PARIS:      { x: 47, y: 30 },
    LONDON:     { x: 45, y: 26 },
    ROME:       { x: 49, y: 34 },
    BARCELONA:  { x: 46, y: 34 },
    'NEW YORK': { x: 25, y: 34 },
    NEWYORK:    { x: 25, y: 34 },
    'LOS ANGELES': { x: 14, y: 38 },
    SYDNEY:     { x: 86, y: 78 },
    DUBAI:      { x: 58, y: 46 },
}

function lookup(city, fallbackIdx) {
    const key = (city ?? '').toString().toUpperCase().trim()
    if (CITY_COORDS[key]) return CITY_COORDS[key]
    // Cluster zone: stack unknown cities along the bottom-right
    return { x: 88, y: 80 + (fallbackIdx % 3) * 3 }
}

const points = computed(() => props.cities.map((c, i) => ({
    city: c,
    ...lookup(c, i),
})))

const polylineD = computed(() => {
    if (!points.value.length) return ''
    return points.value
        .map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`)
        .join(' ')
})

const root = ref(null)
const drawn = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        drawn.value = true
        return
    }
    if (!('IntersectionObserver' in window)) { drawn.value = true; return }
    const io = new IntersectionObserver(es => {
        es.forEach(e => { if (e.isIntersecting) { drawn.value = true; io.unobserve(e.target) } })
    }, { threshold: 0.3 })
    if (root.value) io.observe(root.value)
})
</script>

<template>
    <div class="vp-route" ref="root">
        <div class="vp-route-map" aria-hidden="true">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="vp-route-svg">
                <path
                    :d="polylineD"
                    fill="none"
                    stroke="#2c4a3e"
                    stroke-width="0.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-dasharray="3 2"
                    class="vp-route-line"
                    :class="{ 'vp-route-line--drawn': drawn }"
                />
            </svg>
            <span
                v-for="(p, i) in points"
                :key="p.city + i"
                class="vp-route-pin"
                :style="{ left: `${p.x}%`, top: `${p.y}%` }"
            >
                <PostalStamp size="tiny" :city="p.city" :rotate="-4 + (i % 3) * 4"/>
                <span class="vp-route-pin-label">{{ p.city }}</span>
            </span>
        </div>
    </div>
</template>

<style scoped>
.vp-route {
    position: relative;
    width: 100%;
    aspect-ratio: 3/2;
    background:
        url('/images/templates/vintage-postal/vintage-map.svg') center/cover no-repeat,
        #d8c8a0;
    border: 1px solid rgba(92, 74, 58, 0.4);
    overflow: hidden;
    margin-bottom: 16px;
}
.vp-route-map { position: absolute; inset: 0; }
.vp-route-svg { width: 100%; height: 100%; display: block; }
.vp-route-line {
    stroke-dasharray: 200;
    stroke-dashoffset: 200;
    transition: stroke-dashoffset 2s ease-in-out;
}
.vp-route-line--drawn { stroke-dashoffset: 0; }
.vp-route-pin {
    position: absolute;
    transform: translate(-50%, -50%);
    display: flex; flex-direction: column; align-items: center;
}
.vp-route-pin-label {
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 10px;
    color: #3a2d1f;
    letter-spacing: 1.5px;
    background: rgba(232, 220, 196, 0.85);
    padding: 1px 4px;
    margin-top: 2px;
    white-space: nowrap;
}
@media (prefers-reduced-motion: reduce) {
    .vp-route-line { transition: none; stroke-dasharray: 0; stroke-dashoffset: 0; }
}
</style>
