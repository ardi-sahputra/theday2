<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref, onBeforeUnmount } from 'vue'

const props = defineProps({
    disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['tap-left', 'tap-right', 'hold-start', 'hold-end', 'swipe-down', 'swipe-up'])

const HOLD_THRESHOLD_MS  = 200
const SWIPE_THRESHOLD_PX = 80

const holdTimer     = ref(null)
const pointerStartY = ref(0)
const pointerStartX = ref(0)
const pointerStartT = ref(0)
const isHolding     = ref(false)
const movedAsSwipe  = ref(false)
const leftPulse     = ref(false)
const rightPulse    = ref(false)

function clearHold() {
    if (holdTimer.value) {
        clearTimeout(holdTimer.value)
        holdTimer.value = null
    }
}

function onPointerDown(side, e) {
    if (props.disabled) return
    pointerStartY.value = e.clientY ?? 0
    pointerStartX.value = e.clientX ?? 0
    pointerStartT.value = performance.now()
    isHolding.value    = false
    movedAsSwipe.value = false
    clearHold()
    holdTimer.value = setTimeout(() => {
        isHolding.value = true
        emit('hold-start')
    }, HOLD_THRESHOLD_MS)
}

function onPointerMove(e) {
    if (props.disabled) return
    const dx = (e.clientX ?? 0) - pointerStartX.value
    const dy = (e.clientY ?? 0) - pointerStartY.value
    if (Math.abs(dy) > SWIPE_THRESHOLD_PX && Math.abs(dy) > Math.abs(dx)) {
        if (movedAsSwipe.value) return
        movedAsSwipe.value = true
        clearHold()
        if (dy > 0) emit('swipe-down')
        else        emit('swipe-up')
    }
}

function onPointerUp(side) {
    if (props.disabled) return
    clearHold()
    const dt = performance.now() - pointerStartT.value
    if (movedAsSwipe.value) {
        movedAsSwipe.value = false
        return
    }
    if (isHolding.value) {
        isHolding.value = false
        emit('hold-end')
        return
    }
    if (dt < HOLD_THRESHOLD_MS) {
        if (side === 'left')  { leftPulse.value  = true; setTimeout(() => leftPulse.value  = false, 150); emit('tap-left') }
        else                  { rightPulse.value = true; setTimeout(() => rightPulse.value = false, 150); emit('tap-right') }
    }
}

function onPointerCancel() {
    clearHold()
    if (isHolding.value) {
        isHolding.value = false
        emit('hold-end')
    }
}

onBeforeUnmount(clearHold)
</script>

<template>
    <div class="igs-tap-zones" aria-hidden="true">
        <div
            class="igs-tap-zone igs-tap-zone--left"
            :class="{ 'igs-tap-zone--pulse': leftPulse }"
            @pointerdown="onPointerDown('left',  $event)"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp('left')"
            @pointercancel="onPointerCancel"
        />
        <div
            class="igs-tap-zone igs-tap-zone--right"
            :class="{ 'igs-tap-zone--pulse': rightPulse }"
            @pointerdown="onPointerDown('right', $event)"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp('right')"
            @pointercancel="onPointerCancel"
        />
    </div>
</template>

<style scoped>
.igs-tap-zones {
    position: absolute;
    inset: 80px 0 100px 0;
    display: flex;
    pointer-events: none;
    z-index: 5;
}
.igs-tap-zone {
    pointer-events: auto;
    height: 100%;
    background: transparent;
    touch-action: none;
}
.igs-tap-zone--left  { width: 30%; }
.igs-tap-zone--right { width: 70%; }
.igs-tap-zone--pulse {
    animation: igs-tap-pulse 0.15s ease-out;
}
@keyframes igs-tap-pulse {
    from { background: rgba(255,255,255,0.12); }
    to   { background: transparent; }
}
@media (prefers-reduced-motion: reduce) {
    .igs-tap-zone--pulse { animation: none; }
}
</style>
