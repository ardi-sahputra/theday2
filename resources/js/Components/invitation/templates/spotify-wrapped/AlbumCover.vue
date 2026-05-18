<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    photoUrl:    { type: String, default: null },
    trackNumber: { type: Number, required: true }, // 1-based for display
    caption:     { type: String, default: '' },
    fallbackHue: { type: Number, default: 280 },
})
const emit = defineEmits(['lightbox'])

const displayNum = computed(() => '#' + String(props.trackNumber).padStart(2, '0'))
const placeholderStyle = computed(() => ({
    background: `linear-gradient(135deg, hsl(${props.fallbackHue}, 70%, 60%), hsl(${(props.fallbackHue + 50) % 360}, 70%, 45%))`,
}))
</script>

<template>
    <button
        type="button"
        class="sw-album"
        @click="photoUrl && emit('lightbox', photoUrl)"
        :aria-label="caption || `Album ${displayNum}`"
    >
        <img v-if="photoUrl" :src="photoUrl" :alt="caption" loading="lazy" class="sw-album-img"/>
        <span v-else class="sw-album-ph" :style="placeholderStyle"/>
        <span class="sw-album-num">{{ displayNum }}</span>
        <span class="sw-album-grad" aria-hidden="true"/>
        <span v-if="caption" class="sw-album-caption">{{ caption }}</span>
    </button>
</template>

<style scoped>
.sw-album {
    position: relative;
    display: block;
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 8px;
    overflow: hidden;
    background: rgba(0,0,0,0.18);
    border: none;
    padding: 0;
    cursor: pointer;
    transform: translateY(0) scale(1);
    transition: transform 0.3s ease;
}
.sw-album:hover { transform: translateY(-2px) scale(1.01); }
.sw-album-img, .sw-album-ph {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.sw-album-num {
    position: absolute;
    top: 10px; left: 12px;
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 24px;
    color: #FFFFFF;
    text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    z-index: 2;
    line-height: 1;
}
.sw-album-grad {
    position: absolute;
    inset: auto 0 0 0;
    height: 50%;
    background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);
    z-index: 1;
    pointer-events: none;
}
.sw-album-caption {
    position: absolute;
    bottom: 10px; left: 12px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 12px;
    color: rgba(255,255,255,0.85);
    z-index: 2;
}
@media (prefers-reduced-motion: reduce) {
    .sw-album, .sw-album:hover { transform: none; transition: none; }
}
</style>
