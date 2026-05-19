<script setup>
// 5 leaves, 4 SVG variants — variant repeats with different timing for #5.
const leaves = [
    { src: '/images/templates/tuscany-vineyard/olive-leaf-1.svg' },
    { src: '/images/templates/tuscany-vineyard/olive-leaf-2.svg' },
    { src: '/images/templates/tuscany-vineyard/olive-leaf-3.svg' },
    { src: '/images/templates/tuscany-vineyard/olive-leaf-4.svg' },
    { src: '/images/templates/tuscany-vineyard/olive-leaf-2.svg' },
]
</script>

<template>
    <div class="tv-leaves" aria-hidden="true">
        <img
            v-for="(leaf, i) in leaves"
            :key="i"
            :src="leaf.src"
            class="tv-leaf"
            alt=""
            draggable="false"
        />
    </div>
</template>

<style scoped>
.tv-leaves {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}
.tv-leaf {
    position: absolute;
    width: 38px; height: auto;
    pointer-events: none;
    opacity: 0;
    animation: tv-leaf-drift 18s linear infinite;
    will-change: transform, opacity;
}
.tv-leaf:nth-child(1) { top:  8%; animation-delay:  0s; }
.tv-leaf:nth-child(2) { top: 28%; animation-delay:  3.5s; }
.tv-leaf:nth-child(3) { top: 48%; animation-delay:  7s; }
.tv-leaf:nth-child(4) { top: 68%; animation-delay: 11s; }
.tv-leaf:nth-child(5) { top: 86%; animation-delay: 14.5s; }

@keyframes tv-leaf-drift {
    0%   { transform: translate3d(-10vw, 0, 0) rotate(0deg);   opacity: 0; }
    8%   { opacity: 0.75; }
    92%  { opacity: 0.75; }
    100% { transform: translate3d(110vw, 20px, 0) rotate(360deg); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .tv-leaf { display: none; }
}
</style>
