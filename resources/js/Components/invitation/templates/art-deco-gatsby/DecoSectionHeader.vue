<script setup>
defineProps({
    title:           { type: String, required: true },
    chevronDensity:  { type: String, default: 'medium' }, // subtle | medium | bold
})

const fanArcs = [
    'M100 70 A 18 18 0 0 1 100 34',
    'M100 70 A 28 28 0 0 1 88 16',
    'M100 70 A 28 28 0 0 1 112 16',
    'M100 70 A 40 40 0 0 1 72 12',
    'M100 70 A 40 40 0 0 1 128 12',
    'M100 70 A 52 52 0 0 1 56 16',
    'M100 70 A 52 52 0 0 1 144 16',
]
</script>

<template>
    <header class="deco-section-header">
        <div class="deco-chevron-row" :class="`deco-chev-${chevronDensity}`">
            <span class="deco-chevron-half deco-chevron-half--left"/>
            <span class="deco-chevron-half deco-chevron-half--right"/>
        </div>
        <h2 class="deco-section-title">{{ title }}</h2>
        <span class="deco-gold-line"/>
        <svg class="deco-fan-divider" viewBox="0 0 200 80" fill="none" aria-hidden="true">
            <path
                v-for="(d, i) in fanArcs"
                :key="i"
                :d="d"
                stroke="currentColor"
                stroke-width="1.5"
                class="deco-fan-arc"
            />
        </svg>
    </header>
</template>

<style scoped>
.deco-section-header {
    display: flex; flex-direction: column; align-items: center;
    gap: 14px; margin-bottom: 28px; color: var(--deco-gold, #c9a961);
    overflow: hidden;
}
.deco-chevron-row {
    position: relative; width: 100%; max-width: 360px;
    height: 16px; overflow: hidden;
}
.deco-chevron-half {
    position: absolute; top: 0; bottom: 0; width: 50%;
    background-repeat: repeat-x;
    background-image: repeating-linear-gradient(135deg, currentColor 0 2px, transparent 2px 12px);
    transition: transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
}
.deco-chevron-half--left  { left: 0;  transform: translateX(-100%); }
.deco-chevron-half--right { right: 0; transform: translateX(100%); }
.deco-visible .deco-chevron-half--left,
.deco-visible .deco-chevron-half--right { transform: translateX(0); }
.deco-chev-subtle .deco-chevron-half { background-size: 8px 16px; }
.deco-chev-medium .deco-chevron-half { background-size: 16px 16px; }
.deco-chev-bold   .deco-chevron-half {
    background-size: 24px 16px;
    background-image: repeating-linear-gradient(135deg, currentColor 0 2.5px, transparent 2.5px 16px);
}
.deco-section-title {
    margin: 0; font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 500;
    font-size: 22px; letter-spacing: 0.32em; color: currentColor;
    text-align: center;
}
.deco-gold-line {
    display: inline-block; height: 1.5px; background: currentColor;
    width: 0;
    transition: width 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.3s;
}
.deco-visible .deco-gold-line { width: 60px; }
.deco-fan-divider { width: 120px; height: 48px; }
.deco-fan-arc {
    stroke-dasharray: 120;
    stroke-dashoffset: 120;
}
.deco-visible .deco-fan-arc { animation: deco-fan-draw 0.5s ease-out forwards; }
.deco-visible .deco-fan-arc:nth-child(1) { animation-delay: 0.45s; }
.deco-visible .deco-fan-arc:nth-child(2) { animation-delay: 0.30s; }
.deco-visible .deco-fan-arc:nth-child(3) { animation-delay: 0.30s; }
.deco-visible .deco-fan-arc:nth-child(4) { animation-delay: 0.00s; }
.deco-visible .deco-fan-arc:nth-child(5) { animation-delay: 0.15s; }
.deco-visible .deco-fan-arc:nth-child(6) { animation-delay: 0.15s; }
.deco-visible .deco-fan-arc:nth-child(7) { animation-delay: 0.45s; }
@keyframes deco-fan-draw { to { stroke-dashoffset: 0; } }
@media (prefers-reduced-motion: reduce) {
    .deco-chevron-half--left, .deco-chevron-half--right { transition: none; transform: translateX(0); }
    .deco-fan-arc { animation: none !important; stroke-dashoffset: 0 !important; }
    .deco-gold-line { transition: none; width: 60px !important; }
}
</style>
