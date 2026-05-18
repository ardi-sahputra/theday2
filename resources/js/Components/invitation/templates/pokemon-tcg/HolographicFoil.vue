<script setup>
defineProps({
    intensity: { type: Number, default: 0.55 }, // 0..1
})
</script>

<template>
    <span class="tcg-holo" :style="{ opacity: intensity }" aria-hidden="true"/>
</template>

<style scoped>
.tcg-holo {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(110deg,
        transparent 25%,
        var(--tcg-holo-c1, #7CF7FF) 42%,
        var(--tcg-holo-c2, #FF6BD6) 50%,
        var(--tcg-holo-c3, #FFE66B) 58%,
        transparent 75%);
    background-size: 220% 100%;
    background-position: 200% 0;
    mix-blend-mode: overlay;
    animation: tcg-shimmer 6s linear infinite;
    border-radius: inherit;
}
@keyframes tcg-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -100% 0; }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-holo { animation: none; background-position: 50% 0; opacity: 0.3 !important; }
}
</style>
