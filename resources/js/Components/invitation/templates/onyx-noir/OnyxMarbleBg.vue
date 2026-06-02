<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    intensity: { type: String, default: 'subtle' }, // subtle | medium | strong
})

const opacityVal = computed(() => ({
    subtle: 0.25,
    medium: 0.5,
    strong: 0.75,
}[props.intensity] ?? 0.25))

let onScroll = null

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    let ticking = false
    onScroll = () => {
        if (ticking) return
        ticking = true
        requestAnimationFrame(() => {
            const offset = window.scrollY * 0.3
            document.documentElement.style.setProperty('--onx-vein-offset', `-${offset}px`)
            ticking = false
        })
    }
    window.addEventListener('scroll', onScroll, { passive: true })
})

onBeforeUnmount(() => {
    if (onScroll) window.removeEventListener('scroll', onScroll)
})
</script>

<template>
    <div class="onyx-marble-bg" aria-hidden="true">
        <div class="onyx-marble-base" :style="{ opacity: opacityVal }"/>
        <div class="onyx-marble-vein"/>
        <slot/>
    </div>
</template>

<style scoped>
.onyx-marble-bg {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}
.onyx-marble-base {
    position: absolute; inset: 0;
    background: url('/images/templates/onyx-noir/marble-bg.svg') center/cover no-repeat, #0a0a0a;
    will-change: opacity;
}
.onyx-marble-vein {
    position: absolute; inset: 0;
    background: url('/images/templates/onyx-noir/veins.svg') repeat-y center top;
    background-size: cover;
    transform: translate3d(0, var(--onx-vein-offset, 0px), 0);
    will-change: transform;
    mix-blend-mode: screen;
    opacity: 0.5;
}
@media (prefers-reduced-motion: reduce) {
    .onyx-marble-vein { transform: none; }
}
</style>
