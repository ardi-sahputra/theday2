<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    density:        { type: String,  default: 'medium' },   // low|medium|high
    parallaxDepth:  { type: String,  default: 'medium' },   // subtle|medium|strong
    twinkleEnabled: { type: Boolean, default: true },
    seed:           { type: [String, Number], default: 0 },
})

// Hashed seed → mulberry32 PRNG for deterministic placement
function hashSeed(s) {
    const str = String(s)
    let h = 2166136261
    for (let i = 0; i < str.length; i++) {
        h ^= str.charCodeAt(i)
        h = Math.imul(h, 16777619)
    }
    return h >>> 0
}
function mulberry32(a) {
    return function () {
        a |= 0; a = a + 0x6D2B79F5 | 0
        let t = a
        t = Math.imul(t ^ t >>> 15, t | 1)
        t ^= t + Math.imul(t ^ t >>> 7, t | 61)
        return ((t ^ t >>> 14) >>> 0) / 4294967296
    }
}

const counts = { low: 80, medium: 150, high: 240 }
const parallaxMul = { subtle: 0.15, medium: 0.30, strong: 0.55 }

const stars = computed(() => {
    const rng = mulberry32(hashSeed(props.seed))
    const n = counts[props.density] ?? 150
    const arr = []
    for (let i = 0; i < n; i++) {
        const depth = 1 + Math.floor(rng() * 3)   // 1|2|3
        arr.push({
            id: i,
            x: rng() * 100,                       // %
            y: rng() * 100,                       // %
            r: depth === 1 ? 0.7 : depth === 2 ? 1.1 : 1.6,
            o: depth === 1 ? 0.35 : depth === 2 ? 0.6 : 0.9,
            depth,
            twinkle: props.twinkleEnabled && rng() < 0.2,
            dur: 1.5 + rng() * 1.5,
            delay: rng() * 2,
        })
    }
    return arr
})

const depthMul = computed(() => parallaxMul[props.parallaxDepth] ?? 0.30)

let onScroll = null
let rafToken = null

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    let ticking = false
    onScroll = () => {
        if (ticking) return
        ticking = true
        rafToken = requestAnimationFrame(() => {
            const y = window.scrollY
            const root = document.documentElement
            root.style.setProperty('--ac-depth-1', `${-(y * 0.2 * depthMul.value)}px`)
            root.style.setProperty('--ac-depth-2', `${-(y * 0.5 * depthMul.value)}px`)
            root.style.setProperty('--ac-depth-3', `${-(y * 0.8 * depthMul.value)}px`)
            ticking = false
        })
    }
    window.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => {
    if (onScroll) window.removeEventListener('scroll', onScroll)
    if (rafToken) cancelAnimationFrame(rafToken)
})
</script>

<template>
    <div class="ac-field" aria-hidden="true">
        <div
            v-for="d in [1, 2, 3]"
            :key="d"
            class="ac-field-layer"
            :data-depth="d"
            :style="{ transform: `translate3d(0, var(--ac-depth-${d}, 0px), 0)` }"
        >
            <span
                v-for="s in stars.filter(x => x.depth === d)"
                :key="s.id"
                class="ac-star"
                :class="{ 'ac-twinkle': s.twinkle }"
                :style="{
                    left:    s.x + '%',
                    top:     s.y + '%',
                    width:   (s.r * 2) + 'px',
                    height:  (s.r * 2) + 'px',
                    opacity: s.o,
                    '--ac-twk-dur':   s.dur + 's',
                    '--ac-twk-delay': s.delay + 's',
                }"
            />
        </div>
    </div>
</template>

<style scoped>
.ac-field {
    position: absolute; inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.ac-field-layer {
    position: absolute; inset: 0;
    will-change: transform;
}
.ac-star {
    position: absolute;
    background: #ffffff;
    border-radius: 50%;
    box-shadow: 0 0 4px rgba(255,255,255,0.7);
}
.ac-twinkle {
    animation: ac-twinkle var(--ac-twk-dur, 2s) ease-in-out infinite alternate;
    animation-delay: var(--ac-twk-delay, 0s);
}
@keyframes ac-twinkle {
    0%   { opacity: 0.35; transform: scale(0.9); }
    100% { opacity: 1;    transform: scale(1.15); }
}
@media (prefers-reduced-motion: reduce) {
    .ac-twinkle { animation: none; opacity: 1; }
    .ac-field-layer { transform: none !important; }
}
</style>
