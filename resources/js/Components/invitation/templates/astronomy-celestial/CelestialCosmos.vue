<script setup>
import { onMounted } from 'vue'
import { formatLatLabel, formatLngLabel, STAR_MAP_PLACE } from './constants.js'

const props = defineProps({
    autoSkip: { type: Boolean, default: false },
})
const emit = defineEmits(['enter'])

onMounted(() => {
    if (props.autoSkip) emit('enter')
})
</script>

<template>
    <div class="ac-cosmos">
        <div class="ac-cosmos-layer ac-cosmos-galaxy"   data-depth="1"/>
        <div class="ac-cosmos-layer ac-cosmos-stars"    data-depth="2"/>
        <div class="ac-cosmos-layer ac-cosmos-earth"    data-depth="3"/>

        <div class="ac-cosmos-content">
            <p class="ac-cosmos-eyebrow">A CELESTIAL MOMENT</p>
            <button class="ac-cta" type="button" @click="emit('enter')">
                <span>OPEN THE SKY</span>
                <span aria-hidden="true">→</span>
            </button>
            <p class="ac-cosmos-coords">{{ formatLatLabel() }} · {{ formatLngLabel() }} · {{ STAR_MAP_PLACE }}</p>
        </div>
    </div>
</template>

<style scoped>
.ac-cosmos {
    position: fixed; inset: 0; z-index: 40;
    background: #000;
    overflow: hidden;
    color: #e8e3d3;
}
.ac-cosmos-layer {
    position: absolute; inset: 0;
    pointer-events: none;
    transform-origin: center center;
    animation-fill-mode: forwards;
}
.ac-cosmos-galaxy {
    background: url('/images/templates/astronomy-celestial/galaxy.webp') center/cover no-repeat, #02060c;
    opacity: 0.5;
    animation: ac-cosmos-1 2.4s ease-in-out;
}
.ac-cosmos-stars {
    background:
        radial-gradient(1px 1px at 20% 30%, #fff 50%, transparent 60%),
        radial-gradient(1px 1px at 70% 80%, #fff 50%, transparent 60%),
        radial-gradient(1.5px 1.5px at 40% 60%, #fff 50%, transparent 60%),
        radial-gradient(1px 1px at 85% 25%, #fff 50%, transparent 60%),
        radial-gradient(2px 2px at 55% 40%, #fff 50%, transparent 60%),
        radial-gradient(1px 1px at 10% 70%, #fff 50%, transparent 60%);
    background-size: 400px 400px;
    opacity: 0.7;
    animation: ac-cosmos-2 2.4s 0.2s ease-in-out;
}
.ac-cosmos-earth {
    background: url('/images/templates/astronomy-celestial/earth-wire.svg') center/520px no-repeat;
    animation: ac-cosmos-3 2.4s 0.4s ease-in-out;
}
@keyframes ac-cosmos-1 {
    from { transform: scale(1);    opacity: 0.5; }
    to   { transform: scale(1.5);  opacity: 0.7; }
}
@keyframes ac-cosmos-2 {
    from { transform: scale(1);    }
    to   { transform: scale(2.5);  }
}
@keyframes ac-cosmos-3 {
    from { transform: scale(1);    opacity: 1; }
    to   { transform: scale(4);    opacity: 0.3; }
}
.ac-cosmos-content {
    position: absolute; inset: 0;
    z-index: 2;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 24px;
    padding: 0 24px;
    text-align: center;
}
.ac-cosmos-eyebrow {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: #e8e3d3;
    letter-spacing: 0.4em;
    font-size: 14px;
    margin: 0;
}
.ac-cta {
    display: inline-flex; align-items: center; gap: 12px;
    padding: 14px 32px;
    background: transparent;
    color: #d4af37;
    border: 1px solid #d4af37;
    border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.3em;
    cursor: pointer;
    text-transform: uppercase;
    transition: color 0.3s ease, background 0.3s ease;
}
.ac-cta:hover { background: #d4af37; color: #0a1929; }
.ac-cosmos-coords {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(232, 227, 211, 0.6);
    font-size: 11px;
    letter-spacing: 0.2em;
    margin: 0;
}
@media (prefers-reduced-motion: reduce) {
    .ac-cosmos-layer { animation: none !important; transform: none !important; opacity: 1; }
    .ac-cta { transition: none; }
}
</style>
