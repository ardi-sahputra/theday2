<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    imageUrl: { type: String, default: null },
})

const frameEl   = ref(null)
const dragging  = ref(false)
const lensXPct  = ref(70)   // % of frame width
const lensYPct  = ref(78)   // % of frame height, parked lower-right like a glass left on the desk
const zoom      = ref(2.1)
const lensSize  = ref(190)  // px, recalculated from frame width on mount/resize

function clamp(n, min, max) { return Math.min(max, Math.max(min, n)) }

function recalcSize() {
    const w = frameEl.value?.clientWidth ?? 0
    if (w) lensSize.value = Math.round(clamp(w * 0.34, 150, 240))
}

function moveLensTo(clientX, clientY) {
    const rect = frameEl.value?.getBoundingClientRect()
    if (!rect) return
    lensXPct.value = clamp(((clientX - rect.left) / rect.width) * 100, 0, 100)
    lensYPct.value = clamp(((clientY - rect.top) / rect.height) * 100, 0, 100)
}

// The loupe only moves when grabbed by its own ring/grip — dragging
// elsewhere on the photo is reserved for the page-turn gesture in the
// parent gallery, so pointerdown here must never bubble up.
function onRingPointerDown(e) {
    dragging.value = true
    e.stopPropagation()
    e.target.setPointerCapture?.(e.pointerId)
}
function onRingPointerMove(e) {
    if (!dragging.value) return
    moveLensTo(e.clientX, e.clientY)
}
function onPointerUp() { dragging.value = false }

const lensStyle = computed(() => ({
    '--lr': `${lensSize.value}px`,
    left: `calc(${lensXPct.value}% - ${lensSize.value / 2}px)`,
    top:  `calc(${lensYPct.value}% - ${lensSize.value / 2}px)`,
}))
const glassStyle = computed(() => ({
    backgroundImage: props.imageUrl ? `url(${props.imageUrl})` : 'none',
    backgroundSize: `${zoom.value * 100}% ${zoom.value * 100}%`,
    backgroundPosition: `${lensXPct.value}% ${lensYPct.value}%`,
}))

function zoomIn()  { zoom.value = clamp(+(zoom.value + 0.25).toFixed(2), 1.4, 3.2) }
function zoomOut() { zoom.value = clamp(+(zoom.value - 0.25).toFixed(2), 1.4, 3.2) }

let ro = null
onMounted(() => {
    recalcSize()
    if (typeof ResizeObserver !== 'undefined' && frameEl.value) {
        ro = new ResizeObserver(recalcSize)
        ro.observe(frameEl.value)
    }
})
onBeforeUnmount(() => { dragging.value = false; ro?.disconnect() })
</script>

<template>
    <div ref="frameEl" class="skm-frame">
        <img v-if="imageUrl" :src="imageUrl" alt="" class="skm-photo" draggable="false" />
        <div v-else class="skm-photo skm-photo--placeholder" />

        <!-- the loupe: a real magnifier lying on the page. Grab the ring
             or grip to drag it — everything else belongs to the page-turn
             gesture handled by the parent. -->
        <div class="skm-loupe" :style="lensStyle" :class="{ 'skm-loupe--dragging': dragging }">
            <span
                class="skm-grip"
                aria-hidden="true"
                @pointerdown="onRingPointerDown"
                @pointermove="onRingPointerMove"
                @pointerup="onPointerUp"
                @pointercancel="onPointerUp"
            ></span>
            <span
                class="skm-ring"
                @pointerdown="onRingPointerDown"
                @pointermove="onRingPointerMove"
                @pointerup="onPointerUp"
                @pointercancel="onPointerUp"
            >
                <span class="skm-lens" :style="glassStyle"></span>
            </span>
        </div>

        <div class="skm-controls" @pointerdown.stop @click.stop>
            <button type="button" class="skm-zoom-btn" @click="zoomOut" aria-label="Perkecil kaca pembesar">&minus;</button>
            <span class="skm-zoom-readout">{{ Math.round(zoom * 100) }}%</span>
            <button type="button" class="skm-zoom-btn" @click="zoomIn" aria-label="Perbesar kaca pembesar">+</button>
        </div>

        <p class="skm-hint">Geser kaca pembesar untuk lihat detail</p>
    </div>
</template>

