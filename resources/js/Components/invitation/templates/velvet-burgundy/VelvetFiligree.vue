<script setup>
import { computed } from 'vue'

const props = defineProps({
    corner:  { type: String, default: 'top-l' }, // top-l | top-r | bot-l | bot-r | divider
    density: { type: String, default: 'medium' }, // subtle | medium | ornate
    color:   { type: String, default: 'var(--vb-gold-soft)' },
})

const assetPath = computed(() => {
    if (props.corner === 'divider') return '/images/templates/velvet-burgundy/filigree-divider.svg'
    const map = {
        'top-l': 'tl',
        'top-r': 'tr',
        'bot-l': 'bl',
        'bot-r': 'br',
    }
    const key = map[props.corner] ?? 'tl'
    return `/images/templates/velvet-burgundy/filigree-corner-${key}.svg`
})

const opacityForDensity = computed(() => {
    if (props.density === 'subtle') return 0.4
    if (props.density === 'ornate') return 1.0
    return 0.7
})

const cornerClass = computed(() => `vb-filigree--${props.corner}`)
</script>

<template>
    <img
        :src="assetPath"
        alt=""
        aria-hidden="true"
        class="vb-filigree"
        :class="cornerClass"
        :style="{ color, opacity: opacityForDensity }"
    />
</template>

<style scoped>
.vb-filigree {
    position: absolute;
    width: 96px;
    height: 96px;
    pointer-events: none;
    z-index: 2;
}
.vb-filigree--top-l { top: 8px;    left: 8px; }
.vb-filigree--top-r { top: 8px;    right: 8px; }
.vb-filigree--bot-l { bottom: 8px; left: 8px; }
.vb-filigree--bot-r { bottom: 8px; right: 8px; }
.vb-filigree--divider {
    position: relative;
    display: block;
    width: 240px;
    height: 28px;
    margin: 12px auto;
}

@media (min-width: 480px) {
    .vb-filigree { width: 120px; height: 120px; }
}
</style>
