<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    bars:  { type: Number, default: 5 },
    speed: { type: String, default: 'normal' }, // slow | normal | fast
    color: { type: String, default: 'currentColor' },
    height: { type: Number, default: 32 },
})

const speedSec = computed(() => ({ slow: '1.2s', normal: '0.8s', fast: '0.5s' }[props.speed] ?? '0.8s'))
const barCount = computed(() => Math.max(3, Math.min(7, props.bars)))
</script>

<template>
    <span
        class="sw-eq"
        :style="{
            '--sw-eq-speed': speedSec,
            color: color,
            height: height + 'px',
        }"
        aria-hidden="true"
    >
        <span
            v-for="i in barCount"
            :key="i"
            class="sw-eq-bar"
            :style="{ animationDelay: `-${(i * 0.13).toFixed(2)}s` }"
        />
    </span>
</template>

<style scoped>
.sw-eq {
    display: inline-flex;
    align-items: flex-end;
    gap: 3px;
}
.sw-eq-bar {
    width: 4px;
    height: 100%;
    background: currentColor;
    border-radius: 2px;
    transform-origin: bottom;
    transform: scaleY(0.3);
    animation: sw-eq-dance var(--sw-eq-speed, 0.8s) ease-in-out infinite;
}
@keyframes sw-eq-dance {
    0%, 100% { transform: scaleY(0.3); }
    25%      { transform: scaleY(0.9); }
    50%      { transform: scaleY(0.5); }
    75%      { transform: scaleY(1.0); }
}
@media (prefers-reduced-motion: reduce) {
    .sw-eq-bar { animation: none; transform: scaleY(0.6); }
}
</style>
