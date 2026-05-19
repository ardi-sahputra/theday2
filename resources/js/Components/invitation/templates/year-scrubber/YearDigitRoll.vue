<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    year: { type: Number, required: true },
    size: { type: String, default: 'huge' }, // huge | large | medium
})

const digits = computed(() => String(Math.max(0, Math.floor(props.year))).split('').map(Number))

const fontSize = computed(() => ({
    huge:   'clamp(120px, 28vw, 240px)',
    large:  'clamp(80px, 16vw, 120px)',
    medium: 'clamp(48px, 10vw, 80px)',
}[props.size] ?? 'clamp(120px, 28vw, 240px)'))
</script>

<template>
    <div
        class="ys-digit-roll"
        :style="{ fontSize: fontSize }"
        aria-live="polite"
        :aria-label="`Tahun ${year}`"
    >
        <span
            v-for="(d, i) in digits"
            :key="i"
            class="ys-digit-slot"
        >
            <span
                class="ys-digit-stack"
                :style="{ transform: `translateY(${-d * 10}%)` }"
            >
                <span v-for="n in 10" :key="n - 1" class="ys-digit-cell">{{ n - 1 }}</span>
            </span>
        </span>
    </div>
</template>

<style scoped>
.ys-digit-roll {
    display: inline-flex;
    font-family: 'Bebas Neue', 'Oswald', 'Impact', sans-serif;
    font-weight: 400;
    color: #1A2E4A;
    line-height: 1;
    letter-spacing: 0.02em;
    font-feature-settings: 'tnum';
    font-variant-numeric: tabular-nums;
}
.ys-digit-slot {
    display: inline-block;
    height: 1em;
    overflow: hidden;
    vertical-align: top;
}
.ys-digit-stack {
    display: block;
    height: 1000%;
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1);
    will-change: transform;
}
.ys-digit-cell {
    display: block;
    height: 10%;
    line-height: 1;
}
@media (prefers-reduced-motion: reduce) {
    .ys-digit-stack { transition: none; }
}
</style>
