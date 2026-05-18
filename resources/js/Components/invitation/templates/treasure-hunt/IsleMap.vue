<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <div class="th-isle" :class="{ 'th-isle--dragging': pan.dragging }">
        <div ref="canvas" class="th-isle__canvas" :class="{ 'is-dragging': pan.dragging }"
             :style="canvasStyle"
             @pointerdown="onPointerDown" @pointermove="onPointerMove"
             @pointerup="onPointerUp" @pointercancel="onPointerUp"
             @wheel.prevent="onWheel">
            <img src="/images/templates/treasure-hunt/parchment-base.webp" class="th-isle__parchment"
                 alt="" aria-hidden="true" draggable="false"/>
            <img src="/images/templates/treasure-hunt/isle-of-matrimony.svg" class="th-isle__map"
                 alt="" aria-hidden="true" draggable="false"/>
            <div class="th-isle__cartouche">
                <img src="/images/templates/treasure-hunt/cartouche.svg" alt="" aria-hidden="true"/>
                <span class="th-isle__cartouche-text">{{ islandName }}</span>
            </div>
            <RouteLine v-if="routeRevealed" :pois="pois" :revealed="routeRevealed"/>
            <SeaMonster v-for="m in monsterInstances" :key="m.variant"
                        :variant="m.variant" :x="m.x" :y="m.y" :width="m.width"/>
            <PoiMarker v-for="(poi, i) in pois" :key="poi.key"
                       :roman="poi.roman" :name="poi.name" :x="poi.x" :y="poi.y"
                       :visited="visited.has(poi.key)" :zoom="zoom" :variant="i % 4"
                       @tap="$emit('poi-tap', poi)"/>
            <div class="th-isle__watermark"><slot name="watermark"/></div>
        </div>
        <CompassRose :style="compassStyle"/>
        <Transition name="th-hint">
            <div v-if="hintVisible" class="th-tutorial-hint">
                Geser untuk menjelajah &nbsp;&middot;&nbsp; Tap X untuk membuka
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import RouteLine   from './RouteLine.vue'
import SeaMonster  from './SeaMonster.vue'
import PoiMarker   from './PoiMarker.vue'
import CompassRose from './CompassRose.vue'

const props = defineProps({
    islandName:    { type: String,  default: 'Isle of Matrimony' },
    pois:          { type: Array,   default: () => [] },
    visited:       { type: Object,  default: () => new Set() },
    routeRevealed: { type: Boolean, default: true },
    seaMonsters:   { type: Array,   default: () => [] },
    compassStyle:  { type: String,  default: 'classic' },
    zoomDefault:   { type: Number,  default: 1 },
})
defineEmits(['poi-tap'])

const canvas = ref(null)
const pan = reactive({ x: 0, y: 0, dragging: false, lastX: 0, lastY: 0 })
const zoom = ref(Math.min(2, Math.max(0.5, Number(props.zoomDefault) || 1)))
const pointers = new Map()
let pinchStartDist = 0, pinchStartZoom = 1
const hintVisible = ref(true)
let hintTimer = null

const canvasStyle = computed(() => ({
    '--th-pan-x': `${pan.x}px`, '--th-pan-y': `${pan.y}px`, '--th-zoom': zoom.value,
}))

const MONSTER_POSITIONS = {
    kraken:  { x: 4,  y: 8,  width: 200 },
    mermaid: { x: 88, y: 78, width: 160 },
    serpent: { x: 6,  y: 70, width: 220 },
    whale:   { x: 84, y: 12, width: 180 },
}
const monsterInstances = computed(() =>
    (props.seaMonsters || []).filter(v => MONSTER_POSITIONS[v])
        .map(v => ({ variant: v, ...MONSTER_POSITIONS[v] }))
)

function clampPan() {
    if (typeof window === 'undefined') return
    const vw = window.innerWidth, vh = window.innerHeight
    const slackX = vw * 0.25, slackY = vh * 0.25
    const maxX = (vw * (zoom.value - 1)) / 2 + slackX
    const maxY = (vh * (zoom.value - 1)) / 2 + slackY
    pan.x = Math.max(-maxX, Math.min(maxX, pan.x))
    pan.y = Math.max(-maxY, Math.min(maxY, pan.y))
}

function dismissHint() {
    if (!hintVisible.value) return
    hintVisible.value = false
    if (hintTimer) { clearTimeout(hintTimer); hintTimer = null }
}

