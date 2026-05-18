<script setup>
import { computed } from 'vue'

const props = defineProps({
    intensity: { type: String, default: 'medium' },  // subtle|medium|aged
})

const OPACITY = { subtle: 0.04, medium: 0.08, aged: 0.14 }

const dustStyle = computed(() => ({
    '--pa-dust-opacity': String(OPACITY[props.intensity] ?? OPACITY.medium),
}))
</script>

<template>
    <div class="pa-dust-overlay" :style="dustStyle" aria-hidden="true"/>
</template>

<style scoped>
@keyframes pa-dust-drift {
    0%, 100% { background-position: 0 0; }
    50%      { background-position: 0 -8px; }
}
.pa-dust-overlay {
    position: absolute;
    inset: 0;
    background-image: url('/images/templates/photo-album/dust-noise.svg');
    background-size: 400px 400px;
    opacity: var(--pa-dust-opacity, 0.08);
    mix-blend-mode: screen;
    pointer-events: none;
    z-index: 40;
    animation: pa-dust-drift 8s ease-in-out infinite;
}
@media (prefers-reduced-motion: reduce) {
    .pa-dust-overlay { animation: none !important; }
}
</style>
