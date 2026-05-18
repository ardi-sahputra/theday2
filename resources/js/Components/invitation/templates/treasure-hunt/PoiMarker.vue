<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <button class="th-poi" :class="{ 'th-poi--visited': visited, 'th-poi--rippling': rippling }"
            :style="{ left: `${x}%`, top: `${y}%` }"
            :aria-label="`${roman}. ${name}`" type="button" @click="onTap">
        <span v-if="zoom > 0.8" class="th-poi__name">{{ name }}</span>
        <svg class="th-poi__x" viewBox="0 0 64 64" aria-hidden="true">
            <line x1="14" y1="14" x2="50" y2="50" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
            <line x1="50" y1="14" x2="14" y2="50" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
            <circle v-if="variant === 3" cx="32" cy="32" r="4" fill="currentColor"/>
        </svg>
        <span class="th-poi__numeral">{{ roman }}</span>
        <svg v-if="visited" class="th-poi__check" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M2 8 L7 12 L14 4" stroke="#C9A961" stroke-width="2.5" fill="none" stroke-linecap="round"/>
        </svg>
    </button>
</template>

<script setup>
import { ref } from 'vue'
defineProps({
    roman:   { type: String, default: '' },
    name:    { type: String, default: '' },
    x:       { type: Number, default: 50 },
    y:       { type: Number, default: 50 },
    visited: { type: Boolean, default: false },
    zoom:    { type: Number, default: 1 },
    variant: { type: Number, default: 0 },
})
const emit = defineEmits(['tap'])
const rippling = ref(false)
function onTap() {
    rippling.value = true
    setTimeout(() => { rippling.value = false }, 600)
    emit('tap')
}
</script>

<style scoped>
.th-poi {
    position: absolute; transform: translate(-50%, -50%);
    width: 56px; height: 56px; padding: 0; border: 0;
    background: transparent; color: var(--th-blood-red, #8B1A1F);
    cursor: pointer; animation: th-poi-pulse 2s ease-in-out infinite;
    transform-origin: center center; z-index: 12;
}
.th-poi:focus-visible { outline: 2px solid var(--th-gold-flourish, #C9A961); outline-offset: 4px; }
.th-poi__x { width: 40px; height: 40px; display: block; margin: 0 auto;
    filter: drop-shadow(0 1px 0 rgba(80,50,20,0.25)); }
@media (min-width: 768px) { .th-poi__x { width: 52px; height: 52px; } }
.th-poi__numeral {
    position: absolute; top: 100%; left: 50%; transform: translate(-50%, 4px);
    background: rgba(232,213,160,0.78); color: var(--th-ink, #3D2817);
    font-family: 'Cinzel', serif; font-size: 11px; line-height: 1;
    padding: 2px 6px; border-radius: 2px; border: 1px solid rgba(168,138,79,0.6); white-space: nowrap;
}
.th-poi__name {
    position: absolute; bottom: 100%; left: 50%; transform: translate(-50%, -4px);
    color: var(--th-ink, #3D2817); font-family: 'IM Fell English', serif; font-style: italic;
    font-size: 13px; background: rgba(242,226,181,0.7); padding: 2px 6px;
    white-space: nowrap; pointer-events: none;
}
.th-poi__check { position: absolute; top: -2px; right: -2px; width: 14px; height: 14px; }
.th-poi--visited { animation-duration: 5s; animation-iteration-count: 3; }
.th-poi:hover, .th-poi:focus-visible { animation-play-state: paused; }
.th-poi:hover .th-poi__x, .th-poi:focus-visible .th-poi__x {
    transform: scale(1.15); transition: transform 0.2s ease;
}
@keyframes th-poi-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1);    opacity: 1; }
    50%      { transform: translate(-50%, -50%) scale(1.15); opacity: 0.7; }
}
.th-poi::after {
    content: ''; position: absolute; inset: 8px; border-radius: 50%;
    background: radial-gradient(circle, rgba(139,26,31,0.5) 0%, transparent 70%);
    transform: scale(0); opacity: 0; pointer-events: none;
}
.th-poi--rippling::after { animation: th-poi-ripple 0.6s ease-out forwards; }
@keyframes th-poi-ripple {
    0%   { transform: scale(0.5); opacity: 0.8; }
    100% { transform: scale(3);   opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .th-poi { animation: none; }
    .th-poi--rippling::after { animation: none; }
}
</style>
