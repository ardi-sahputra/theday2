<script setup>
import { computed } from 'vue'

const props = defineProps({
    intensity: { type: String,  default: 'medium' },  // subtle | medium | legendary
    legendary: { type: Boolean, default: false },     // Card VI + XII — extra rainbow + sparkles
})

const opacityValue = computed(() => ({
    subtle:    0.35,
    medium:    0.55,
    legendary: 0.85,
}[props.intensity] ?? 0.55))

const sparkles = computed(() => {
    if (!props.legendary) return []
    return Array.from({ length: 6 }, (_, i) => ({
        key:   i,
        x:     Math.round(10 + Math.random() * 80) + '%',
        y:     Math.round(10 + Math.random() * 80) + '%',
        dur:   (2.4 + Math.random() * 2.6).toFixed(2) + 's',
        delay: (Math.random() * 2).toFixed(2) + 's',
    }))
})
</script>

<template>
    <span class="tr-foil-wrap" aria-hidden="true">
        <span class="tr-foil" :style="{ '--tr-holo-opacity': opacityValue }"/>
        <span v-if="legendary" class="tr-foil tr-foil--rainbow"/>
        <img
            v-for="s in sparkles"
            :key="s.key"
            src="/images/templates/tarot-reading/star-sparkle.svg"
            class="tr-foil-sparkle"
            :style="{
                '--sp-x':     s.x,
                '--sp-y':     s.y,
                '--sp-dur':   s.dur,
                '--sp-delay': s.delay,
            }"
            alt=""
        />
    </span>
</template>

<style scoped>
.tr-foil-wrap {
    position: absolute;
    inset: 0;
    pointer-events: none;
    border-radius: inherit;
    overflow: hidden;
}
.tr-foil {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: linear-gradient(110deg,
        transparent 0%,
        rgba(103,232,249,0.45) 20%,
        rgba(255,107,214,0.45) 40%,
        rgba(255,230,107,0.45) 60%,
        rgba(103,232,249,0.45) 80%,
        transparent 100%);
    background-size: 200% 200%;
    background-position: 0% 0%;
    mix-blend-mode: overlay;
    opacity: var(--tr-holo-opacity, 0.55);
    animation: tr-foil-sweep 5s linear infinite;
    border-radius: inherit;
}
.tr-foil--rainbow {
    background-image: linear-gradient(135deg,
        rgba(255,107,214,0.35) 0%,
        rgba(103,232,249,0.35) 25%,
        rgba(255,230,107,0.35) 50%,
        rgba(139,92,246,0.35) 75%,
        rgba(255,107,214,0.35) 100%);
    mix-blend-mode: screen;
    animation: tr-foil-sweep 7s linear infinite reverse;
    opacity: 0.7;
}
@keyframes tr-foil-sweep {
    0%   { background-position: 0% 0%; }
    100% { background-position: 200% 200%; }
}
.tr-foil-sparkle {
    position: absolute;
    width: 14px; height: 14px;
    pointer-events: none;
    opacity: 0;
    top:  var(--sp-y, 50%);
    left: var(--sp-x, 50%);
    animation: tr-sparkle-twinkle var(--sp-dur, 3s) ease-in-out infinite;
    animation-delay: var(--sp-delay, 0s);
}
@keyframes tr-sparkle-twinkle {
    0%, 100% { opacity: 0; transform: scale(0.6); }
    50%      { opacity: 1; transform: scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .tr-foil { animation: none; background-position: 50% 50%; opacity: 0.25 !important; }
    .tr-foil--rainbow { display: none; }
    .tr-foil-sparkle { display: none; }
}
</style>
