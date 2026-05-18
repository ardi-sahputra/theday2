<script setup>
import { computed } from 'vue'

const props = defineProps({
    position: { type: String, default: 'top-right' },  // top-right | top-left | bottom-right | bottom-left
})

const positionClass = computed(() => `tr-crystal-ball--${props.position}`)
</script>

<template>
    <img
        src="/images/templates/tarot-reading/crystal-ball.svg"
        class="tr-crystal-ball"
        :class="positionClass"
        alt=""
        aria-hidden="true"
        draggable="false"
    />
</template>

<style scoped>
.tr-crystal-ball {
    position: fixed;
    width: 64px;
    height: 64px;
    z-index: 50;
    pointer-events: none;
    animation:
        tr-crystal-rotate 20s linear infinite,
        tr-crystal-breathe 6s ease-in-out infinite alternate;
    filter: drop-shadow(0 0 12px rgba(139,92,246,0.55));
}
.tr-crystal-ball--top-right    { top: 24px;    right: 24px; }
.tr-crystal-ball--top-left     { top: 24px;    left: 24px; }
.tr-crystal-ball--bottom-right { bottom: 24px; right: 24px; }
.tr-crystal-ball--bottom-left  { bottom: 24px; left: 24px; }

@keyframes tr-crystal-rotate {
    from { transform: rotateY(0deg); }
    to   { transform: rotateY(360deg); }
}
@keyframes tr-crystal-breathe {
    from { filter: drop-shadow(0 0 8px  rgba(139,92,246,0.55)); }
    to   { filter: drop-shadow(0 0 18px rgba(103,232,249,0.65)); }
}
@media (prefers-reduced-motion: reduce) {
    .tr-crystal-ball { animation: none; }
}
@media (max-width: 480px) {
    .tr-crystal-ball { width: 48px; height: 48px; }
    .tr-crystal-ball--top-right    { top: 16px; right: 16px; }
    .tr-crystal-ball--top-left     { top: 16px; left:  16px; }
    .tr-crystal-ball--bottom-right { bottom: 16px; right: 16px; }
    .tr-crystal-ball--bottom-left  { bottom: 16px; left:  16px; }
}
</style>
