<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
    beamRadius: { type: Number,  default: 200 },
    warmth:     { type: String,  default: 'warm' }, // cool | neutral | warm
    disabled:   { type: Boolean, default: false },
})

const emit = defineEmits(['beam-move', 'beam-tick'])

const rootEl = ref(null)
const overlayEl = ref(null)

// Reduced motion detection (live)
const reducedMotion = ref(false)
let mql = null

// Pointer state
const targetX = ref(0)
const targetY = ref(0)
let currentX = 0
let currentY = 0
let rafId = null

// Beam radius state (px, derived from preset prop but mutable in-session via wheel/pinch)
const currentRadius = ref(props.beamRadius)
let radiusTweenRaf = null

// Trail history (for LightTrail component data-driven via emit)
const trailHistory = ref([])
const MAX_TRAIL = 8

// Mask support detection
const maskSupported = ref(true)

const warmthHex = computed(() => ({
    cool:    '#FFFFFF',
    neutral: '#FFF4D6',
    warm:    '#FFD580',
}[props.warmth] ?? '#FFD580'))

function setVar(name, value) {
    if (!overlayEl.value) return
    overlayEl.value.style.setProperty(name, value)
}

function tick() {
    if (props.disabled) {
        rafId = requestAnimationFrame(tick)
        return
    }
    currentX += (targetX.value - currentX) * 0.15
    currentY += (targetY.value - currentY) * 0.15
    setVar('--fl-x', `${currentX}px`)
    setVar('--fl-y', `${currentY}px`)

    // Trail bookkeeping
    if (!reducedMotion.value) {
        const now = performance.now()
        trailHistory.value.push({ x: currentX, y: currentY, t: now })
        if (trailHistory.value.length > MAX_TRAIL) trailHistory.value.shift()
        trailHistory.value = trailHistory.value.filter(p => now - p.t < 400)
    }

    emit('beam-tick', { x: currentX, y: currentY, radius: currentRadius.value, trail: trailHistory.value })
    rafId = requestAnimationFrame(tick)
}

function onPointerMove(e) {
    if (props.disabled) return
    targetX.value = e.clientX
    targetY.value = e.clientY
    emit('beam-move', { x: e.clientX, y: e.clientY })
    if (reducedMotion.value) {
        // Snap mode — update CSS variable immediately
        setVar('--fl-x', `${e.clientX}px`)
        setVar('--fl-y', `${e.clientY}px`)
    }
}

function tweenRadius(from, to, durationMs, easing = 'ease-out') {
    if (radiusTweenRaf) cancelAnimationFrame(radiusTweenRaf)
    const t0 = performance.now()
    const ease = easing === 'ease-in'
        ? (p) => p * p * p
        : (p) => 1 - Math.pow(1 - p, 3) // ease-out cubic default
    function step(t) {
        const p = Math.min(1, (t - t0) / durationMs)
        const eased = ease(p)
        const v = from + (to - from) * eased
        currentRadius.value = v
        setVar('--fl-beam-radius', `${v}px`)
        if (p < 1) {
            radiusTweenRaf = requestAnimationFrame(step)
        } else {
            radiusTweenRaf = null
        }
    }
    radiusTweenRaf = requestAnimationFrame(step)
}

function adjustBeam(delta) {
    const target = Math.max(100, Math.min(360, currentRadius.value + delta))
    if (reducedMotion.value) {
        currentRadius.value = target
        setVar('--fl-beam-radius', `${target}px`)
        return
    }
    tweenRadius(currentRadius.value, target, 300, 'ease-out')
}

function onWheel(e) {
    if (props.disabled) return
    e.preventDefault()
    adjustBeam(-e.deltaY * 0.5)
}

// Touch tap-pulse: tap (no drag, release <200ms) -> expand beam burst
function onPointerDown(e) {
    if (props.disabled) return
    if (e.pointerType !== 'touch') return
    const startTime = performance.now()
    const startX = e.clientX
    const startY = e.clientY
    let moved = false

    const onMove = (ev) => {
        if (Math.hypot(ev.clientX - startX, ev.clientY - startY) > 10) moved = true
    }
    const onUp = () => {
        rootEl.value?.removeEventListener('pointermove', onMove)
        rootEl.value?.removeEventListener('pointerup', onUp)
        rootEl.value?.removeEventListener('pointercancel', onUp)
        if (!moved && performance.now() - startTime < 200) {
            triggerTapPulse(startX, startY)
        }
    }
    rootEl.value?.addEventListener('pointermove', onMove)
    rootEl.value?.addEventListener('pointerup', onUp)
    rootEl.value?.addEventListener('pointercancel', onUp)
}

function triggerTapPulse(x, y) {
    // Snap beam to tap location
    targetX.value = x
    targetY.value = y
    currentX = x
    currentY = y
    setVar('--fl-x', `${x}px`)
    setVar('--fl-y', `${y}px`)

    const start = currentRadius.value
    const peak = start * 1.8
    // Expand 0.3s ease-out, then contract 0.4s ease-in
    tweenRadius(start, peak, 300, 'ease-out')
    setTimeout(() => tweenRadius(peak, start, 400, 'ease-in'), 320)
}

// Pinch handling (manual two-finger distance)
let pinchStartDist = null
let pinchStartRadius = null

function onTouchStart(e) {
    if (props.disabled) return
    if (e.touches.length === 2) {
        const [a, b] = e.touches
        pinchStartDist = Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY)
        pinchStartRadius = currentRadius.value
    }
}

