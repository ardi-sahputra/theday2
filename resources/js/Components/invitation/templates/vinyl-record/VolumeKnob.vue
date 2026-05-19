<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'

const props = defineProps({
    value:    { type: Number,  default: 0.6 },  // 0..1
    disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['update:value'])

const dragging = ref(false)
const startY   = ref(0)
const startVal = ref(0)

const angle = computed(() => (props.value - 0.5) * 270) // -135..+135 deg

function clamp(v) { return Math.max(0, Math.min(1, v)) }

function onPointerDown(ev) {
    if (props.disabled) return
    dragging.value = true
    startY.value = ev.clientY
    startVal.value = props.value
    window.addEventListener('pointermove', onPointerMove)
    window.addEventListener('pointerup',   onPointerUp,   { once: true })
}
function onPointerMove(ev) {
    if (!dragging.value) return
    const delta = startY.value - ev.clientY // up = increase
    emit('update:value', clamp(startVal.value + delta * 0.003))
}
function onPointerUp() {
    dragging.value = false
    window.removeEventListener('pointermove', onPointerMove)
}
function onKey(ev) {
    if (props.disabled) return
    if (ev.key === 'ArrowUp' || ev.key === 'ArrowRight') {
        ev.preventDefault()
        emit('update:value', clamp(props.value + 0.05))
    } else if (ev.key === 'ArrowDown' || ev.key === 'ArrowLeft') {
        ev.preventDefault()
        emit('update:value', clamp(props.value - 0.05))
    } else if (ev.key === 'Home') {
        ev.preventDefault(); emit('update:value', 0)
    } else if (ev.key === 'End') {
        ev.preventDefault(); emit('update:value', 1)
    }
}
onBeforeUnmount(() => {
    window.removeEventListener('pointermove', onPointerMove)
})
</script>

<template>
    <div
        class="vr-knob"
        :class="{ 'vr-knob--disabled': disabled }"
        :data-disabled="disabled"
        role="slider"
        aria-orientation="vertical"
        aria-valuemin="0" aria-valuemax="1"
        :aria-valuenow="value"
        :aria-disabled="disabled"
        :tabindex="disabled ? -1 : 0"
        @pointerdown="onPointerDown"
        @keydown="onKey"
        :title="disabled ? 'No audio file' : 'Volume'"
    >
        <div class="vr-knob-face" :style="{ transform: `rotate(${angle}deg)` }">
            <span class="vr-knob-dot"/>
        </div>
        <span class="vr-knob-label">VOL</span>
    </div>
</template>

<style scoped>
.vr-knob {
    display: inline-flex; flex-direction: column; align-items: center;
    gap: 4px;
    cursor: grab;
    user-select: none;
}
.vr-knob:active { cursor: grabbing; }
.vr-knob:focus-visible { outline: 2px solid #B8902F; outline-offset: 4px; border-radius: 50%; }
.vr-knob-face {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: radial-gradient(circle at 30% 30%, #D4AA42 0%, #B8902F 60%, #8e6f24 100%);
    position: relative;
    transition: transform 0.1s linear;
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.4), 0 2px 6px rgba(0,0,0,0.3);
}
.vr-knob-dot {
    position: absolute;
    top: 4px; left: 50%;
    transform: translateX(-50%);
    width: 4px; height: 4px;
    background: #F5E6CC;
    border-radius: 50%;
}
.vr-knob-label {
    font-family: 'Bebas Neue', sans-serif;
    color: #D8C8A8;
    font-size: 10px;
    letter-spacing: 0.2em;
}
.vr-knob--disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.vr-knob--disabled .vr-knob-face { cursor: not-allowed; }
@media (prefers-reduced-motion: reduce) {
    .vr-knob-face { transition: none; }
}
</style>
