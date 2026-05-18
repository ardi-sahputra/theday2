<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import RippleAnim  from './RippleAnim.vue'
import SilkTexture from './SilkTexture.vue'
import LaceTrim    from './LaceTrim.vue'
import PearlDecor  from './PearlDecor.vue'

const props = defineProps({
    sectionKey:   { type: String, required: true },
    initialState: { type: String, default: 'covered' }, // covered | parted
    autoPart:     { type: Boolean, default: false },
    veilColor:    { type: String, default: 'white' },
    laceDensity:  { type: String, default: 'medium' },
    pearlDecor:   { type: String, default: 'edges' },
})

const emit = defineEmits(['part', 'drag-start'])

const DRAG_THRESHOLD = 12 // px before drag kicks in
const SNAP_RATIO     = 0.35

// State: covered | dragging | snapping-back | parting | tap-parting | parted
const state = ref(props.initialState === 'parted' ? 'parted' : 'covered')

const rootEl   = ref(null)
const fabricEl = ref(null)
const hintVisible = ref(true)
let dragStartX = 0
let dragStartY = 0
let dragging   = false
let pointerId  = null
let hintTimer  = null
let autoPartObserver = null

watch(() => props.initialState, (newVal) => {
    if (newVal === 'parted') state.value = 'parted'
})

function setDragX(px) {
    if (fabricEl.value) {
        fabricEl.value.style.setProperty('--sv-drag-x', `${px}px`)
    }
}

function onPointerDown(e) {
    if (state.value === 'parted') return
    if (state.value === 'parting' || state.value === 'tap-parting') return
    dragStartX = e.clientX
    dragStartY = e.clientY
    dragging = false
    pointerId = e.pointerId
    try { e.currentTarget.setPointerCapture(e.pointerId) } catch (_) {}
    hintVisible.value = false
}

function onPointerMove(e) {
    if (pointerId === null) return
    const dx = e.clientX - dragStartX
    const dy = e.clientY - dragStartY
    const absDx = Math.abs(dx)
    const absDy = Math.abs(dy)

    // Vertical scroll intent → release pointer capture, let native scroll
    if (!dragging && absDy > absDx * 2 && absDy > DRAG_THRESHOLD) {
        try { e.currentTarget.releasePointerCapture(pointerId) } catch (_) {}
        pointerId = null
        dragging = false
        return
    }
    if (!dragging && absDx < DRAG_THRESHOLD) return
    if (!dragging) {
        dragging = true
        state.value = 'dragging'
        emit('drag-start')
    }
    setDragX(absDx)
}

function onPointerUp(e) {
    if (pointerId === null) return
    const id = pointerId
    pointerId = null
    try { e.currentTarget.releasePointerCapture(id) } catch (_) {}

    if (!dragging) {
        // No real drag → treat as tap
        onTap()
        return
    }

    const finalDelta = Math.abs(e.clientX - dragStartX)
    const width = fabricEl.value?.offsetWidth ?? 0
    const threshold = width * SNAP_RATIO

    if (finalDelta >= threshold) {
        snapOpen()
    } else {
        snapBack()
    }
    dragging = false
}

function onPointerCancel() {
    if (dragging) snapBack()
    pointerId = null
    dragging = false
}

function snapBack() {
    state.value = 'snapping-back'
    setDragX(0)
    setTimeout(() => {
        if (state.value === 'snapping-back') {
            state.value = 'covered'
            hintVisible.value = true
        }
    }, 600)
}

function snapOpen() {
    state.value = 'parting'
    setTimeout(() => {
        state.value = 'parted'
        emit('part')
    }, 500)
}

function onTap() {
    if (state.value === 'parted') return
    state.value = 'tap-parting'
    setTimeout(() => {
        state.value = 'parted'
        emit('part')
    }, 1500)
}