function onTouchMove(e) {
    if (props.disabled) return
    if (e.touches.length === 2 && pinchStartDist !== null) {
        e.preventDefault()
        const [a, b] = e.touches
        const dist = Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY)
        const scale = dist / pinchStartDist
        const target = Math.max(100, Math.min(360, pinchStartRadius * scale))
        currentRadius.value = target
        setVar('--fl-beam-radius', `${target}px`)
    }
}

function onTouchEnd() {
    pinchStartDist = null
    pinchStartRadius = null
}

// Keyboard nav — Tab focuses section anchors; we jump beam to focused anchor center
function onFocusIn(e) {
    if (props.disabled) return
    const anchor = e.target?.closest?.('.fl-section-anchor')
    if (!anchor) return
    const rect = anchor.getBoundingClientRect()
    targetX.value = rect.left + rect.width / 2
    targetY.value = rect.top + rect.height / 2
}

// Watch prop changes (e.g., user changes beam_radius preset via wizard)
watch(() => props.beamRadius, (v) => {
    adjustBeam(v - currentRadius.value)
})

onMounted(() => {
    if (typeof window === 'undefined') return

    // Detect mask-image support — fallback strategy if absent
    maskSupported.value = window.CSS?.supports?.(
        'mask-image',
        'radial-gradient(circle, black 50%, transparent 100%)'
    ) || window.CSS?.supports?.(
        '-webkit-mask-image',
        'radial-gradient(circle, black 50%, transparent 100%)'
    ) || false

    if (!maskSupported.value && overlayEl.value) {
        overlayEl.value.classList.add('fl-mask-fallback')
    }

    mql = window.matchMedia('(prefers-reduced-motion: reduce)')
    reducedMotion.value = mql.matches
    const onMqlChange = (e) => { reducedMotion.value = e.matches }
    mql.addEventListener?.('change', onMqlChange)

    // Initial position — center viewport
    const initX = window.innerWidth / 2
    const initY = window.innerHeight / 2
    targetX.value = initX
    targetY.value = initY
    currentX = initX
    currentY = initY
    setVar('--fl-x', `${initX}px`)
    setVar('--fl-y', `${initY}px`)
    setVar('--fl-beam-radius', `${currentRadius.value}px`)
    setVar('--fl-glow-color', warmthHex.value)

    rootEl.value?.addEventListener('pointermove',  onPointerMove,  { passive: true })
    rootEl.value?.addEventListener('pointerdown',  onPointerDown,  { passive: true })
    rootEl.value?.addEventListener('wheel',        onWheel,        { passive: false })
    rootEl.value?.addEventListener('touchstart',   onTouchStart,   { passive: false })
    rootEl.value?.addEventListener('touchmove',    onTouchMove,    { passive: false })
    rootEl.value?.addEventListener('touchend',     onTouchEnd,     { passive: true })
    rootEl.value?.addEventListener('focusin',      onFocusIn)

    rafId = requestAnimationFrame(tick)
})

onBeforeUnmount(() => {
    if (rafId) cancelAnimationFrame(rafId)
    if (radiusTweenRaf) cancelAnimationFrame(radiusTweenRaf)
    rootEl.value?.removeEventListener('pointermove',  onPointerMove)
    rootEl.value?.removeEventListener('pointerdown',  onPointerDown)
    rootEl.value?.removeEventListener('wheel',        onWheel)
    rootEl.value?.removeEventListener('touchstart',   onTouchStart)
    rootEl.value?.removeEventListener('touchmove',    onTouchMove)
    rootEl.value?.removeEventListener('touchend',     onTouchEnd)
    rootEl.value?.removeEventListener('focusin',      onFocusIn)
})

// Expose to parent (orchestrator may read radius / trail / mask support)
defineExpose({ currentRadius, trailHistory, maskSupported })
</script>

<template>
    <div
        ref="rootEl"
        class="fl-beam-mask"
        :class="{ 'fl-beam-disabled': disabled }"
        aria-label="Senter — geser untuk menemukan section"
    >
        <slot/>
        <div ref="overlayEl" class="fl-beam-overlay" aria-hidden="true"/>
    </div>
</template>

<style scoped>
.fl-beam-mask {
    position: relative;
    width: 100%;
    min-height: 100vh;
    background: #000000;
}

.fl-beam-overlay {
    /* Black overlay with a transparent radial "hole" at pointer */
    --fl-x: 50%;
    --fl-y: 50%;
    --fl-beam-radius: 200px;
    --fl-glow-color: #FFD580;
    position: fixed;
    inset: 0;
    background: #000000;
    pointer-events: none;
    z-index: 50;
    -webkit-mask-image: radial-gradient(
        circle at var(--fl-x) var(--fl-y),
        transparent 0px,
        transparent calc(var(--fl-beam-radius) - 60px),
        black var(--fl-beam-radius)
    );
            mask-image: radial-gradient(
        circle at var(--fl-x) var(--fl-y),
        transparent 0px,
        transparent calc(var(--fl-beam-radius) - 60px),
        black var(--fl-beam-radius)
    );
}

/* Disabled (a11y "Show all" toggle) OR missing browser support -> hide overlay */
.fl-beam-disabled .fl-beam-overlay,
.fl-beam-overlay.fl-mask-fallback {
    -webkit-mask-image: none;
            mask-image: none;
    background: transparent;
}

@media (prefers-reduced-motion: reduce) {
    /* Beam still functional in snap mode; just no smooth transitions */
    .fl-beam-overlay { transition: none; }
}
</style>
