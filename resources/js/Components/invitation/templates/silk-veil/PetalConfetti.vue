<script setup>
import { computed, watch, ref } from 'vue'

const props = defineProps({
    active: { type: Boolean, default: false },
    count:  { type: Number, default: 40 },
})

const done = ref(false)

const particles = computed(() => {
    if (!props.active || done.value) return []
    return Array.from({ length: props.count }, (_, i) => ({
        id: i,
        type: i % 4 === 0 ? 'pearl' : 'petal',
        left: Math.floor(Math.random() * 100),
        delay: (Math.random() * 1).toFixed(2),
        hue:  Math.floor(Math.random() * 20 - 10),
        size: 24 + Math.floor(Math.random() * 16),
    }))
})

watch(
    () => props.active,
    (val) => {
        if (val) {
            done.value = false
            setTimeout(() => { done.value = true }, 4500)
        }
    },
    { immediate: true }
)
</script>

<template>
    <Teleport to="body">
        <div v-if="active && !done" class="sv-petal-stage" aria-hidden="true">
            <template v-for="p in particles" :key="p.id">
                <!-- Petal silhouette (rose petal) -->
                <svg
                    v-if="p.type === 'petal'"
                    class="sv-petal"
                    :style="{
                        left: p.left + 'vw',
                        '--sv-petal-delay': p.delay + 's',
                        width: p.size + 'px',
                        height: (p.size * 1.25) + 'px',
                        filter: `hue-rotate(${p.hue}deg)`,
                    }"
                    viewBox="0 0 32 40"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M16 2 C24 8 28 20 24 30 C22 36 18 38 16 38 C14 38 10 36 8 30 C4 20 8 8 16 2 Z"
                        fill="#F8E0DC"
                        stroke="#D4A5A5"
                        stroke-width="0.5"
                        stroke-opacity="0.6"
                    />
                </svg>
                <!-- Pearl -->
                <svg
                    v-else
                    class="sv-petal sv-petal--pearl"
                    :style="{
                        left: p.left + 'vw',
                        '--sv-petal-delay': p.delay + 's',
                        width: (p.size * 0.6) + 'px',
                        height: (p.size * 0.6) + 'px',
                    }"
                    viewBox="0 0 16 16"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <radialGradient id="sv-petal-pearl-shine" cx="35%" cy="35%" r="65%">
                            <stop offset="0%"  stop-color="#FFFFFF" stop-opacity="0.95"/>
                            <stop offset="40%" stop-color="#F2E9DC" stop-opacity="1"/>
                            <stop offset="100%" stop-color="#C9C2B3" stop-opacity="0.9"/>
                        </radialGradient>
                    </defs>
                    <circle cx="8" cy="8" r="6.5" fill="url(#sv-petal-pearl-shine)"/>
                </svg>
            </template>
        </div>
    </Teleport>
</template>

<style scoped>
.sv-petal-stage {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 50;
    overflow: hidden;
}

@keyframes sv-petal-fall {
    0%   { transform: translate(0, 0)       rotate(0deg);   opacity: 1; }
    30%  { transform: translate(8vw, 30vh)  rotate(180deg); opacity: 1; }
    60%  { transform: translate(-6vw, 70vh) rotate(360deg); opacity: 0.9; }
    100% { transform: translate(4vw, 130vh) rotate(720deg); opacity: 0; }
}

.sv-petal {
    position: absolute;
    top: -10vh;
    will-change: transform, opacity;
    animation: sv-petal-fall 4s ease-out forwards;
    animation-delay: var(--sv-petal-delay, 0s);
}

@media (prefers-reduced-motion: reduce) {
    .sv-petal { display: none; }
}
</style>
