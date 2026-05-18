<script setup>
import { computed, ref, onMounted } from 'vue'

const props = defineProps({
    trailHistory: { type: Array, default: () => [] },
})

const reducedMotion = ref(false)
onMounted(() => {
    if (typeof window === 'undefined') return
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
})

const visibleTrail = computed(() => {
    if (reducedMotion.value) return []
    const now = performance.now()
    return props.trailHistory.map((p) => {
        const age = now - p.t
        const opacity = Math.max(0, 1 - age / 400) * 0.8
        return { x: p.x, y: p.y, opacity }
    }).filter(p => p.opacity > 0.02)
})
</script>

<template>
    <div v-if="visibleTrail.length" class="fl-light-trail" aria-hidden="true">
        <div
            v-for="(dot, i) in visibleTrail"
            :key="i"
            class="fl-trail-dot"
            :style="{
                left:    dot.x + 'px',
                top:     dot.y + 'px',
                opacity: dot.opacity,
            }"
        />
    </div>
</template>

<style scoped>
.fl-light-trail { position: fixed; inset: 0; pointer-events: none; z-index: 49; }

.fl-trail-dot {
    position: fixed;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,213,128,0.3) 0%, transparent 70%);
    transform: translate(-50%, -50%);
    pointer-events: none;
}

@media (prefers-reduced-motion: reduce) {
    .fl-trail-dot { display: none; }
}
</style>