function onKeydown(e) {
    if (state.value === 'parted') return
    if (e.key === 'Enter' || e.key === ' ' || e.code === 'Space') {
        e.preventDefault()
        onTap()
    }
}

onMounted(() => {
    if (props.autoPart && state.value === 'covered' && 'IntersectionObserver' in window) {
        autoPartObserver = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting && state.value === 'covered') {
                    onTap()
                    autoPartObserver?.disconnect()
                    autoPartObserver = null
                    break
                }
            }
        }, { threshold: 0.4 })
        if (rootEl.value) autoPartObserver.observe(rootEl.value)
    }

    // Idle hint re-fade after 3s if user starts then aborts
    hintTimer = setInterval(() => {
        if (state.value === 'covered' && !hintVisible.value) {
            hintVisible.value = true
        }
    }, 3000)
})

onBeforeUnmount(() => {
    if (autoPartObserver) { autoPartObserver.disconnect(); autoPartObserver = null }
    if (hintTimer) { clearInterval(hintTimer); hintTimer = null }
})

const showVeil = computed(() => state.value !== 'parted')
const fabricStateClass = computed(() => `sv-veil-fabric--${state.value}`)

const ariaLabel = computed(() => `Buka veil untuk section ${props.sectionKey}`)
</script>

<template>
    <div ref="rootEl" class="sv-veil-overlay" :class="`sv-veil--${sectionKey}`">
        <!-- Section content underneath -->
        <div class="sv-veil-content">
            <slot/>
        </div>

        <!-- Veil layer -->
        <div
            v-if="showVeil"
            ref="fabricEl"
            class="sv-veil-layer"
            :class="fabricStateClass"
            role="button"
            tabindex="0"
            :aria-label="ariaLabel"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp"
            @pointercancel="onPointerCancel"
            @keydown="onKeydown"
        >
            <RippleAnim :enabled="state === 'covered'">
                <!-- Veil two halves with clip-path-like overflow control -->
                <div class="sv-veil-half sv-veil-half--left">
                    <SilkTexture :tint="veilColor" side="left"/>
                </div>
                <div class="sv-veil-half sv-veil-half--right">
                    <SilkTexture :tint="veilColor" side="right"/>
                </div>

                <!-- Lace top + bottom trim -->
                <LaceTrim variant="veil-edge" :density="laceDensity" class="sv-veil-edge sv-veil-edge--top"/>
                <LaceTrim variant="veil-edge" :density="laceDensity" class="sv-veil-edge sv-veil-edge--bot"/>

                <!-- Pearl strand top + bottom (if pearlDecor !== 'none') -->
                <template v-if="pearlDecor !== 'none'">
                    <PearlDecor variant="strand-horizontal" :count="12" :size="6" class="sv-veil-pearls sv-veil-pearls--top"/>
                    <PearlDecor variant="strand-horizontal" :count="12" :size="6" class="sv-veil-pearls sv-veil-pearls--bot"/>
                </template>

                <!-- Drag hint -->
                <p
                    v-show="hintVisible && state === 'covered'"
                    class="sv-veil-hint"
                >
                    Geser atau ketuk untuk membuka
                </p>
            </RippleAnim>
        </div>
    </div>
</template>

<style scoped>
.sv-veil-overlay {
    position: relative;
    width: 100%;
}

.sv-veil-content {
    position: relative;
    z-index: 1;
}

