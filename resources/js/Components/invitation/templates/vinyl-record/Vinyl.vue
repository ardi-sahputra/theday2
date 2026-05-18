<script setup>
import { computed } from 'vue'
import { LABEL_COLOR_HEX } from './track-config.js'

const props = defineProps({
    spinning:        { type: Boolean, default: false },
    labelColor:      { type: String,  default: 'red' },   // red|blue|green|gold
    centerLabelText: { type: String,  default: 'WEDDING SESSIONS' },
    centerSubText:   { type: String,  default: '2026' },
    monogram:        { type: String,  default: 'A & B' },
    isPremium:       { type: Boolean, default: false },
})

const labelHex = computed(() => LABEL_COLOR_HEX[props.labelColor] ?? LABEL_COLOR_HEX.red)
</script>

<template>
    <div
        class="vr-vinyl"
        :class="{ 'vr-vinyl--playing': spinning }"
        role="img"
        :aria-label="`Vinyl record, ${spinning ? 'playing' : 'idle'}`"
    >
        <svg viewBox="0 0 400 400" class="vr-vinyl-svg" aria-hidden="true">
            <!-- vinyl body -->
            <circle cx="200" cy="200" r="198" fill="#111111"/>
            <!-- outer rim subtle highlight -->
            <circle cx="200" cy="200" r="198" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="0.6"/>
            <!-- groove rings (15 concentric) -->
            <g fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="0.5">
                <circle cx="200" cy="200" r="190"/>
                <circle cx="200" cy="200" r="182"/>
                <circle cx="200" cy="200" r="173"/>
                <circle cx="200" cy="200" r="164"/>
                <circle cx="200" cy="200" r="155"/>
                <circle cx="200" cy="200" r="146"/>
                <circle cx="200" cy="200" r="137"/>
                <circle cx="200" cy="200" r="128"/>
                <circle cx="200" cy="200" r="119"/>
                <circle cx="200" cy="200" r="110"/>
                <circle cx="200" cy="200" r="101"/>
                <circle cx="200" cy="200" r="93"/>
                <circle cx="200" cy="200" r="86"/>
            </g>
            <!-- specular highlight (subtle radial) -->
            <defs>
                <radialGradient id="vr-vinyl-spec" cx="35%" cy="35%" r="65%">
                    <stop offset="0%"   stop-color="rgba(255,255,255,0.06)"/>
                    <stop offset="60%"  stop-color="rgba(255,255,255,0)"/>
                </radialGradient>
            </defs>
            <circle cx="200" cy="200" r="198" fill="url(#vr-vinyl-spec)"/>
            <!-- center label outer ring -->
            <circle cx="200" cy="200" r="80" :fill="labelHex"/>
            <!-- center label paper -->
            <circle cx="200" cy="200" r="76" fill="#F5E6CC"/>
            <!-- label center text -->
            <g class="vr-label-text">
                <text
                    x="200" y="180"
                    text-anchor="middle"
                    font-family="'Bebas Neue', 'Oswald', Impact, sans-serif"
                    font-size="11"
                    fill="#1a1a1a"
                    letter-spacing="2"
                >{{ centerLabelText }}</text>
                <text
                    v-if="isPremium"
                    x="200" y="212"
                    text-anchor="middle"
                    font-family="'DM Serif Display', 'Playfair Display', Georgia, serif"
                    font-size="22"
                    font-style="italic"
                    fill="#1a1a1a"
                >{{ monogram }}</text>
                <text
                    v-else
                    x="200" y="212"
                    text-anchor="middle"
                    font-family="'Bebas Neue', sans-serif"
                    font-size="14"
                    fill="#B8902F"
                    letter-spacing="3"
                >THE DAY</text>
                <text
                    x="200" y="232"
                    text-anchor="middle"
                    font-family="'Inter', sans-serif"
                    font-size="9"
                    fill="#5C3A21"
                    letter-spacing="2"
                >{{ centerSubText }}</text>
            </g>
            <!-- spindle hole -->
            <circle cx="200" cy="200" r="4" fill="#050505"/>
            <circle cx="200" cy="200" r="2" fill="#1a1a1a"/>
        </svg>
    </div>
</template>

<style scoped>
.vr-vinyl {
    display: block;
    width: 100%;
    height: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.35);
}
.vr-vinyl-svg {
    width: 100%;
    height: 100%;
    display: block;
    animation: vr-spin 4s linear infinite;
    animation-play-state: paused;
    transform-origin: 50% 50%;
    will-change: transform;
}
.vr-vinyl.vr-vinyl--playing .vr-vinyl-svg {
    animation-play-state: running;
}
@keyframes vr-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
@media (prefers-reduced-motion: reduce) {
    .vr-vinyl-svg { animation: none; transform: none; }
}
</style>