function onPointerDown(e) {
    if (e.target.closest?.('.th-poi')) return
    pointers.set(e.pointerId, { x: e.clientX, y: e.clientY })
    if (pointers.size === 1) {
        pan.dragging = true; pan.lastX = e.clientX; pan.lastY = e.clientY
        canvas.value.setPointerCapture(e.pointerId)
    } else if (pointers.size === 2) {
        const pts = Array.from(pointers.values())
        pinchStartDist = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y)
        pinchStartZoom = zoom.value
        pan.dragging = false
    }
}

function onPointerMove(e) {
    if (!pointers.has(e.pointerId)) return
    pointers.set(e.pointerId, { x: e.clientX, y: e.clientY })
    if (pointers.size === 2) {
        const pts = Array.from(pointers.values())
        const d = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y)
        if (pinchStartDist > 0) {
            zoom.value = Math.min(2, Math.max(0.5, pinchStartZoom * (d / pinchStartDist)))
            clampPan()
        }
        return
    }
    if (!pan.dragging) return
    pan.x += e.clientX - pan.lastX
    pan.y += e.clientY - pan.lastY
    pan.lastX = e.clientX; pan.lastY = e.clientY
    clampPan(); dismissHint()
}

function onPointerUp(e) {
    pointers.delete(e.pointerId)
    if (pointers.size < 2) pinchStartDist = 0
    if (pointers.size === 0) {
        pan.dragging = false
        try { canvas.value.releasePointerCapture(e.pointerId) } catch {}
    }
}

function onWheel(e) {
    zoom.value = Math.min(2, Math.max(0.5, zoom.value + (-e.deltaY * 0.001)))
    clampPan()
}

watch(() => [pan.x, pan.y], ([x, y]) => {
    const deg = Math.max(-15, Math.min(15, (x + y) * 0.02))
    document.documentElement.style.setProperty('--th-compass-rotate', `${deg}deg`)
})

onMounted(() => {
    document.documentElement.style.setProperty('--th-compass-rotate', '0deg')
    hintTimer = setTimeout(() => { hintVisible.value = false }, 4000)
})
onBeforeUnmount(() => {
    if (hintTimer) clearTimeout(hintTimer)
    document.documentElement.style.removeProperty('--th-compass-rotate')
})
</script>

<style scoped>
.th-isle {
    position: fixed; inset: 0; background: var(--th-ink, #3D2817);
    overflow: hidden; user-select: none; touch-action: none;
}
.th-isle__canvas {
    position: absolute; left: 50%; top: 50%;
    width: 100vw; height: 100dvh;
    transform: translate(-50%, -50%)
        translate3d(var(--th-pan-x, 0px), var(--th-pan-y, 0px), 0)
        scale(var(--th-zoom, 1));
    transform-origin: center center;
    transition: transform 0.05s linear; will-change: transform; cursor: grab;
}
.th-isle__canvas.is-dragging { cursor: grabbing; transition: none; }
.th-isle__parchment, .th-isle__map {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: cover; pointer-events: none;
}
.th-isle__map { object-fit: contain; }
.th-isle__cartouche {
    position: absolute; left: 50%; top: 6%; transform: translateX(-50%);
    width: 32%; min-width: 240px; pointer-events: none; z-index: 6;
}
.th-isle__cartouche img { width: 100%; height: auto; display: block; }
.th-isle__cartouche-text {
    position: absolute; inset: 0; display: grid; place-items: center;
    font-family: 'IM Fell English', serif; font-style: italic;
    color: var(--th-ink, #3D2817); font-size: clamp(14px, 1.6vw, 22px); text-align: center;
}
.th-isle__watermark { position: absolute; right: 12%; bottom: 6%; z-index: 6; pointer-events: none; }
.th-tutorial-hint {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
    padding: 10px 18px; background: rgba(232,213,160,0.92);
    color: var(--th-ink, #3D2817); font-family: 'Cinzel', serif;
    font-size: 13px; letter-spacing: 0.05em;
    border: 1px solid var(--th-aged-border, #A88A4F);
    border-radius: 2px; z-index: 40; pointer-events: none;
}
.th-hint-enter-active, .th-hint-leave-active { transition: opacity 0.4s ease; }
.th-hint-enter-from, .th-hint-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .th-isle__canvas { transition: none; }
    .th-hint-enter-active, .th-hint-leave-active { transition: none; }
}
</style>
