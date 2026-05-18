<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
    years:          { type: Array,  required: true },
    milestoneYears: { type: Array,  default: () => [] },
    currentYear:    { type: Number, required: true },
    show:           { type: Boolean, default: true },
})

const W = 1000  // viewBox width
const H = 120   // viewBox height
const drawn = ref(false)

const points = computed(() => {
    if (!props.years.length) return []
    const last = Math.max(1, props.years.length - 1)
    return props.years.map((yr, i) => {
        const progress = i / last
        const baseY = H - (Math.pow(progress, 1.5) * H * 0.7)
        const isMs = props.milestoneYears.includes(yr)
        const bump = isMs ? -H * 0.12 : 0
        const y = Math.max(8, Math.min(H - 8, baseY + bump))
        const x = progress * W
        return [x, y]
    })
})

function cardinalSplineToBezier(pts, tension = 0.4) {
    if (pts.length < 2) return ''
    const s = (1 - tension) / 2
    const p = [pts[0], ...pts, pts[pts.length - 1]]
    let d = `M ${pts[0][0]} ${pts[0][1]}`
    for (let i = 1; i < p.length - 2; i++) {
        const p0 = p[i - 1], p1 = p[i], p2 = p[i + 1], p3 = p[i + 2]
        const c1x = p1[0] + s * (p2[0] - p0[0])
        const c1y = p1[1] + s * (p2[1] - p0[1])
        const c2x = p2[0] - s * (p3[0] - p1[0])
        const c2y = p2[1] - s * (p3[1] - p1[1])
        d += ` C ${c1x.toFixed(2)} ${c1y.toFixed(2)}, ${c2x.toFixed(2)} ${c2y.toFixed(2)}, ${p2[0].toFixed(2)} ${p2[1].toFixed(2)}`
    }
    return d
}

const pathD = computed(() => cardinalSplineToBezier(points.value, 0.4))

const areaD = computed(() => {
    if (!points.value.length) return ''
    const last = points.value[points.value.length - 1]
    return `${pathD.value} L ${last[0]} ${H} L 0 ${H} Z`
})

const currentDotPos = computed(() => {
    if (!props.years.length) return { x: 0, y: H }
    const first = props.years[0]
    const lastY = props.years[props.years.length - 1]
    const span  = Math.max(1, lastY - first)
    const t     = Math.min(1, Math.max(0, (props.currentYear - first) / span))
    const idxF  = t * (props.years.length - 1)
    const i0    = Math.floor(idxF)
    const i1    = Math.min(props.years.length - 1, i0 + 1)
    const frac  = idxF - i0
    const p0 = points.value[i0] ?? [0, H]
    const p1 = points.value[i1] ?? p0
    return { x: p0[0] + (p1[0] - p0[0]) * frac, y: p0[1] + (p1[1] - p0[1]) * frac }
})

onMounted(() => {
    if (typeof window === 'undefined') return
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reduced) { drawn.value = true; return }
    requestAnimationFrame(() => { drawn.value = true })
})
</script>

<template>
    <div v-if="show" class="ys-graph" aria-hidden="true">
        <svg :viewBox="`0 0 ${W} ${H}`" preserveAspectRatio="none" class="ys-graph-svg">
            <defs>
                <linearGradient id="ys-graph-stroke" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%"  stop-color="#7A9B8E"/>
                    <stop offset="60%" stop-color="#C9A961"/>
                    <stop offset="100%" stop-color="#922B3E"/>
                </linearGradient>
                <linearGradient id="ys-graph-fill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="#E8B4B8" stop-opacity="0.35"/>
                    <stop offset="100%" stop-color="#E8B4B8" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <path :d="areaD" fill="url(#ys-graph-fill)"/>
            <path
                :d="pathD"
                fill="none"
                stroke="url(#ys-graph-stroke)"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="ys-graph-path"
                :class="{ 'is-drawn': drawn }"
            />
            <circle
                v-for="(p, i) in points"
                :key="i"
                :cx="p[0]"
                :cy="p[1]"
                r="3"
                fill="#C9A961"
                opacity="0.55"
            />
            <circle
                :cx="currentDotPos.x"
                :cy="currentDotPos.y"
                r="5"
                fill="#922B3E"
                stroke="#FAF8F2"
                stroke-width="1.5"
                class="ys-graph-cursor"
            />
        </svg>
    </div>
</template>

<style scoped>
.ys-graph {
    width: 100%;
    height: 120px;
    padding: 0 16px;
}
.ys-graph-svg { width: 100%; height: 100%; overflow: visible; }
.ys-graph-path {
    stroke-dasharray: 1800;
    stroke-dashoffset: 1800;
    transition: stroke-dashoffset 2.5s ease-out;
}
.ys-graph-path.is-drawn { stroke-dashoffset: 0; }
.ys-graph-cursor { transition: cx 0.4s ease, cy 0.4s ease; }
@media (prefers-reduced-motion: reduce) {
    .ys-graph-path { transition: none; stroke-dashoffset: 0; }
    .ys-graph-cursor { transition: none; }
}
</style>
