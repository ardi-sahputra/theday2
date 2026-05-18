<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    density: { type: String,  default: 'medium' }, // sparse | medium | dense
    tint:    { type: String,  default: 'neutral' }, // neutral | red | blue | yellow | green
    opacity: { type: Number,  default: 0.18 },
    shimmer: { type: Boolean, default: false },
})

const patternFile = computed(() => ({
    sparse: '/images/templates/comic-book/cb-halftone-sm.svg',
    medium: '/images/templates/comic-book/cb-halftone-md.svg',
    dense:  '/images/templates/comic-book/cb-halftone-lg.svg',
}[props.density] ?? '/images/templates/comic-book/cb-halftone-md.svg'))

const tintColor = computed(() => ({
    neutral: 'transparent',
    red:     'rgba(230, 57, 70, 0.22)',
    blue:    'rgba(29, 53, 87, 0.22)',
    yellow:  'rgba(241, 196, 83, 0.28)',
    green:   'rgba(42, 157, 143, 0.22)',
}[props.tint] ?? 'transparent'))
</script>

<template>
    <span class="cb-halftone" :class="{ 'cb-halftone-shimmer': shimmer }"
          :style="{ '--cb-halftone-url': `url(${patternFile})`, '--cb-halftone-tint': tintColor, opacity: opacity }"
          aria-hidden="true">
        <span v-if="tint !== 'neutral'" class="cb-halftone-tint"/>
    </span>
</template>

<style scoped>
.cb-halftone {
    position: absolute;
    inset: 0;
    background-image: var(--cb-halftone-url);
    background-repeat: repeat;
    background-size: 24px 24px;
    pointer-events: none;
    mix-blend-mode: multiply;
}
.cb-halftone-tint {
    position: absolute;
    inset: 0;
    background: var(--cb-halftone-tint);
    mix-blend-mode: multiply;
}
.cb-halftone-shimmer {
    animation: cb-halftone-drift 8s linear infinite;
}
@keyframes cb-halftone-drift {
    0%   { background-position: 0 0; }
    100% { background-position: 24px 24px; }
}
@media (prefers-reduced-motion: reduce) {
    .cb-halftone-shimmer { animation: none; }
}
</style>
