<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'
import StoryFrame       from './StoryFrame.vue'
import CountdownSticker from './stickers/CountdownSticker.vue'

const props = defineProps({
    countdown:      { type: Object, default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:     { type: [String, Object, Date], default: null },
    firstEventDate: { type: String, default: '' },
    pad:            { type: Function, default: (n) => String(n).padStart(2, '0') },
})

const isLive = computed(() => {
    if (!props.targetDate) return false
    return Number(props.countdown?.days ?? 0) < 0
})
</script>

<template>
    <StoryFrame story-key="countdown" story-theme="dark">
        <template #backdrop>
            <div class="igs-cd-bg"/>
        </template>
        <div class="igs-cd-stack">
            <p class="igs-cd-eye igs-stagger" style="--d: 0s">COUNTDOWN</p>
            <template v-if="!isLive">
                <div class="igs-cd-ring-wrap igs-stagger" style="--d: 0.15s">
                    <CountdownSticker :days="Math.max(0, Number(countdown?.days ?? 0))" target-label="DAYS TO GO"/>
                </div>
                <p class="igs-cd-row igs-stagger" style="--d: 0.3s">
                    {{ pad(countdown?.hours ?? 0) }}H · {{ pad(countdown?.minutes ?? 0) }}M · {{ pad(countdown?.seconds ?? 0) }}S
                </p>
                <p class="igs-cd-footer igs-stagger" style="--d: 0.45s">{{ firstEventDate }}</p>
            </template>
            <template v-else>
                <h2 class="igs-cd-live-title igs-stagger" style="--d: 0.15s">LIVE NOW</h2>
                <p class="igs-cd-live-sub igs-stagger" style="--d: 0.3s">The wedding has begun</p>
            </template>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-cd-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(165deg, #FF416C 0%, #FF4B2B 100%);
}
.igs-cd-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 16px;
    flex: 1;
}
.igs-cd-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
}
.igs-cd-ring-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
}
.igs-cd-row {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 16px;
    color: #FFFFFF;
    margin: 0;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.02em;
}
.igs-cd-footer {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    margin: 0;
}
.igs-cd-live-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(40px, 12vw, 64px);
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.04em;
}
.igs-cd-live-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    color: rgba(255,255,255,0.85);
    margin: 0;
}
.igs-stagger {
    opacity: 0;
    transform: scale(0.8) translateY(8px);
    transition: opacity 0.5s ease-out var(--d, 0s), transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: scale(1) translateY(0);
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
}
</style>
