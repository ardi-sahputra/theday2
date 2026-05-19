<script setup>
defineProps({
    enabled: { type: Boolean, default: true },
})
</script>

<template>
    <div class="sv-veil-fabric" :class="{ 'sv-veil-fabric--paused': !enabled }">
        <slot/>
    </div>
</template>

<style scoped>
@keyframes sv-silk-ripple {
    0%   { transform: translate3d(0, 0, 0) skewY(0deg); }
    33%  { transform: translate3d(1.5px, -1px, 0) skewY(0.4deg); }
    66%  { transform: translate3d(-1px, 1.5px, 0) skewY(-0.3deg); }
    100% { transform: translate3d(0, 0, 0) skewY(0deg); }
}

.sv-veil-fabric {
    position: relative;
    width: 100%;
    height: 100%;
    animation: sv-silk-ripple 6s ease-in-out infinite alternate;
    will-change: transform;
}
.sv-veil-fabric--paused { animation: none; }

@media (prefers-reduced-motion: reduce) {
    .sv-veil-fabric { animation: none; }
}
</style>
