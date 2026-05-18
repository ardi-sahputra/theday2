<script setup>
import { onBeforeUnmount } from 'vue'

const props = defineProps({
    enabled: { type: Boolean, default: true },
})

let audioCtx = null

function ensureCtx() {
    if (typeof window === 'undefined') return null
    if (!audioCtx) {
        const Ctx = window.AudioContext || window.webkitAudioContext
        if (!Ctx) return null
        audioCtx = new Ctx()
    }
    if (audioCtx.state === 'suspended') {
        // Must be called within user gesture for autoplay-policy compliance.
        audioCtx.resume().catch(() => {})
    }
    return audioCtx
}

function playChime() {
    if (!props.enabled) return
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    const ctx = ensureCtx()
    if (!ctx) return
    const now = ctx.currentTime
    const notes = [523.25, 659.25, 783.99] // C5, E5, G5
    notes.forEach((freq, i) => {
        const osc  = ctx.createOscillator()
        const gain = ctx.createGain()
        osc.type = 'sine'
        osc.frequency.value = freq
        osc.connect(gain).connect(ctx.destination)
        const start = now + i * 0.08
        gain.gain.setValueAtTime(0, start)
        gain.gain.linearRampToValueAtTime(0.15, start + 0.02)
        gain.gain.exponentialRampToValueAtTime(0.001, start + 0.4)
        osc.start(start)
        osc.stop(start + 0.45)
    })
}

onBeforeUnmount(() => {
    if (audioCtx && audioCtx.state !== 'closed') {
        audioCtx.close().catch(() => {})
        audioCtx = null
    }
})

defineExpose({ playChime })
</script>

<template><div style="display:none" aria-hidden="true"/></template>
