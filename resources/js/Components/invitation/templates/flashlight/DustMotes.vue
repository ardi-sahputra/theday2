<script setup>
import { computed } from 'vue'

const props = defineProps({
    enabled: { type: Boolean, default: true },
    count:   { type: Number,  default: 14 },
})

function rand(min, max) { return min + Math.random() * (max - min) }

const motes = computed(() => {
    if (!props.enabled) return []
    return Array.from({ length: props.count }, () => ({
        left:     rand(0, 100),
        top:      rand(0, 100),
        delay:    rand(0, 4),
        duration: rand(4, 8),
        amp:      rand(6, 14),
    }))
})
</script>

<template>
    <div v-if="enabled" class="fl-dust-motes" aria-hidden="true">
        <img
            v-for="(m, i) in motes"
            :key="i"
            class="fl-dust-mote"
            src="/images/templates/flashlight/dust-mote.svg"
            alt=""
            :style="{
                left:              m.left + '%',
                top:               m.top + '%',
                animationDelay:    m.delay + 's',
                animationDuration: m.duration + 's',
                '--fl-mote-amp':   m.amp + 'px',
            }"
        />
    </div>
</template>

<style scoped>
.fl-dust-motes { position: absolute; inset: 0; pointer-events: none; z-index: 4; }

.fl-dust-mote {
    position: absolute;
    width: 6px; height: 6px;
    opacity: 0.5;
    animation: fl-dust-float ease-in-out infinite;
    pointer-events: none;
    transform: translate3d(0, 0, 0);
    will-change: transform, opacity;
}

@keyframes fl-dust-float {
    0%   { transform: translate(0, 0) scale(0.8); opacity: 0.3; }
    50%  { transform: translate(var(--fl-mote-amp, 8px), -5px) scale(1); opacity: 0.8; }
    100% { transform: translate(calc(var(--fl-mote-amp, 8px) * -0.7), -10px) scale(0.7); opacity: 0.3; }
}

@media (prefers-reduced-motion: reduce) {
    .fl-dust-mote { animation: none; opacity: 0.5; }
}
</style>
