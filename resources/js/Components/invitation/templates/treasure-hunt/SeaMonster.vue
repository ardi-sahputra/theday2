<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <img class="th-sea-monster" :class="`th-sea-monster--${variant}`"
         :style="{ left: `${x}%`, top: `${y}%`, width: `${width}px` }"
         :src="`/images/templates/treasure-hunt/sea-${variant}.svg`"
         alt="" aria-hidden="true" draggable="false"/>
</template>

<script setup>
defineProps({
    variant: { type: String, default: 'kraken',
        validator: (v) => ['kraken','mermaid','serpent','whale'].includes(v) },
    x:     { type: Number, default: 0 },
    y:     { type: Number, default: 0 },
    width: { type: Number, default: 240 },
})
</script>

<style scoped>
.th-sea-monster {
    position: absolute; transform-origin: center center; pointer-events: none;
    opacity: 0.7; z-index: 3;
    animation: th-monster-float 6s ease-in-out infinite alternate;
}
.th-sea-monster--kraken  { animation-duration: 7s; }
.th-sea-monster--mermaid { animation-duration: 5.5s; animation-delay: 1s; }
.th-sea-monster--serpent { animation-duration: 6.5s; animation-delay: 0.5s; }
.th-sea-monster--whale   { animation-duration: 8s;   animation-delay: 1.5s; }
@keyframes th-monster-float { 0% { transform: translateY(0px) } 100% { transform: translateY(-5px) } }
@media (prefers-reduced-motion: reduce) { .th-sea-monster { animation: none; } }
</style>
