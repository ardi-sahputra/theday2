<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Observer, Horizon } from 'astronomy-engine'
import { STAR_MAP_LAT, STAR_MAP_LNG } from './constants.js'

const props = defineProps({
    dateTime:  { type: String,  default: null }, // ISO 8601 with offset, e.g. '2026-06-15T08:00:00+07:00'
    showLines: { type: Boolean, default: true },
    style:     { type: String,  default: 'classic' },  // classic | modern | minimal
    fallback:  { type: String,  default: null },       // 'generic' forces decorative
})

const SIZE = 480
const CX = SIZE / 2
const CY = SIZE / 2
const R  = SIZE / 2 - 8

const stars = ref([])     // [{x,y,r,name,mag}]
const lines = ref([])     // [{x1,y1,x2,y2,key}]
const ready = ref(false)
const isGeneric = computed(() => props.fallback === 'generic' || !props.dateTime)

function projectAltAz(altDeg, azDeg) {
    if (altDeg <= 0) return null
    // Stereographic-ish: zenith at center, horizon at radius R.
    // r = R * tan((90 - alt) / 2) normalized to map to R at horizon.
    const z = (90 - altDeg) * Math.PI / 180
    const rho = R * Math.tan(z / 2) / Math.tan(Math.PI / 4)
    // Azimuth: 0 = North (top), 90 = East (right). SVG y grows downward.
    const az = azDeg * Math.PI / 180
    const x = CX + rho * Math.sin(az)
    const y = CY - rho * Math.cos(az)
    return { x, y }
}

function magToRadius(mag) {
    // Magnitude scale: -1.5 (brightest) → r 3.5; 6.0 (faintest visible) → r 0.4.
    const clamped = Math.max(-1.5, Math.min(6, mag))
    return 3.5 - ((clamped + 1.5) / 7.5) * 3.1
}
function magToOpacity(mag) {
    const clamped = Math.max(-1.5, Math.min(6, mag))
    return 1 - ((clamped + 1.5) / 7.5) * 0.6
}

async function build() {
    if (isGeneric.value) {
        buildGeneric()
        ready.value = true
        return
    }
    try {
        const [starsRaw, constellations] = await Promise.all([
            fetch('/data/templates/astronomy-celestial/stars-bsc.json').then(r => r.json()),
            fetch('/data/templates/astronomy-celestial/constellations.json').then(r => r.json()),
        ])

        const observer = new Observer(STAR_MAP_LAT, STAR_MAP_LNG, 0)
        const date = new Date(props.dateTime)

        const byId = new Map()
        const visible = []

        for (const s of starsRaw) {
            // astronomy-engine v2 API: Horizon(date, observer, ra, dec, refraction)
            const hz = Horizon(date, observer, s.ra, s.dec, 'normal')
            const pos = projectAltAz(hz.altitude, hz.azimuth)
            if (!pos) {
                byId.set(s.id, null)
                continue
            }
            const star = {
                id: s.id,
                name: s.name,
                mag: s.mag,
                x: pos.x,
                y: pos.y,
                r: magToRadius(s.mag),
                o: magToOpacity(s.mag),
            }
            byId.set(s.id, star)
            visible.push(star)
        }

        const linesOut = []
        if (props.showLines && props.style !== 'modern') {
            for (const c of constellations) {
                for (const [a, b] of c.lines) {
                    const sa = byId.get(a)
                    const sb = byId.get(b)
                    if (!sa || !sb) continue
                    linesOut.push({
                        key: `${c.code}-${a}-${b}`,
                        x1: sa.x, y1: sa.y, x2: sb.x, y2: sb.y,
                    })
                }
            }
        }

        stars.value = visible
        lines.value = linesOut
    } catch (e) {
        console.warn('[CelestialStarMap] compute failed, falling back to generic', e)
        buildGeneric()
    }
    ready.value = true
}

