<script setup>
import { ref } from 'vue'
import VelvetSeal from './VelvetSeal.vue'

const props = defineProps({
    guestName: { type: String, default: 'Tamu Undangan' },
    monogram:  { type: String, default: 'B & G' },
    motif:     { type: String, default: 'rose' },
    density:   { type: String, default: 'medium' },
})

const emit = defineEmits(['proceed'])

const sealState = ref('intact')

function onCrack() {
    if (sealState.value !== 'intact') return
    sealState.value = 'cracking'

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const delay = prefersReduced ? 0 : 1200

    setTimeout(() => {
        sealState.value = 'cracked'
        emit('proceed')
    }, delay)
}
</script>

<template>
    <div class="vb-env-root">
        <div class="vb-env-grain"/>
        <div class="vb-env-paper">
            <p class="vb-env-prefix">Undangan untuk:</p>
            <p class="vb-env-guest">{{ guestName }}</p>
            <p class="vb-env-monogram">{{ monogram }}</p>
            <div class="vb-env-seal-wrap">
                <VelvetSeal
                    :state="sealState"
                    :motif="motif"
                    :monogram="monogram"
                    :size="120"
                    @crack="onCrack"
                />
            </div>
            <p class="vb-env-hint">Tekan segel untuk membuka</p>
        </div>
    </div>
</template>

<style scoped>
.vb-env-root {
    position: fixed;
    inset: 0;
    z-index: 60;
    background: var(--vb-burgundy-deep, #3a0c0e);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    overflow: hidden;
}

.vb-env-grain {
    position: absolute;
    inset: 0;
    background-image: url('/images/templates/velvet-burgundy/velvet-grain.svg');
    background-repeat: repeat;
    opacity: 0.15;
    animation: vb-grain-shimmer 8s linear infinite;
    pointer-events: none;
}

.vb-env-paper {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 360px;
    aspect-ratio: 3 / 4;
    background: var(--vb-cream, #f8f1e7);
    background-image: url('/images/templates/velvet-burgundy/paper-cream.svg');
    background-size: cover;
    box-shadow: 0 18px 60px var(--vb-shadow, #2d0507);
    border: 1px solid rgba(168,122,74,0.25);
    border-radius: 4px;
    padding: 36px 24px 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
}

.vb-env-prefix {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 4px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0;
    text-transform: uppercase;
}

.vb-env-guest {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 22px;
    color: var(--vb-burgundy-deep, #3a0c0e);
    margin: 0;
}

.vb-env-monogram {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 56px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 8px 0 4px;
    line-height: 1;
    text-shadow: 0 2px 8px rgba(212,165,116,0.25);
}

.vb-env-seal-wrap {
    margin-top: auto;
    display: flex;
    justify-content: center;
}

.vb-env-hint {
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 2px;
    color: var(--vb-gold-antique, #a87a4a);
    text-transform: uppercase;
    margin: 4px 0 0;
}

@keyframes vb-grain-shimmer {
    0%   { background-position: 0 0; }
    100% { background-position: 200px 200px; }
}
@media (prefers-reduced-motion: reduce) {
    .vb-env-grain { animation: none; }
}
</style>
