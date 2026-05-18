<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: { type: String, default: 'inline-divider' },
        // header-flank | inline-divider | veil-edge | oval-frame | portrait-frame | square-frame | closing-divider
    side:    { type: String, default: 'left' },     // left | right (used for header-flank to mirror)
    density: { type: String, default: 'medium' },   // sparse | medium | ornate
    color:   { type: String, default: 'var(--sv-gold, #C9A961)' },
})

const isInline = computed(() =>
    ['header-flank', 'inline-divider', 'veil-edge'].includes(props.variant)
)

const externalSrc = computed(() => {
    switch (props.variant) {
        case 'oval-frame':      return '/images/templates/silk-veil/lace-oval.svg'
        case 'portrait-frame':  return '/images/templates/silk-veil/lace-portrait.svg'
        case 'square-frame':    return '/images/templates/silk-veil/lace-square.svg'
        case 'closing-divider': return '/images/templates/silk-veil/lace-closing.svg'
        default:                return null
    }
})

const densityOpacity = computed(() => {
    if (props.density === 'sparse') return 0.5
    if (props.density === 'ornate') return 1.0
    return 0.75
})

const densityStroke = computed(() => {
    if (props.density === 'sparse') return 0.5
    if (props.density === 'ornate') return 1.5
    return 1
})

const variantClass = computed(() => `sv-lace--${props.variant}`)
const flipClass    = computed(() => (props.variant === 'header-flank' && props.side === 'right' ? 'sv-lace--flip' : ''))
</script>

<template>
    <!-- Header flank: small floral spray (80×24), left/right via scaleX -->
    <svg
        v-if="variant === 'header-flank'"
        class="sv-lace"
        :class="[variantClass, flipClass]"
        :style="{ color, opacity: densityOpacity }"
        xmlns="http://www.w3.org/2000/svg"
        width="80" height="24" viewBox="0 0 80 24"
        fill="none" aria-hidden="true"
    >
        <g :stroke="color" :stroke-width="densityStroke" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12 L60 12"/>
            <path d="M60 12 Q66 4 72 12 Q78 20 72 12"/>
            <path d="M48 12 Q50 4 56 6 M48 12 Q50 20 56 18"/>
            <path d="M30 12 Q32 6 38 8 M30 12 Q32 18 38 16"/>
            <circle cx="78" cy="12" r="1.5" :fill="color"/>
        </g>
    </svg>

    <!-- Inline divider: 200×16 horizontal flourish -->
    <svg
        v-else-if="variant === 'inline-divider'"
        class="sv-lace"
        :class="variantClass"
        :style="{ color, opacity: densityOpacity }"
        xmlns="http://www.w3.org/2000/svg"
        width="200" height="16" viewBox="0 0 200 16"
        fill="none" aria-hidden="true"
    >
        <g :stroke="color" :stroke-width="densityStroke" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 8 L80 8 M120 8 L192 8"/>
            <path d="M80 8 Q90 2 100 8 Q110 14 120 8"/>
            <path d="M100 8 L100 3 M100 8 L100 13"/>
            <circle cx="100" cy="8" r="1.2" :fill="color"/>
        </g>
    </svg>

    <!-- Veil edge: thin horizontal trim used at top/bottom of the veil layer -->
    <svg
        v-else-if="variant === 'veil-edge'"
        class="sv-lace"
        :class="variantClass"
        :style="{ color, opacity: densityOpacity }"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 320 12"
        preserveAspectRatio="none"
        fill="none" aria-hidden="true"
    >
        <g :stroke="color" :stroke-width="densityStroke" stroke-linecap="round">
            <path d="M0 6 H320"/>
            <path d="M8 6 q8 -6 16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0"/>
        </g>
    </svg>

    <!-- External heavy SVG (oval / portrait / square / closing) loaded as img -->
    <img
        v-else-if="externalSrc"
        :src="externalSrc"
        alt=""
        aria-hidden="true"
        class="sv-lace sv-lace--external"
        :class="variantClass"
        :style="{ color, opacity: densityOpacity }"
    />
</template>

<style scoped>
.sv-lace {
    display: block;
    pointer-events: none;
    color: var(--sv-gold, #C9A961);
}
.sv-lace--flip { transform: scaleX(-1); }

.sv-lace--header-flank { width: 80px; height: 24px; }
.sv-lace--inline-divider { width: 200px; height: 16px; margin: 12px auto; }
.sv-lace--veil-edge { width: 100%; height: 12px; }

/* External heavy frames are positioned absolutely by the parent section CSS */
.sv-lace--external { width: 100%; height: 100%; }

@media (prefers-reduced-motion: reduce) {
    .sv-lace { animation: none; }
}
</style>
