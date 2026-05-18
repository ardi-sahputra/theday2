<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { computed } from 'vue'
import Equalizer from './Equalizer.vue'

const props = defineProps({
    countdown:       { type: Object,  default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:      { type: [Date, String, null], default: null },
    firstEventDate:  { type: String,  default: '' },
    pad:             { type: Function, default: (n) => String(n).padStart(2, '0') },
    equalizerSpeed:  { type: String,  default: 'normal' },
})

const isLive = computed(() => {
    const c = props.countdown
    return !props.targetDate || (c?.days ?? 0) < 0
})
</script>

<template>
    <section
        class="sw-slide sw-slide-countdown"
        data-slide-key="countdown"
        :style="{
            '--sw-bg-from':       '#E91D8E',
            '--sw-bg-to':         '#FF3B7D',
            '--sw-bg-direction':  '170deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">PREMIERE COUNTDOWN</span>
                <span class="sw-slide-counter">05 / 10</span>
            </header>

            <div v-if="!isLive" class="sw-cd-stack">
                <div class="sw-cd-huge">{{ countdown.days }}</div>
                <p class="sw-cd-unit">DAYS UNTIL THE BIG DROP</p>
                <div class="sw-cd-sub">
                    <Transition name="sw-flip" mode="out-in">
                        <span :key="countdown.hours">{{ pad(countdown.hours) }}H</span>
                    </Transition>
                    <span class="sw-cd-sep">:</span>
                    <Transition name="sw-flip" mode="out-in">
                        <span :key="countdown.minutes">{{ pad(countdown.minutes) }}M</span>
                    </Transition>
                    <span class="sw-cd-sep">:</span>
                    <Transition name="sw-flip" mode="out-in">
                        <span :key="countdown.seconds">{{ pad(countdown.seconds) }}S</span>
                    </Transition>
                </div>
                <p v-if="firstEventDate" class="sw-cd-footer">{{ firstEventDate }}</p>
                <Equalizer :bars="7" :speed="equalizerSpeed" color="#FFFFFF" :height="48" class="sw-cd-eq"/>
            </div>

            <div v-else class="sw-cd-stack sw-cd-live">
                <h2 class="sw-cd-now-title">NOW PLAYING</h2>
                <p class="sw-cd-now-sub">The wedding has started.</p>
                <Equalizer :bars="7" :speed="equalizerSpeed" color="#FFFFFF" :height="48" class="sw-cd-eq"/>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-cd-stack {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    gap: 16px; margin-top: 32px;
}
.sw-cd-huge {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(80px, 22vw, 160px);
    line-height: 0.9;
    letter-spacing: -0.04em;
    font-variant-numeric: tabular-nums;
}
.sw-cd-unit {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    letter-spacing: 0.06em;
    margin: 0;
}
.sw-cd-sub {
    display: inline-flex; align-items: center; gap: 4px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 18px;
    font-variant-numeric: tabular-nums;
    margin-top: 8px;
}
.sw-cd-sep { opacity: 0.6; }
.sw-cd-footer {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    opacity: 0.72;
    margin: 4px 0 0;
}
.sw-cd-eq { margin-top: 16px; }
.sw-cd-now-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(56px, 14vw, 96px);
    margin: 0;
    letter-spacing: -0.03em;
}
.sw-cd-now-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 18px;
    margin: 8px 0 0;
}

.sw-flip-enter-active, .sw-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    display: inline-block;
}
.sw-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.sw-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .sw-flip-enter-active, .sw-flip-leave-active { transition: none; }
    .sw-flip-enter-from, .sw-flip-leave-to { transform: none; opacity: 1; }
}
</style>
