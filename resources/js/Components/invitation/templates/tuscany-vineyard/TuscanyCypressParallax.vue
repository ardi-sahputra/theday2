<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

defineProps({
    density: { type: String, default: 'medium' }, // sparse | medium | dense
})

const root = ref(null)
let onScroll = null

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    let ticking = false
    onScroll = () => {
        if (ticking) return
        ticking = true
        requestAnimationFrame(() => {
            if (root.value) {
                const y = window.scrollY * 0.3
                root.value.style.setProperty('--tv-parallax-y', `${y}px`)
            }
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
    <div ref="root" class="tv-cypress-horizon" :data-density="density" aria-hidden="true">
        <img src="/images/templates/tuscany-vineyard/cypress-horizon.svg" alt="" draggable="false"/>
    </div>
</template>

<style scoped>
.tv-cypress-horizon {
    position: fixed;
    left: 0; right: 0; bottom: 0;
    height: 28vh;
    z-index: -1;
    pointer-events: none;
    transform: translate3d(0, var(--tv-parallax-y, 0px), 0);
    will-change: transform;
}
.tv-cypress-horizon img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: bottom;
    display: block;
}
/* Density tiers — scale-based approach since SVG loaded as img */
.tv-cypress-horizon[data-density="sparse"] img { transform: scaleX(0.7); transform-origin: center bottom; opacity: 0.85; }
.tv-cypress-horizon[data-density="medium"] img { transform: scaleX(1);   opacity: 0.95; }
.tv-cypress-horizon[data-density="dense"]  img { transform: scaleX(1.25); transform-origin: center bottom; opacity: 1; }

@media (prefers-reduced-motion: reduce) {
    .tv-cypress-horizon { transform: none !important; }
}
</style>
