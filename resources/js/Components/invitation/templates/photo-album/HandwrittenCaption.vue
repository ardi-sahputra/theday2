<script setup>
import { computed } from 'vue'

const props = defineProps({
    rotate: { type: Number, default: 0 },                  // -3..+3 typical
    size:   { type: String, default: 'md' },               // sm|md|lg
    color:  { type: String, default: '#8b6f47' },          // pa-handwriting
})

const SIZE_PX = { sm: '14px', md: '20px', lg: '32px' }

const captionStyle = computed(() => ({
    '--pa-cap-rotate': `${props.rotate}deg`,
    '--pa-cap-color':  props.color,
    '--pa-cap-size':   SIZE_PX[props.size] ?? SIZE_PX.md,
}))
</script>

<template>
    <span class="pa-handwriting-caption" :style="captionStyle">
        <slot/>
    </span>
</template>

<style scoped>
.pa-handwriting-caption {
    display: inline-block;
    font-family: 'Homemade Apple', 'Caveat', cursive;
    color: var(--pa-cap-color, #8b6f47);
    font-size: var(--pa-cap-size, 20px);
    line-height: 1.3;
    transform: rotate(var(--pa-cap-rotate, 0deg));
    transform-origin: center;
    white-space: pre-wrap;
}
@media (prefers-reduced-motion: reduce) {
    .pa-handwriting-caption { transform: rotate(var(--pa-cap-rotate, 0deg)); }
}
</style>
