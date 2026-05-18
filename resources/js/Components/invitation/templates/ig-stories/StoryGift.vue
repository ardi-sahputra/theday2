<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import StoryFrame from './StoryFrame.vue'

defineProps({
    accountsCount: { type: Number, default: 0 },
})
const emit = defineEmits(['open-gift'])
</script>

<template>
    <StoryFrame story-key="gift" story-theme="light">
        <template #backdrop>
            <div class="igs-gift-bg"/>
        </template>
        <div class="igs-gift-stack">
            <p class="igs-gift-eye igs-stagger" style="--d: 0s">WEDDING GIFT</p>
            <h2 class="igs-gift-title igs-stagger" style="--d: 0.1s">SEND A GIFT</h2>
            <p class="igs-gift-sub igs-stagger" style="--d: 0.2s">Your blessings are enough. But if you'd like…</p>
            <button
                type="button"
                class="igs-gift-cta igs-stagger"
                style="--d: 0.35s"
                aria-label="Open gift accounts"
                @click="emit('open-gift')"
            >
                <svg viewBox="0 0 24 24" width="36" height="36" aria-hidden="true" class="igs-gift-chevron">
                    <path d="M6 14l6-6 6 6" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="igs-gift-cta-label">SWIPE UP TO SEND</span>
            </button>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-gift-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(150deg, #f6d365 0%, #fda085 100%);
}
.igs-gift-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
    flex: 1;
    justify-content: center;
    color: #191919;
}
.igs-gift-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #191919;
    margin: 0;
}
.igs-gift-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 32px;
    color: #191919;
    margin: 0;
    letter-spacing: -0.02em;
}
.igs-gift-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: rgba(25,25,25,0.72);
    margin: 0;
    max-width: 280px;
}
.igs-gift-cta {
    margin-top: 32px;
    background: transparent;
    border: none;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: #191919;
    min-height: 64px;
}
.igs-gift-chevron {
    animation: igs-gift-chevron 1.6s ease-in-out infinite;
}
@keyframes igs-gift-chevron {
    0%   { transform: translateY(0); }
    50%  { transform: translateY(-8px); }
    100% { transform: translateY(0); }
}
.igs-gift-cta-label {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 0.12em;
    color: #191919;
    animation: igs-gift-pulse 2s ease-in-out infinite;
}
@keyframes igs-gift-pulse {
    0%, 100% { opacity: 1; }
    50%      { opacity: 0.7; }
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-gift-chevron, .igs-gift-cta-label { animation: none; }
}
</style>
