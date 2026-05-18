<script setup>
import { computed } from 'vue'

const props = defineProps({
    tint:    { type: String, default: 'white' },    // white | ivory | blush | champagne
    side:    { type: String, default: 'full' },     // left | right | full
    opacity: { type: Number, default: 0.92 },
})

const tintHex = computed(() => {
    switch (props.tint) {
        case 'ivory':     return '#F5EFE0'
        case 'blush':     return '#FBE8E5'
        case 'champagne': return '#F0E2BE'
        case 'white':
        default:          return '#FAFAF5'
    }
})

const sideClass = computed(() => `sv-silk--${props.side}`)
</script>

<template>
    <svg
        class="sv-silk"
        :class="sideClass"
        :style="{ opacity }"
        xmlns="http://www.w3.org/2000/svg"
        preserveAspectRatio="none"
        viewBox="0 0 200 400"
        aria-hidden="true"
    >
        <defs>
            <linearGradient id="sv-silk-drape" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" :stop-color="tintHex" stop-opacity="1"/>
                <stop offset="55%" :stop-color="tintHex" stop-opacity="0.96"/>
                <stop offset="100%" stop-color="#C9C2B3" stop-opacity="0.18"/>
            </linearGradient>
            <pattern id="sv-silk-weave" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
                <path d="M0 0 L8 8" stroke="#7A6F65" stroke-width="0.4" stroke-opacity="0.06"/>
                <path d="M8 0 L0 8" stroke="#7A6F65" stroke-width="0.4" stroke-opacity="0.06"/>
            </pattern>
            <radialGradient id="sv-cloth-shadow" cx="50%" cy="100%" r="80%">
                <stop offset="0%" stop-color="#7A6F65" stop-opacity="0.15"/>
                <stop offset="100%" stop-color="#7A6F65" stop-opacity="0"/>
            </radialGradient>
        </defs>
        <rect width="200" height="400" fill="url(#sv-silk-drape)"/>
        <rect width="200" height="400" fill="url(#sv-silk-weave)"/>
        <rect width="200" height="400" fill="url(#sv-cloth-shadow)"/>
    </svg>
</template>

<style scoped>
.sv-silk {
    width: 100%;
    height: 100%;
    display: block;
}
.sv-silk--left,
.sv-silk--right,
.sv-silk--full { width: 100%; height: 100%; }
</style>
