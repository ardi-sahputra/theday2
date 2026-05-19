<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    show:      { type: Boolean, default: false },
    playSound: { type: Boolean, default: true },
})

const active = ref(false)

watch(() => props.show, (val) => {
    if (!val) { active.value = false; return }
    active.value = true
    if (props.playSound) {
        try {
            const reduced = typeof window !== 'undefined'
                && window.matchMedia('(prefers-reduced-motion: reduce)').matches
            const audio = new Audio('/images/templates/tuscany-vineyard/cheers.mp3')
            audio.volume = 0.6
            // Defer ~400ms to align with the clink (phase 2) frame
            setTimeout(() => audio.play().catch(() => {}), reduced ? 0 : 400)
        } catch (_) { /* ignore */ }
    }
})

// 8 sparkles spread radially around the clink point
const sparkles = Array.from({ length: 8 }, (_, i) => {
    const angle = (i / 8) * Math.PI * 2
    const dist  = 40
    return {
        x: Math.round(Math.cos(angle) * dist),
        y: Math.round(Math.sin(angle) * dist),
        delay: 0.55,
    }
})
</script>

<template>
    <div class="tv-cheers" :class="{ 'tv-cheers--active': active }" aria-hidden="true">
        <svg viewBox="0 0 240 200" class="tv-cheers-svg">
            <use class="tv-glass tv-glass--left"  href="/images/templates/tuscany-vineyard/wine-glasses.svg#glass-left"/>
            <use class="tv-glass tv-glass--right" href="/images/templates/tuscany-vineyard/wine-glasses.svg#glass-right"/>
        </svg>
        <span
            v-for="(s, i) in sparkles"
            :key="i"
            class="tv-sparkle"
            :style="{ '--sx': s.x + 'px', '--sy': s.y + 'px', animationDelay: s.delay + 's' }"
        >
            <img src="/images/templates/tuscany-vineyard/sparkle.svg" alt="" draggable="false"/>
        </span>
    </div>
</template>

<style scoped>
.tv-cheers {
    position: relative;
    width: 240px; height: 200px;
    margin: 24px auto 0;
    pointer-events: none;
}
.tv-cheers-svg {
    width: 100%; height: 100%;
    display: block;
}
.tv-glass {
    opacity: 0;
    transform-origin: bottom center;
}

/* Phase 1 (0-0.4s): tilt-in. Phase 2 (0.4-0.55s): clink + scale pulse. Phase 3 (0.55-1.2s): recoil. */
@keyframes tv-glass-left {
    0%   { transform: translateX(-80px) rotate(25deg);                opacity: 0; }
    33%  { transform: translateX(  0px) rotate( 8deg);                opacity: 1; }
    46%  { transform: translateX(  4px) rotate( 4deg) scale(1.06);    opacity: 1; }
    100% { transform: translateX(  0px) rotate( 6deg) scale(1);       opacity: 1; }
}
@keyframes tv-glass-right {
    0%   { transform: translateX( 80px) rotate(-25deg);               opacity: 0; }
    33%  { transform: translateX(  0px) rotate(-8deg);                opacity: 1; }
    46%  { transform: translateX( -4px) rotate(-4deg) scale(1.06);    opacity: 1; }
    100% { transform: translateX(  0px) rotate(-6deg) scale(1);       opacity: 1; }
}
.tv-cheers--active .tv-glass--left  { animation: tv-glass-left  1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
.tv-cheers--active .tv-glass--right { animation: tv-glass-right 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

/* Sparkles */
.tv-sparkle {
    position: absolute;
    top: 50%; left: 50%;
    width: 14px; height: 14px;
    margin-left: -7px; margin-top: -7px;
    opacity: 0;
}
.tv-sparkle img { width: 100%; height: 100%; display: block; }
@keyframes tv-sparkle-burst {
    0%   { opacity: 1; transform: translate(0, 0) scale(1); }
    100% { opacity: 0; transform: translate(var(--sx), var(--sy)) scale(0.3); }
}
.tv-cheers--active .tv-sparkle {
    animation: tv-sparkle-burst 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@media (prefers-reduced-motion: reduce) {
    .tv-glass { opacity: 1; transform: none; animation: none !important; }
    .tv-sparkle { animation: none !important; opacity: 0; }
}
</style>
