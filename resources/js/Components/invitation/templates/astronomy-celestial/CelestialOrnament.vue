<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: { type: String, default: 'full' }, // comet | sun | moon | full
})

const pathMap = {
    comet: `<line x1='20' y1='20' x2='180' y2='20' />
            <path d='M180 20 Q190 16 198 20 Q210 22 218 18' />
            <circle cx='198' cy='20' r='1.5' fill='currentColor' />`,
    sun:   `<line x1='20' y1='20' x2='100' y2='20' />
            <circle cx='120' cy='20' r='6' />
            <path d='M120 6 L120 12 M120 28 L120 34 M106 20 L112 20 M128 20 L134 20' />
            <line x1='140' y1='20' x2='220' y2='20' />`,
    moon:  `<line x1='20' y1='20' x2='100' y2='20' />
            <path d='M114 14 A8 8 0 1 0 114 26 A6 6 0 1 1 114 14 Z' />
            <line x1='140' y1='20' x2='220' y2='20' />`,
    full:  `<line x1='20' y1='20' x2='100' y2='20' />
            <circle cx='120' cy='20' r='6' />
            <circle cx='120' cy='20' r='2' fill='currentColor' />
            <path d='M114 8 L120 14 L126 8 M114 32 L120 26 L126 32' />
            <line x1='140' y1='20' x2='220' y2='20' />
            <circle cx='20' cy='20' r='1.5' fill='currentColor' />
            <circle cx='220' cy='20' r='1.5' fill='currentColor' />`,
}

const innerHtml = computed(() => pathMap[props.variant] ?? pathMap.full)
</script>

<template>
    <span class="ac-ornament" aria-hidden="true">
        <svg viewBox="0 0 240 40" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" v-html="innerHtml"/>
    </span>
</template>

<style scoped>
.ac-ornament {
    display: inline-block;
    width: 240px;
    max-width: 80%;
    color: var(--ac-gold, #d4af37);
}
.ac-ornament svg {
    width: 100%;
    height: auto;
    display: block;
}
.ac-ornament svg :deep(line),
.ac-ornament svg :deep(path),
.ac-ornament svg :deep(circle) {
    stroke-dasharray: 220;
    stroke-dashoffset: 220;
    animation: ac-orn-draw 1.2s ease-out forwards;
}
.ac-ornament svg :deep(circle[fill='currentColor']) {
    animation-name: ac-orn-fade;
    stroke-dasharray: none;
    stroke-dashoffset: 0;
}
@keyframes ac-orn-draw { to { stroke-dashoffset: 0; } }
@keyframes ac-orn-fade { from { opacity: 0; } to { opacity: 1; } }
@media (prefers-reduced-motion: reduce) {
    .ac-ornament svg :deep(line),
    .ac-ornament svg :deep(path),
    .ac-ornament svg :deep(circle) {
        animation: none;
        stroke-dashoffset: 0;
    }
}
</style>
