<script setup>
import { computed } from 'vue'

const props = defineProps({
    pattern:  { type: String, default: 'striped' },   // striped|polka|floral|random|mixed
    position: { type: String, default: 'top-left' },  // top-left|top-right|bottom-left|bottom-right|horizontal-top|horizontal-bottom|top-center
    rotate:   { type: Number, default: null },        // default per position
    length:   { type: Number, default: 100 },         // px
    seed:     { type: Number, default: 0 },           // stable index for 'random' / 'mixed'
})

const PATTERNS = ['striped', 'polka', 'floral']

const resolvedPattern = computed(() => {
    if (props.pattern === 'random' || props.pattern === 'mixed') {
        return PATTERNS[Math.abs(props.seed) % PATTERNS.length]
    }
    if (PATTERNS.includes(props.pattern)) return props.pattern
    return 'striped'
})

const tapeUrl = computed(() => `/images/templates/photo-album/washi-${resolvedPattern.value}.svg`)

const defaultRotate = {
    'top-left':         -12,
    'top-right':         12,
    'bottom-left':       12,
    'bottom-right':     -12,
    'horizontal-top':     0,
    'horizontal-bottom':  0,
    'top-center':         0,
}

const finalRotate = computed(() =>
    props.rotate ?? defaultRotate[props.position] ?? 0
)

const tapeStyle = computed(() => {
    const base = {
        width: `${props.length}px`,
        height: '24px',
        backgroundImage: `url(${tapeUrl.value})`,
        '--pa-washi-rotate': `${finalRotate.value}deg`,
    }
    switch (props.position) {
        case 'top-left':         return { ...base, top: '-10px', left:  '-14px' }
        case 'top-right':        return { ...base, top: '-10px', right: '-14px' }
        case 'bottom-left':      return { ...base, bottom: '-10px', left:  '-14px' }
        case 'bottom-right':     return { ...base, bottom: '-10px', right: '-14px' }
        case 'horizontal-top':   return { ...base, top: '-12px',    left: '50%', '--pa-washi-translate-x': '-50%' }
        case 'horizontal-bottom':return { ...base, bottom: '-12px', left: '50%', '--pa-washi-translate-x': '-50%' }
        case 'top-center':       return { ...base, top: '-12px',    left: '50%', '--pa-washi-translate-x': '-50%' }
        default:                 return base
    }
})
</script>

<template>
    <span class="pa-washi pa-reveal" :style="tapeStyle" aria-hidden="true"/>
</template>

<style scoped>
.pa-washi {
    position: absolute;
    background-size: 100% 100%;
    background-repeat: no-repeat;
    transform-origin: center;
    transform: translateX(var(--pa-washi-translate-x, 0)) rotate(var(--pa-washi-rotate, 0deg));
    opacity: 0.92;
    z-index: 20;
    clip-path: inset(0 100% 0 0);
    transition: clip-path 0.4s ease-out;
    pointer-events: none;
}
.pa-washi.pa-visible { clip-path: inset(0 0 0 0); }

@media (prefers-reduced-motion: reduce) {
    .pa-washi { clip-path: none !important; transition: none !important; opacity: 0.92; }
}
</style>
