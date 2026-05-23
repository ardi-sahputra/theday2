<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import Equalizer from './Equalizer.vue'

defineProps({
    brandName:      { type: String,  default: 'Theday Wrapped' },
    groomNick:      { type: String,  default: '' },
    brideNick:      { type: String,  default: '' },
    year:           { type: String,  default: '2026' },
    showYearBg:     { type: Boolean, default: true },
    equalizerSpeed: { type: String,  default: 'normal' },
    isPremium:      { type: Boolean, default: false },
})
const emit = defineEmits(['start'])
</script>

<template>
    <section
        class="sw-slide sw-slide-intro"
        data-slide-key="intro"
        :style="{
            '--sw-bg-from':       '#1ED760',
            '--sw-bg-to':         '#191414',
            '--sw-bg-direction':  '180deg',
        }"
    >
        <span v-if="showYearBg" class="sw-year-bg" aria-hidden="true">{{ year }}</span>

        <div class="sw-slide-content sw-slide-intro-inner">
            <header class="sw-intro-top">
                <span class="sw-brand">{{ brandName }}</span>
                <span class="sw-slide-counter">01 / 10</span>
            </header>

            <div class="sw-intro-hero">
                <p class="sw-intro-eyebrow">YOUR WEDDING</p>
                <h1 class="sw-intro-title">WRAPPED</h1>
                <p class="sw-intro-couple">{{ groomNick }} &amp; {{ brideNick }}</p>
                <p class="sw-intro-year">{{ year }}</p>
            </div>

            <div class="sw-intro-cta-wrap">
                <button type="button" class="sw-cta-pill" @click="emit('start')">
                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                        <path d="M6 4l14 8-14 8z" fill="currentColor"/>
                    </svg>
                    START WRAPPED
                </button>
                <span class="sw-intro-scroll-hint" aria-hidden="true">SCROLL ↓</span>
            </div>

            <Equalizer :bars="5" :speed="equalizerSpeed" color="#FFFFFF" :height="40" class="sw-intro-eq"/>

            <p v-if="!isPremium" class="sw-watermark sw-watermark-intro">Powered by Theday</p>
        </div>
    </section>
</template>

<style scoped>
.sw-slide-intro {
    --sw-intro-text: #FFFFFF;
    color: var(--sw-intro-text);
    overflow: hidden;
}
.sw-slide-intro-inner {
    position: relative; z-index: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 24px;
}
.sw-year-bg {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 64vw;
    line-height: 1;
    opacity: 0.08;
    color: #FFFFFF;
    pointer-events: none;
    z-index: 0;
    animation: sw-year-drift 8s ease-in-out infinite alternate;
    letter-spacing: -0.04em;
}
@keyframes sw-year-drift {
    0%   { transform: translate(-2%, 2%); }
    100% { transform: translate(2%, -2%); }
}
.sw-intro-top { display: flex; justify-content: space-between; align-items: center; }
.sw-brand {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 18px;
    letter-spacing: -0.01em;
}
.sw-slide-counter {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    letter-spacing: 0.12em;
    opacity: 0.72;
}
.sw-intro-hero { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; }
.sw-intro-eyebrow {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.18em;
    margin: 0;
    opacity: 0.85;
}
.sw-intro-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(64px, 18vw, 120px);
    line-height: 0.95;
    letter-spacing: -0.04em;
    margin: 0;
}
.sw-intro-couple {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: clamp(22px, 5vw, 32px);
    margin: 12px 0 0;
    letter-spacing: -0.01em;
}
.sw-intro-year {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: clamp(40px, 8vw, 64px);
    margin: 0;
    letter-spacing: -0.02em;
}
.sw-intro-cta-wrap { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.sw-cta-pill {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 28px;
    background: #FFFFFF;
    color: #191414;
    border: none;
    border-radius: 9999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
}
.sw-cta-pill:hover { transform: scale(1.03); }
.sw-intro-scroll-hint {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    letter-spacing: 0.18em;
    opacity: 0.6;
    animation: sw-scroll-bounce 1.6s ease-in-out infinite;
}
@keyframes sw-scroll-bounce {
    0%, 100% { transform: translateY(0); opacity: 0.6; }
    50%      { transform: translateY(4px); opacity: 0.85; }
}
.sw-intro-eq {
    position: absolute;
    bottom: 48px;
    right: 24px;
    z-index: 2;
}
.sw-watermark-intro {
    position: absolute;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    letter-spacing: 0.12em;
    margin: 0;
    z-index: 2;
}
@media (prefers-reduced-motion: reduce) {
    .sw-year-bg { animation: none; transform: none; }
    .sw-cta-pill, .sw-cta-pill:hover { transform: none; transition: none; }
    .sw-intro-scroll-hint { animation: none; }
}
</style>