.sv-veil-layer {
    position: absolute;
    inset: 0;
    z-index: 2;
    cursor: grab;
    touch-action: pan-y; /* allow vertical scroll, capture horizontal */
    user-select: none;
    outline: none;
    overflow: hidden;
    min-height: var(--sv-veil-thickness, 260px);
}
.sv-veil-layer:focus-visible {
    box-shadow: inset 0 0 0 2px var(--sv-gold, #C9A961);
}
.sv-veil-layer[role="button"]:active { cursor: grabbing; }

.sv-veil-half {
    position: absolute;
    top: 0;
    height: 100%;
    width: 50%;
    overflow: hidden;
    transform: translateX(var(--sv-half-translate, 0px));
    will-change: transform, opacity;
}
.sv-veil-half--left  { left: 0;  --sv-half-translate: calc(var(--sv-drag-x, 0px) * -1); }
.sv-veil-half--right { right: 0; --sv-half-translate: var(--sv-drag-x, 0px); }

/* While dragging — no transition, follow pointer instantly */
.sv-veil-fabric--dragging .sv-veil-half {
    transition: none;
}

/* Snap-back spring */
.sv-veil-fabric--snapping-back .sv-veil-half {
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    --sv-half-translate: 0px !important;
}

/* Snap-open drag past midpoint */
.sv-veil-fabric--parting .sv-veil-half {
    transition: transform 0.5s ease-out, opacity 0.5s ease-out;
    opacity: 0;
}
.sv-veil-fabric--parting .sv-veil-half--left  { --sv-half-translate: -110% !important; }
.sv-veil-fabric--parting .sv-veil-half--right { --sv-half-translate:  110% !important; }

/* Tap-to-part cloth ripple keyframes */
@keyframes sv-tap-part-left {
    0%   { transform: translateX(0)     skewY(0deg);  opacity: 1; }
    30%  { transform: translateX(-30px) skewY(-1deg); opacity: 1; }
    60%  { transform: translateX(-60px) skewY(0.5deg); opacity: 0.85; }
    100% { transform: translateX(-110%) skewY(0deg);  opacity: 0; }
}
@keyframes sv-tap-part-right {
    0%   { transform: translateX(0)    skewY(0deg);   opacity: 1; }
    30%  { transform: translateX(30px) skewY(1deg);   opacity: 1; }
    60%  { transform: translateX(60px) skewY(-0.5deg); opacity: 0.85; }
    100% { transform: translateX(110%) skewY(0deg);   opacity: 0; }
}
.sv-veil-fabric--tap-parting .sv-veil-half--left  { animation: sv-tap-part-left  1.5s cubic-bezier(0.65, 0, 0.35, 1) forwards; }
.sv-veil-fabric--tap-parting .sv-veil-half--right { animation: sv-tap-part-right 1.5s cubic-bezier(0.65, 0, 0.35, 1) forwards; }

/* Edges */
.sv-veil-edge {
    position: absolute;
    left: 0; right: 0;
    width: 100%;
    height: 12px;
    z-index: 3;
    pointer-events: none;
}
.sv-veil-edge--top { top: 0; }
.sv-veil-edge--bot { bottom: 0; transform: scaleY(-1); }

/* Pearl strands */
.sv-veil-pearls {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    z-index: 4;
    pointer-events: none;
}
.sv-veil-pearls--top { top: 14px; }
.sv-veil-pearls--bot { bottom: 14px; }

/* Drag hint */
.sv-veil-hint {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted, #7A6F65);
    background: rgba(250, 250, 245, 0.7);
    padding: 8px 14px;
    border: 1px solid rgba(201, 169, 97, 0.4);
    border-radius: 2px;
    z-index: 5;
    pointer-events: none;
    transition: opacity 0.4s ease;
}

/* Reduced motion: drag preserved as essential, but cloth keyframes short-circuit */
@media (prefers-reduced-motion: reduce) {
    .sv-veil-fabric--snapping-back .sv-veil-half {
        transition: transform 0.2s ease-out;
    }
    .sv-veil-fabric--parting .sv-veil-half {
        transition: opacity 0.3s ease;
    }
    .sv-veil-fabric--parting .sv-veil-half--left,
    .sv-veil-fabric--parting .sv-veil-half--right {
        --sv-half-translate: 0px !important;
    }
    .sv-veil-fabric--tap-parting .sv-veil-half {
        animation: none;
        transition: opacity 0.3s ease;
        opacity: 0;
    }
}
</style>
