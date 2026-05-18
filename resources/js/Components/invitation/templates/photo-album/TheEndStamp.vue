<script setup>
import { computed, ref, onMounted } from 'vue'

const props = defineProps({
    text:  { type: String, default: 'The End' },
    color: { type: String, default: '#7a3838' },     // sepia/red ink
    size:  { type: Number, default: 280 },
})

const visible = ref(false)
const root = ref(null)

const stampStyle = computed(() => ({
    width: `${props.size}px`,
    '--pa-end-color': props.color,
}))

onMounted(() => {
    if (typeof window === 'undefined') return
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (!('IntersectionObserver' in window) || reduced) { visible.value = true; return }
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { visible.value = true; io.unobserve(e.target) }
        })
    }, { threshold: 0.4 })
    if (root.value) io.observe(root.value)
})
</script>

<template>
    <span
        ref="root"
        class="pa-the-end-stamp"
        :class="{ 'pa-visible': visible }"
        :style="stampStyle"
        role="img"
        :aria-label="text"
    >
        <svg viewBox="0 0 320 140" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <g fill="none" stroke="currentColor" stroke-width="3">
                <rect x="6" y="6" width="308" height="128" rx="4"/>
                <rect x="14" y="14" width="292" height="112" rx="3" stroke-width="1.5" opacity="0.6"/>
            </g>
            <text x="160" y="86" text-anchor="middle" fill="currentColor"
                  font-family="Cormorant SC, serif" font-size="44" font-weight="600" letter-spacing="6">
                {{ text.toUpperCase() }}
            </text>
        </svg>
    </span>
</template>

<style scoped>
.pa-the-end-stamp {
    display: inline-block;
    color: var(--pa-end-color, #7a3838);
    opacity: 0;
    transform: scale(1.8) rotate(0deg);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.45));
}
.pa-the-end-stamp svg {
    display: block;
    width: 100%;
    height: auto;
}
@keyframes pa-the-end-slam {
    0%   { transform: scale(1.8) rotate(0deg);   opacity: 0; }
    70%  { transform: scale(0.96) rotate(-4deg); opacity: 1; }
    100% { transform: scale(1)    rotate(-4deg); opacity: 1; }
}
.pa-the-end-stamp.pa-visible {
    animation: pa-the-end-slam 0.5s cubic-bezier(0.5, 1.6, 0.5, 1) forwards;
}
@media (prefers-reduced-motion: reduce) {
    .pa-the-end-stamp,
    .pa-the-end-stamp.pa-visible {
        animation: none !important;
        opacity: 1;
        transform: rotate(-4deg);
    }
}
</style>
