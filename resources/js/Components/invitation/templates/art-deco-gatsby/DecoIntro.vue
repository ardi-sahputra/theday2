<script setup>
import { onMounted, computed } from 'vue'
import DecoSunburst from './DecoSunburst.vue'

const props = defineProps({
    monogram: { type: String, default: 'A·B' },
    rays:     { type: Number, default: 24 },
    year:     { type: [String, Number], default: '' },
})
const emit = defineEmits(['done'])

const letters = computed(() => {
    const chars = String(props.monogram).split('')
    return chars.length ? chars : ['A', '·', 'B']
})

onMounted(() => {
    setTimeout(() => emit('done'), 2600)
})
</script>

<template>
    <div class="deco-intro" role="img" aria-label="Pembuka undangan">
        <div class="deco-intro-sunburst">
            <DecoSunburst :rays="rays" :size="120"/>
        </div>
        <div class="deco-intro-monogram">
            <span
                v-for="(ch, i) in letters"
                :key="i"
                class="deco-monogram-letter"
                :style="{ '--letter-index': i }"
            >{{ ch }}</span>
        </div>
        <p v-if="year" class="deco-intro-est">EST. {{ year }}</p>
    </div>
</template>

<style scoped>
.deco-intro {
    position: fixed; inset: 0; z-index: 50;
    background: radial-gradient(circle at center, #1a1a1a 0%, #0d0d0d 70%);
    color: #c9a961;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    overflow: hidden;
}
.deco-intro-sunburst {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    opacity: 0.8;
}
.deco-intro-monogram {
    position: relative; z-index: 2;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 80px; letter-spacing: 0.05em;
    display: flex; gap: 8px;
}
.deco-monogram-letter {
    display: inline-block;
    opacity: 0;
    transform: rotateY(90deg);
    animation: deco-letter-rotate 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: calc(1.0s + var(--letter-index) * 0.2s);
}
@keyframes deco-letter-rotate {
    to { opacity: 1; transform: rotateY(0); }
}
.deco-intro-est {
    position: relative; z-index: 2;
    margin-top: 24px;
    font-family: 'Lato', system-ui, sans-serif;
    font-size: 13px; letter-spacing: 0.4em;
    color: rgba(244, 234, 213, 0.65);
    opacity: 0;
    transform: translateY(8px);
    animation: deco-est-fade 0.4s ease-out 1.6s forwards;
}
@keyframes deco-est-fade {
    to { opacity: 1; transform: translateY(0); }
}
@media (prefers-reduced-motion: reduce) {
    .deco-monogram-letter,
    .deco-intro-est { animation: none !important; opacity: 1 !important; transform: none !important; }
}
</style>
