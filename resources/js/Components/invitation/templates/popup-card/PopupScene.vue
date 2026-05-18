<script setup>
import { ref, inject, onMounted, onBeforeUnmount } from 'vue'

defineProps({
    sceneKey:    { type: String, default: '' },
    sceneIndex:  { type: Number, default: 0 },
    totalScenes: { type: Number, default: 1 },
})

const sceneRoot = ref(null)
const depthIntensity = inject('depthIntensity', () => 'medium')

const intensityMap = { subtle: 5, medium: 10, dramatic: 18 }

let isTouch = false
if (typeof window !== 'undefined') {
    isTouch = window.matchMedia('(hover: none)').matches
}

function onMouseMove(e) {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    if (isTouch) return
    if (!sceneRoot.value) return

    const rect = sceneRoot.value.getBoundingClientRect()
    const nx = ((e.clientX - rect.left) / rect.width - 0.5) * 2
    const ny = ((e.clientY - rect.top) / rect.height - 0.5) * 2

    const intensityVal = typeof depthIntensity === 'function'
        ? depthIntensity()
        : (depthIntensity?.value ?? depthIntensity ?? 'medium')
    const maxShift = intensityMap[intensityVal] ?? 10

    sceneRoot.value.querySelectorAll('.pc-layer').forEach((el) => {
        const depth = parseInt(el.dataset.depth || '0', 10)
        const factor = depth / 4
        const tx = -nx * maxShift * factor
        const ty = -ny * maxShift * factor
        el.style.setProperty('--pc-parallax-x', `${tx}px`)
        el.style.setProperty('--pc-parallax-y', `${ty}px`)
    })
}

function resetParallax() {
    if (!sceneRoot.value) return
    sceneRoot.value.querySelectorAll('.pc-layer').forEach((el) => {
        el.style.setProperty('--pc-parallax-x', '0px')
        el.style.setProperty('--pc-parallax-y', '0px')
    })
}

onMounted(() => {
    if (isTouch) return
    sceneRoot.value?.addEventListener('mousemove', onMouseMove)
    sceneRoot.value?.addEventListener('mouseleave', resetParallax)
})

onBeforeUnmount(() => {
    sceneRoot.value?.removeEventListener('mousemove', onMouseMove)
    sceneRoot.value?.removeEventListener('mouseleave', resetParallax)
})
</script>

<template>
    <section
        ref="sceneRoot"
        class="pc-scene"
        :data-scene-key="sceneKey"
        :data-scene-index="sceneIndex"
    >
        <div class="pc-scene-stage">
            <slot/>
        </div>
    </section>
</template>

<style scoped>
.pc-scene {
    position: relative;
    width: 100%;
    max-width: 600px;
    min-height: 560px;
    margin: 0 auto;
    padding: 32px 20px;
    perspective: 1200px;
    transform-style: preserve-3d;
}
@media (min-width: 768px) {
    .pc-scene { padding: 56px 40px; min-height: 640px; }
}
.pc-scene-stage {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 480px;
    transform-style: preserve-3d;
}
@media (hover: none) {
    .pc-scene { perspective: 1000px; }
}
</style>
