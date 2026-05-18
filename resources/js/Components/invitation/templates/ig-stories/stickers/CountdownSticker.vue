<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    days:        { type: Number, default: 0 },
    targetLabel: { type: String, default: 'DAYS' },
    maxDays:     { type: Number, default: 365 },
})

const R = 88
const CIRCUMFERENCE = computed(() => 2 * Math.PI * R)
const dashOffset = computed(() => {
    const ratio = Math.min(1, Math.max(0, props.days / Math.max(1, props.maxDays)))
    return CIRCUMFERENCE.value * (1 - ratio)
})
</script>

<template>
    <div class="igs-sticker igs-countdown" role="img" :aria-label="`${days} ${targetLabel}`">
        <svg class="igs-countdown-ring" viewBox="0 0 200 200" aria-hidden="true">
            <defs>
                <linearGradient id="igs-cd-grad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%"   stop-color="#FFFFFF" stop-opacity="0.95"/>
                    <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.55"/>
                </linearGradient>
            </defs>
            <circle cx="100" cy="100" r="88" stroke="rgba(255,255,255,0.18)" stroke-width="8" fill="none"/>
            <circle
                cx="100" cy="100" r="88"
                stroke="url(#igs-cd-grad)"
                stroke-width="8"
                fill="none"
                stroke-linecap="round"
                :stroke-dasharray="CIRCUMFERENCE"
                :stroke-dashoffset="dashOffset"
                transform="rotate(-90 100 100)"
                class="igs-countdown-ring-fg"
            />
        </svg>
        <div class="igs-countdown-center">
            <span class="igs-countdown-digits">{{ days }}</span>
            <span class="igs-countdown-label">{{ targetLabel }}</span>
        </div>
    </div>
</template>

<style scoped>
.igs-countdown {
    position: relative;
    width: 220px;
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.igs-countdown-ring {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
.igs-countdown-ring-fg {
    transition: stroke-dashoffset 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.igs-countdown-center {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #FFFFFF;
}
.igs-countdown-digits {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 72px;
    line-height: 1;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.04em;
}
.igs-countdown-label {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 0.18em;
    opacity: 0.92;
}
@media (prefers-reduced-motion: reduce) {
    .igs-countdown-ring-fg { transition: none; }
}
</style>