function buildGeneric() {
    // Decorative ring of dots — no real data
    const seedRng = (a) => {
        let s = a
        return () => {
            s = (s * 1664525 + 1013904223) >>> 0
            return s / 0xFFFFFFFF
        }
    }
    const rng = seedRng(20260615)
    const out = []
    for (let i = 0; i < 120; i++) {
        const angle = rng() * Math.PI * 2
        const rad = rng() * (R - 12)
        const mag = -1 + rng() * 6
        out.push({
            id: i, name: '', mag,
            x: CX + Math.cos(angle) * rad,
            y: CY + Math.sin(angle) * rad,
            r: magToRadius(mag),
            o: magToOpacity(mag),
        })
    }
    stars.value = out
    lines.value = []
}

onMounted(build)
watch(() => [props.dateTime, props.showLines, props.style, props.fallback], () => {
    ready.value = false
    build()
})
</script>

<template>
    <div class="ac-star-map" :class="['ac-style-' + style, { 'ac-loading': !ready, 'ac-generic': isGeneric }]">
        <svg :viewBox="`0 0 ${SIZE} ${SIZE}`" class="ac-map-svg" aria-hidden="true">
            <defs>
                <radialGradient id="ac-map-vignette" cx="50%" cy="50%" r="50%">
                    <stop offset="60%" stop-color="#0a1929" stop-opacity="0"/>
                    <stop offset="100%" stop-color="#0a1929" stop-opacity="0.6"/>
                </radialGradient>
                <clipPath id="ac-map-clip">
                    <circle :cx="CX" :cy="CY" :r="R - 2"/>
                </clipPath>
            </defs>

            <circle :cx="CX" :cy="CY" :r="R" class="ac-map-frame"/>
            <circle :cx="CX" :cy="CY" :r="R - 2" class="ac-map-bg"/>

            <g clip-path="url(#ac-map-clip)">
                <line
                    v-for="ln in lines"
                    :key="ln.key"
                    :x1="ln.x1" :y1="ln.y1" :x2="ln.x2" :y2="ln.y2"
                    class="ac-constellation-line"
                />
                <circle
                    v-for="s in stars"
                    :key="s.id"
                    :cx="s.x" :cy="s.y" :r="s.r"
                    :opacity="s.o"
                    class="ac-map-star"
                />
                <rect :x="0" :y="0" :width="SIZE" :height="SIZE" fill="url(#ac-map-vignette)"/>
            </g>

            <circle :cx="CX" :cy="CY" :r="R" class="ac-map-glow-ring"/>
        </svg>
    </div>
</template>

<style scoped>
.ac-star-map {
    position: relative;
    width: 100%;
    max-width: 480px;
    aspect-ratio: 1 / 1;
    margin: 0 auto;
}
.ac-map-svg {
    width: 100%;
    height: 100%;
    display: block;
}
.ac-map-frame {
    fill: none;
    stroke: var(--ac-gold, #d4af37);
    stroke-width: 2;
}
.ac-map-bg {
    fill: #060d18;
}
.ac-map-glow-ring {
    fill: none;
    stroke: rgba(212, 175, 55, 0.35);
    stroke-width: 6;
    filter: blur(6px);
    pointer-events: none;
}
.ac-map-star {
    fill: #ffffff;
}
.ac-constellation-line {
    stroke: var(--ac-gold, #d4af37);
    stroke-width: 0.6;
    stroke-opacity: 0.7;
    fill: none;
    stroke-dasharray: 200;
    stroke-dashoffset: 200;
    animation: ac-line-draw 1.6s ease-out forwards;
    animation-delay: calc(var(--ac-line-stagger, 0) * 0.08s);
}
@keyframes ac-line-draw { to { stroke-dashoffset: 0; } }
.ac-style-minimal .ac-map-star { display: none; }
.ac-style-modern .ac-constellation-line { display: none; }
.ac-style-modern .ac-map-star { fill: var(--ac-ivory, #e8e3d3); }
@media (prefers-reduced-motion: reduce) {
    .ac-constellation-line {
        animation: none;
        stroke-dashoffset: 0;
    }
}
</style>
