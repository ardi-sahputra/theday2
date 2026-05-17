<script setup>
import { computed } from 'vue'

const props = defineProps({
    count: { type: Number, default: 5 },     // 0 | 3 | 5 | 8
})

const safeCount = computed(() => {
    const allowed = [0, 3, 5, 8]
    if (!allowed.includes(props.count)) {
        if (props.count <= 0) return 0
        if (props.count <= 3) return 3
        if (props.count <= 5) return 5
        return 8
    }
    return props.count
})

// Fixed delay schedule per spec (Section 10.3)
const delays = ['0s', '1.8s', '3.5s', '5.2s', '7s', '8.5s', '10s', '12s']
// Horizontal positions distributed across viewport
const positions = ['10%', '25%', '40%', '55%', '70%', '85%', '15%', '60%']

const petals = computed(() =>
    Array.from({ length: safeCount.value }, (_, i) => ({
        variant: (i % 5) + 1,
        left:    positions[i % positions.length],
        delay:   delays[i % delays.length],
    }))
)
</script>

<template>
    <div v-if="safeCount > 0" class="ryokan-petals" aria-hidden="true">
        <img
            v-for="(p, i) in petals"
            :key="i"
            :src="`/images/templates/japanese-ryokan/petal-${p.variant}.svg`"
            class="ryokan-petal"
            :style="{ left: p.left, animationDelay: p.delay }"
            alt=""
            draggable="false"
        />
    </div>
</template>

<style scoped>
.ryokan-petals {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}
.ryokan-petal {
    position: fixed;
    top: -40px;
    width: 32px;
    height: 32px;
    pointer-events: none;
    will-change: transform;
    animation:
        ryokan-petal-fall 14s ease-in-out infinite,
        ryokan-petal-sway  4s ease-in-out infinite alternate,
        ryokan-petal-spin  8s linear        infinite;
}
@keyframes ryokan-petal-fall {
    0%   { transform: translateY(-40px); }
    100% { transform: translateY(110vh); }
}
@keyframes ryokan-petal-sway {
    0%   { margin-left: -25px; }
    100% { margin-left:  25px; }
}
@keyframes ryokan-petal-spin {
    0%   { rotate: 0deg; }
    100% { rotate: 540deg; }
}
@media (prefers-reduced-motion: reduce) {
    /* CRITICAL: petals must NOT render at all for users with reduced-motion
       preference (vertigo / motion-sickness trigger). */
    .ryokan-petal { display: none; }
}
</style>