<style scoped>
.skm-frame {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: visible;
}
.skm-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    user-select: none;
    pointer-events: none;
    /* the plate bleeds into the paper as an irregular painted edge — a
       hand-torn cloud outline, not a hard photo-rectangle crop */
    -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 282'%3E%3Cdefs%3E%3Cfilter id='b' x='-20%25' y='-20%25' width='140%25' height='140%25'%3E%3CfeGaussianBlur stdDeviation='9'/%3E%3C/filter%3E%3C/defs%3E%3Cpath filter='url(%23b)' fill='%23fff' d='M367,111 Q383,141 364,171 Q345,200 321,228 Q296,256 248,259 Q200,261 154,257 Q107,253 83,226 Q58,198 35,170 Q11,141 32,112 Q53,82 81,57 Q109,32 155,29 Q200,25 249,25 Q297,24 324,53 Q350,81 367,111 Z'/%3E%3C/svg%3E");
    -webkit-mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
    -webkit-mask-position: center;
    mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 282'%3E%3Cdefs%3E%3Cfilter id='b2' x='-20%25' y='-20%25' width='140%25' height='140%25'%3E%3CfeGaussianBlur stdDeviation='9'/%3E%3C/filter%3E%3C/defs%3E%3Cpath filter='url(%23b2)' fill='%23fff' d='M367,111 Q383,141 364,171 Q345,200 321,228 Q296,256 248,259 Q200,261 154,257 Q107,253 83,226 Q58,198 35,170 Q11,141 32,112 Q53,82 81,57 Q109,32 155,29 Q200,25 249,25 Q297,24 324,53 Q350,81 367,111 Z'/%3E%3C/svg%3E");
    mask-size: 100% 100%;
    mask-repeat: no-repeat;
    mask-position: center;
}
.skm-photo--placeholder { background: #e5ddcc; }

/* ---- the loupe: ring bezel + grip handle + glass dome ---- */
.skm-loupe {
    position: absolute;
    width: var(--lr, 190px);
    height: var(--lr, 190px);
    z-index: 8;
    will-change: transform;
}
.skm-grip {
    position: absolute;
    left: 50%;
    top: 50%;
    width: calc(var(--lr, 190px) * 0.74);
    height: calc(var(--lr, 190px) * 0.125);
    transform-origin: 0 50%;
    transform: rotate(40deg) translate(calc(var(--lr, 190px) * 0.33), -50%);
    border-radius: calc(var(--lr, 190px) * 0.06);
    background:
        linear-gradient(180deg, rgba(255,255,255,.46) 0 13%, rgba(255,255,255,0) 44%, rgba(0,0,0,.26) 100%),
        linear-gradient(90deg, #d9bd82 0 14%, #a9884e 14% 20%, #6d4c2b 20% 62%, #5a3d22 62% 92%, #7a563180 92% 100%);
    box-shadow: 0 8px 15px rgba(58,44,26,.26), 0 18px 26px rgba(58,44,26,.14);
    cursor: grab;
    touch-action: none;
}
.skm-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    cursor: grab;
    touch-action: none;
    padding: calc(var(--lr, 190px) * 0.058);
    box-shadow:
        0 1px 2px rgba(58,44,26,.30),
        0 10px 18px rgba(58,44,26,.24),
        0 26px 40px rgba(58,44,26,.20);
    display: block;
}
.skm-loupe--dragging .skm-ring { cursor: grabbing; }
.skm-ring::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    pointer-events: none;
    background: linear-gradient(146deg,
        #fdf7e9 0%, #e6d7b4 14%, #b69d70 32%, #7d6740 50%,
        #cdbb92 66%, #f4ead3 80%, #9b8459 100%);
    box-shadow:
        inset 0 1px 1px rgba(255,255,255,.8),
        inset 0 -2px 3px rgba(70,52,26,.5);
    -webkit-mask-image: radial-gradient(circle closest-side at 50% 50%, transparent 0 88.2%, #000 89.8% 100%);
    mask-image: radial-gradient(circle closest-side at 50% 50%, transparent 0 88.2%, #000 89.8% 100%);
}
.skm-lens {
    position: relative;
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    overflow: hidden;
    background-repeat: no-repeat;
    box-shadow:
        inset 0 0 0 1px rgba(52,40,22,.55),
        inset 0 4px 12px rgba(40,30,14,.28),
        inset 0 -7px 16px rgba(255,250,240,.14);
}
/* the glass bends and darkens toward its rim */
.skm-lens::before, .skm-lens::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    pointer-events: none;
}
.skm-lens::before {
    background: radial-gradient(circle at 50% 50%, rgba(0,0,0,0) 54%, rgba(58,44,26,.10) 76%, rgba(46,34,16,.34) 100%);
    box-shadow:
        inset 0 0 0 2px rgba(130,162,196,.26),
        inset 0 0 0 4px rgba(206,158,112,.15);
}
/* the dome: one broad specular and a tight crescent opposite it */
.skm-lens::after {
    background:
        radial-gradient(36% 26% at 29% 19%, rgba(255,255,255,.30), rgba(255,255,255,0) 76%),
        radial-gradient(24% 16% at 74% 86%, rgba(255,255,255,.12), rgba(255,255,255,0) 80%),
        linear-gradient(150deg, rgba(255,255,255,.06) 0 18%, rgba(255,255,255,0) 42%);
}

.skm-controls {
    position: absolute;
    left: 12px;
    bottom: 12px;
    z-index: 9;
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 252, 244, 0.88);
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
}
.skm-zoom-btn {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 1px solid rgba(43,39,33,.15);
    background: #fff;
    cursor: pointer;
    line-height: 1;
    transition: background 0.2s ease, transform 0.15s ease;
}
.skm-zoom-btn:hover { background: #f0ebe0; transform: translateY(-1px); }
.skm-zoom-readout { min-width: 34px; text-align: center; color: #4a4436; }

.skm-hint {
    position: absolute;
    right: 12px;
    bottom: 14px;
    z-index: 9;
    margin: 0;
    font-size: 11px;
    letter-spacing: 0.04em;
    color: rgba(255, 255, 255, 0.85);
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    pointer-events: none;
}

@media (prefers-reduced-motion: reduce) {
    .skm-zoom-btn { transition: none; }
}

@media (max-width: 480px) {
    .skm-hint { display: none; }
}
@media (pointer: coarse) {
    .skm-loupe { transform: scale(0.86); }
}
</style>
