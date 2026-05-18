<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import Equalizer from './Equalizer.vue'

defineProps({
    brandName:      { type: String,  default: 'TheDay Wrapped' },
    year:           { type: String,  default: '2026' },
    groomNick:      { type: String,  default: '' },
    brideNick:      { type: String,  default: '' },
    closingText:    { type: String,  default: '' },
    shareHandler:   { type: Function, required: true },
    isPremium:      { type: Boolean, default: false },
    equalizerSpeed: { type: String,  default: 'normal' },
})
</script>

<template>
    <section
        class="sw-slide sw-slide-closing"
        data-slide-key="closing"
    >
        <div class="sw-slide-content sw-closing-stack">
            <header class="sw-closing-top">
                <span class="sw-brand">{{ brandName }}</span>
                <span class="sw-slide-counter">10 / 10</span>
            </header>

            <div class="sw-closing-hero">
                <h2 class="sw-closing-title">WRAPPED {{ year }}</h2>
                <p class="sw-closing-couple">{{ groomNick }} &amp; {{ brideNick }}</p>
                <p v-if="closingText" class="sw-closing-text">{{ closingText }}</p>
            </div>

            <div class="sw-closing-footer">
                <button type="button" class="sw-cta-pill sw-cta-pulse" @click="shareHandler">
                    SHARE YOUR WRAPPED
                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                        <path d="M7 17L17 7M17 7H9M17 7V15" stroke="currentColor" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <Equalizer :bars="5" :speed="equalizerSpeed" color="#FFFFFF" :height="32" class="sw-closing-eq"/>
                <p v-if="!isPremium" class="sw-watermark">Powered by TheDay</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-slide-closing {
    background: linear-gradient(135deg, #E13300, #FFCB3E, #1ED760, #0066FF, #7B2CBF, #E91D8E, #E13300);
    background-size: 400% 400%;
    animation: sw-rainbow 12s ease infinite;
    color: #FFFFFF;
}
@keyframes sw-rainbow {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.sw-closing-stack {
    height: 100%;
    display: flex; flex-direction: column;
    justify-content: space-between;
    gap: 24px;
}
.sw-closing-top { display: flex; justify-content: space-between; align-items: center; }
.sw-brand {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 18px;
}
.sw-slide-counter {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    letter-spacing: 0.12em;
    opacity: 0.72;
}
.sw-closing-hero {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px;
    opacity: 0;
    transform: scale(0.95);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
:global(.sw-visible) .sw-closing-hero { opacity: 1; transform: scale(1); }
.sw-closing-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(56px, 16vw, 96px);
    margin: 0;
    letter-spacing: -0.04em;
    line-height: 0.95;
}
.sw-closing-couple {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: clamp(20px, 5vw, 32px);
    margin: 0;
}
.sw-closing-text {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    line-height: 1.6;
    max-width: 480px;
    margin: 8px 0 0;
    opacity: 0.9;
}
.sw-closing-footer {
    display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.sw-cta-pill {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 28px;
    background: #FFFFFF; color: #191414;
    border: none; border-radius: 9999px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 13px;
    letter-spacing: 0.08em;
    cursor: pointer;
}
.sw-cta-pulse { animation: sw-cta-pulse 1.8s ease-in-out infinite; }
@keyframes sw-cta-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
    50%      { box-shadow: 0 0 0 12px rgba(255,255,255,0); }
}
.sw-closing-eq { opacity: 0.8; }
.sw-watermark {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    color: rgba(255,255,255,0.6);
    letter-spacing: 0.12em;
    margin: 0;
}
@media (prefers-reduced-motion: reduce) {
    .sw-slide-closing { animation: none; background-position: 0% 50%; }
    .sw-closing-hero { opacity: 1; transform: none; transition: none; }
    .sw-cta-pulse { animation: none; }
}
</style>
