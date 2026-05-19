<script setup>
import { computed } from 'vue'
import { GRAIN_OPACITY } from './track-config.js'

const props = defineProps({
    intensity: { type: String, default: 'subtle' }, // subtle|medium|strong
})
const opacityVal = computed(() => GRAIN_OPACITY[props.intensity] ?? GRAIN_OPACITY.subtle)
</script>

<template>
    <div class="vr-grain-layer" aria-hidden="true" :style="{ opacity: opacityVal }">
        <div class="vr-grain"/>
        <div class="vr-scratch"/>
    </div>
</template>

<style scoped>
.vr-grain-layer {
    position: fixed; inset: 0;
    pointer-events: none;
    z-index: 1;
}
.vr-grain {
    position: absolute; inset: 0;
    background: url('/images/templates/vinyl-record/grain.svg') repeat;
    background-size: 256px 256px;
    animation: vr-grain-shift 12s ease-in-out infinite alternate;
}
.vr-scratch {
    position: absolute; inset: 0;
    background: repeating-linear-gradient(
        110deg,
        transparent 0 80px,
        rgba(245,230,204,0.02) 80px 81px
    );
}
@keyframes vr-grain-shift {
    from { background-position: 0 0; }
    to   { background-position: 0 4px; }
}
@media (prefers-reduced-motion: reduce) {
    .vr-grain { animation: none; }
}
</style>
