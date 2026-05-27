<script setup>
import { computed } from 'vue'

const props = defineProps({
    intensity: { type: String, default: 'subtle' }, // subtle | medium | strong
})

const opacityVal = computed(() => ({
    subtle: 0.06,
    medium: 0.12,
    strong: 0.20,
}[props.intensity] ?? 0.06))
</script>

<template>
    <div class="isg-arabesque-bg" :style="{ '--isg-pattern-opacity': opacityVal }" aria-hidden="true">
        <slot />
    </div>
</template>

<style scoped>
.isg-arabesque-bg {
    position: relative;
    /* inline SVG tile via data-URI — small repeating arabesque diamond grid */
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'><g fill='none' stroke='%230e4d3d' stroke-width='1'><path d='M40 5 L75 40 L40 75 L5 40 Z'/><path d='M40 20 L60 40 L40 60 L20 40 Z'/><circle cx='40' cy='40' r='3'/></g></svg>");
    background-repeat: repeat;
    background-size: 80px 80px;
    background-color: transparent;
    opacity: var(--isg-pattern-opacity, 0.06);
}
</style>
