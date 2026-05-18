<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: { type: String, default: 'cross' }, // 'cross' | 'fan' | 'symmetric'
})

const paths = computed(() => {
    switch (props.variant) {
        case 'fan':
            return [
                'M 300 800 L 100 0',
                'M 300 800 L 300 0',
                'M 300 800 L 500 0',
                'M 300 800 L 50 200',
                'M 300 800 L 550 200',
            ]
        case 'symmetric':
            return [
                'M 0 400 L 600 400',
                'M 300 0 L 300 800',
                'M 100 0 L 100 800',
                'M 500 0 L 500 800',
            ]
        case 'cross':
        default:
            return [
                'M 0 400 L 600 400',
                'M 300 0 L 300 800',
                'M 0 0 L 600 800',
                'M 600 0 L 0 800',
            ]
    }
})
</script>

<template>
    <svg
        class="pc-fold-lines"
        viewBox="0 0 600 800"
        preserveAspectRatio="none"
        aria-hidden="true"
        focusable="false"
    >
        <path v-for="(d, i) in paths" :key="i" class="pc-fold-line" :d="d"/>
    </svg>
</template>

<style scoped>
.pc-fold-lines {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    opacity: 0.4;
    z-index: 5;
}
.pc-fold-line {
    fill: none;
    stroke: var(--pc-crease, rgba(58, 46, 33, 0.25));
    stroke-width: 1;
    stroke-dasharray: 6 6;
    stroke-dashoffset: 1000;
    animation: pc-crease-draw 0.8s ease-out forwards;
}
@keyframes pc-crease-draw {
    to { stroke-dashoffset: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .pc-fold-line { animation: none; stroke-dashoffset: 0; }
}
</style>
