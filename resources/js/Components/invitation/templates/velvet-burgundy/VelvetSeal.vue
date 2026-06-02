<script setup>
import { computed } from 'vue'

const props = defineProps({
    state:    { type: String, default: 'intact' },   // intact | cracking | cracked
    motif:    { type: String, default: 'rose' },     // rose | crest | geometric (visual only, single asset for v1)
    monogram: { type: String, default: 'B & G' },
    size:     { type: Number, default: 120 },
})

const emit = defineEmits(['crack'])

function onClick() {
    if (props.state === 'intact') emit('crack')
}

const sizePx = computed(() => `${props.size}px`)
const isIntact   = computed(() => props.state === 'intact')
const isCracking = computed(() => props.state === 'cracking')
const isCracked  = computed(() => props.state === 'cracked')

// motif kept for future asset switch; v1 uses same wax-seal.png regardless
const sealSrc      = computed(() => `/images/templates/velvet-burgundy/wax-seal.svg`)
const sealLeftSrc  = computed(() => `/images/templates/velvet-burgundy/wax-seal-left.svg`)
const sealRightSrc = computed(() => `/images/templates/velvet-burgundy/wax-seal-right.svg`)
</script>

<template>
    <button
        type="button"
        class="vb-seal"
        :class="{ 'vb-seal--cracking': isCracking, 'vb-seal--cracked': isCracked }"
        :style="{ width: sizePx, height: sizePx }"
        :aria-label="`Buka segel ${monogram}`"
        :data-motif="motif"
        :disabled="!isIntact"
        @click="onClick"
    >
        <img v-if="isIntact" :src="sealSrc" alt="" class="vb-seal__whole"/>
        <template v-else>
            <img :src="sealLeftSrc"  alt="" class="vb-seal__half vb-seal__half--left"/>
            <img :src="sealRightSrc" alt="" class="vb-seal__half vb-seal__half--right"/>
        </template>
        <span v-if="isIntact" class="vb-seal__monogram">{{ monogram }}</span>
    </button>
</template>

<style scoped>
.vb-seal {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    min-width: 44px;
    min-height: 44px;
    transform: translateZ(0);
    transition: transform 0.2s ease-out;
}
.vb-seal:not(:disabled):hover { transform: scale(1.04); }
.vb-seal:disabled { cursor: default; }

.vb-seal__whole,
.vb-seal__half {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    pointer-events: none;
}

.vb-seal__half--left,
.vb-seal__half--right {
    width: 50%;
}
.vb-seal__half--left  { left: 0;  right: auto; }
.vb-seal__half--right { left: auto; right: 0; }

.vb-seal__monogram {
    position: relative;
    z-index: 2;
    color: #f8f1e7;
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 1.1rem;
    letter-spacing: 1px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.4);
    pointer-events: none;
}

.vb-seal--cracking .vb-seal__half--left  { animation: vb-seal-crack-left  1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.vb-seal--cracking .vb-seal__half--right { animation: vb-seal-crack-right 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

.vb-seal--cracked .vb-seal__half { opacity: 0; }

@keyframes vb-seal-crack-left {
    0%   { transform: translate(0,0) rotate(0deg);    opacity: 1; }
    20%  { transform: translate(0,0) rotate(-2deg);   opacity: 1; }
    100% { transform: translate(-40px,8px) rotate(-12deg); opacity: 0; }
}
@keyframes vb-seal-crack-right {
    0%   { transform: translate(0,0) rotate(0deg);   opacity: 1; }
    20%  { transform: translate(0,0) rotate(2deg);   opacity: 1; }
    100% { transform: translate(40px,8px) rotate(12deg); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .vb-seal--cracking .vb-seal__half,
    .vb-seal--cracked  .vb-seal__half {
        animation: none;
        opacity: 0;
        transform: none;
    }
    .vb-seal:not(:disabled):hover { transform: none; }
}
</style>
