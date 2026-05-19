<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    trigger: { type: Boolean, default: false },
    count:   { type: Number,  default: 40 },
})

const active = ref(false)

const SHAPES = ['circle', 'square', 'triangle', 'star', 'heart']
const COLORS = ['#d4af37', '#f5b8b8', '#b73e3e', '#8b9d6f']

const particles = computed(() => {
    if (!active.value) return []
    return Array.from({ length: props.count }, (_, i) => {
        const shape = SHAPES[i % SHAPES.length]
        const color = COLORS[i % COLORS.length]
        return {
            id: i,
            shape,
            iconUrl: `/images/templates/popup-card/confetti-${shape}.svg`,
            style: {
                '--pc-tx':     `${(Math.random() - 0.5) * 400}px`,
                '--pc-ty':     `${-Math.random() * 150 - 50}vh`,
                '--pc-rot':    `${(Math.random() - 0.5) * 1440}deg`,
                '--pc-color':  color,
                '--pc-delay':  `${Math.random() * 0.2}s`,
                color: color,
                left: `${50 + (Math.random() - 0.5) * 30}%`,
                top: '60%',
            },
        }
    })
})

watch(() => props.trigger, (v) => {
    if (!v) return
    if (typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return
    }
    active.value = true
    setTimeout(() => { active.value = false }, 2200)
})
</script>

<template>
    <div v-if="active" class="pc-confetti" aria-hidden="true">
        <span
            v-for="p in particles"
            :key="p.id"
            class="pc-confetti-particle"
            :style="p.style"
        >
            <img :src="p.iconUrl" :alt="''" draggable="false"/>
        </span>
    </div>
</template>

<style scoped>
.pc-confetti {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 50;
    overflow: hidden;
}
.pc-confetti-particle {
    position: absolute;
    width: 16px;
    height: 16px;
    color: var(--pc-color, #d4af37);
    transform: translate(0, 0) rotate(0deg);
    opacity: 1;
    animation: pc-confetti-fly 2s ease-out var(--pc-delay, 0s) forwards;
    will-change: transform, opacity;
}
.pc-confetti-particle img {
    width: 100%;
    height: 100%;
    display: block;
}
@keyframes pc-confetti-fly {
    0%   { transform: translate(0, 0) rotate(0); opacity: 1; }
    60%  { opacity: 1; }
    100% { transform: translate(var(--pc-tx), var(--pc-ty)) rotate(var(--pc-rot)); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .pc-confetti { display: none; }
}
</style>
