<script setup>
import { computed } from 'vue'

const props = defineProps({
    count:   { type: Number,  default: 6 },
    enabled: { type: Boolean, default: true },
})

const particles = computed(() => {
    if (!props.enabled) return []
    return Array.from({ length: Math.min(8, Math.max(1, props.count)) }, (_, i) => ({
        key:   i,
        x:     Math.round(5 + Math.random() * 90) + '%',
        y:     Math.round(20 + Math.random() * 70) + '%',
        dur:   (3.5 + Math.random() * 2.5).toFixed(2) + 's',
        delay: (Math.random() * 3).toFixed(2) + 's',
        scale: (0.6 + Math.random() * 0.8).toFixed(2),
    }))
})
</script>

<template>
    <div v-if="enabled" class="tr-aura" aria-hidden="true">
        <img
            v-for="p in particles"
            :key="p.key"
            src="/images/templates/tarot-reading/dust-particle.svg"
            class="tr-particle"
            :style="{
                '--p-x':     p.x,
                '--p-y':     p.y,
                '--p-dur':   p.dur,
                '--p-delay': p.delay,
                '--p-scale': p.scale,
            }"
            alt=""
        />
    </div>
</template>

<style scoped>
.tr-aura {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
}
.tr-particle {
    position: absolute;
    width: 14px;
    height: 14px;
    pointer-events: none;
    opacity: 0;
    top:  var(--p-y, 50%);
    left: var(--p-x, 50%);
    animation: tr-aura-float var(--p-dur, 4s) ease-in-out infinite;
    animation-delay: var(--p-delay, 0s);
    filter: drop-shadow(0 0 6px rgba(139,92,246,0.5));
    transform: scale(var(--p-scale, 1));
}
@keyframes tr-aura-float {
    0%   { opacity: 0;   transform: translateY(0)     scale(calc(var(--p-scale, 1) * 0.6)); }
    50%  { opacity: 0.6; transform: translateY(-25px) scale(var(--p-scale, 1)); }
    100% { opacity: 0;   transform: translateY(-50px) scale(calc(var(--p-scale, 1) * 0.6)); }
}
@media (prefers-reduced-motion: reduce) {
    .tr-particle { display: none; }
}
</style>
