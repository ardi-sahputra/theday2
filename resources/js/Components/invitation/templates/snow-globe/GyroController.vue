<script setup>
import { onBeforeUnmount, watch } from 'vue'

const props = defineProps({
    enabled: { type: Boolean, default: false },
})

const emit = defineEmits(['tilt', 'permission'])

let ticking = false
let listenerAttached = false

function handle(e) {
    if (ticking) return
    ticking = true
    requestAnimationFrame(() => {
        const beta  = e.beta  ?? 0     // front-back tilt -180..180
        const gamma = e.gamma ?? 0     // left-right tilt -90..90
        emit('tilt', {
            tiltX: Math.max(-1, Math.min(1, gamma / 30)),
            tiltY: Math.max(-1, Math.min(1, beta  / 60)),
        })
        ticking = false
    })
}

function attach() {
    if (listenerAttached) return
    window.addEventListener('deviceorientation', handle, { passive: true })
    listenerAttached = true
}

function detach() {
    if (!listenerAttached) return
    window.removeEventListener('deviceorientation', handle)
    listenerAttached = false
}

async function requestPermission() {
    if (typeof window === 'undefined') return false
    const DOE = window.DeviceOrientationEvent
    if (DOE && typeof DOE.requestPermission === 'function') {
        try {
            const state = await DOE.requestPermission()
            const granted = state === 'granted'
            emit('permission', granted)
            if (granted) attach()
            return granted
        } catch {
            emit('permission', false)
            return false
        }
    }
    // Android / non-iOS: assume granted, just attach when enabled.
    emit('permission', true)
    attach()
    return true
}

// React to enabled changes (parent toggles via pill).
watch(() => props.enabled, (val) => {
    if (val) {
        // Don't auto-request on iOS without user gesture — handled by exposed method.
        const needsPermission = typeof window !== 'undefined'
            && window.DeviceOrientationEvent
            && typeof window.DeviceOrientationEvent.requestPermission === 'function'
        if (!needsPermission) attach()
    } else {
        detach()
    }
}, { immediate: true })

onBeforeUnmount(detach)

defineExpose({ requestPermission })
</script>

<template><div style="display:none" aria-hidden="true"/></template>
