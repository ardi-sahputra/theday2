<script setup>
import { computed } from 'vue'

const props = defineProps({
    type:     { type: String,  default: 'romantic' }, // romantic|tender|joyful|sacred
    label:    { type: String,  default: '' },
    showIcon: { type: Boolean, default: true },
})

const colorMap = {
    romantic: '#FF6B9D',
    tender:   '#4ECDC4',
    joyful:   '#FFD93D',
    sacred:   '#7B68EE',
}
const labelText = computed(() => props.label || props.type.toUpperCase())
const typeColor = computed(() => colorMap[props.type] ?? colorMap.romantic)
const iconSrc   = computed(() => `/images/templates/pokemon-tcg/type-${props.type}.svg`)
</script>

<template>
    <span
        class="tcg-type-badge"
        :style="{ '--tcg-type-color': typeColor }"
    >
        <img v-if="showIcon" :src="iconSrc" :alt="`${type} type`" class="tcg-type-icon" draggable="false"/>
        <span class="tcg-type-label">{{ labelText }}</span>
    </span>
</template>

<style scoped>
.tcg-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    background: rgba(255,255,255,0.06);
    border: 1px solid var(--tcg-type-color, currentColor);
    color: var(--tcg-type-color, #fff);
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    box-shadow: 0 0 6px var(--tcg-type-color, currentColor);
    animation: tcg-type-pulse 2.4s ease-in-out infinite alternate;
}
.tcg-type-icon { width: 14px; height: 14px; display: block; }
.tcg-type-label { line-height: 1; }
@keyframes tcg-type-pulse {
    from { box-shadow: 0 0 4px  var(--tcg-type-color, currentColor); }
    to   { box-shadow: 0 0 14px var(--tcg-type-color, currentColor); }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-type-badge { animation: none; }
}
</style>
