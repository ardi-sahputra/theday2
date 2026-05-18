<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    index:        { type: Number, required: true },          // 0-based; displayed as idx+1
    title:        { type: String, required: true },
    subtitle:     { type: String, default: '' },
    duration:     { type: String, default: '' },             // pre-formatted "M:SS"
    thumbnailUrl: { type: String, default: null },
    fallbackHue:  { type: Number, default: 200 },            // 0-360 for placeholder gradient
})

const displayNumber = computed(() => String(props.index + 1).padStart(2, '0'))
const placeholderStyle = computed(() => ({
    background: `linear-gradient(135deg, hsl(${props.fallbackHue}, 70%, 55%), hsl(${(props.fallbackHue + 40) % 360}, 70%, 45%))`,
}))
const staggerDelay = computed(() => ({ '--d': (props.index * 0.08).toFixed(2) + 's' }))
</script>

<template>
    <div class="sw-track-row" :style="staggerDelay">
        <span class="sw-track-num">{{ displayNumber }}</span>
        <span class="sw-track-thumb">
            <img v-if="thumbnailUrl" :src="thumbnailUrl" :alt="title" loading="lazy"/>
            <span v-else class="sw-track-thumb-ph" :style="placeholderStyle"/>
        </span>
        <span class="sw-track-meta">
            <span class="sw-track-title">{{ title }}</span>
            <span v-if="subtitle" class="sw-track-sub">{{ subtitle }}</span>
        </span>
        <span v-if="duration" class="sw-track-duration">{{ duration }}</span>
    </div>
</template>

<style scoped>
.sw-track-row {
    display: grid;
    grid-template-columns: 36px 64px 1fr auto;
    align-items: center;
    gap: 16px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.12);
    opacity: 0;
    transform: translateX(-20px);
    transition:
        opacity 0.5s ease-out var(--d, 0s),
        transform 0.5s ease-out var(--d, 0s);
}
:global(.sw-visible) .sw-track-row {
    opacity: 1;
    transform: translateX(0);
}
.sw-track-num {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 24px;
    color: rgba(255,255,255,0.9);
    text-align: center;
    font-variant-numeric: tabular-nums;
}
.sw-track-thumb {
    display: block;
    width: 64px; height: 64px;
    border-radius: 8px;
    overflow: hidden;
    background: rgba(0,0,0,0.18);
}
.sw-track-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sw-track-thumb-ph { display: block; width: 100%; height: 100%; }
.sw-track-meta { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.sw-track-title {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 16px;
    color: #FFFFFF;
    line-height: 1.25;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.sw-track-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: rgba(255,255,255,0.6);
}
.sw-track-duration {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 13px;
    color: rgba(255,255,255,0.65);
    font-variant-numeric: tabular-nums;
}
@media (prefers-reduced-motion: reduce) {
    .sw-track-row { opacity: 1; transform: none; transition: none; }
}
</style>
