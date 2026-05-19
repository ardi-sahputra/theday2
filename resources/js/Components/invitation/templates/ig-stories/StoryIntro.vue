<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import StoryFrame from './StoryFrame.vue'

defineProps({
    groomNick:      { type: String, default: '' },
    brideNick:      { type: String, default: '' },
    firstEventDate: { type: String, default: '' },
    openingText:    { type: String, default: '' },
})
</script>

<template>
    <StoryFrame story-key="opening" story-theme="dark">
        <template #backdrop>
            <div class="igs-intro-bg"/>
        </template>
        <div class="igs-intro-stack">
            <p class="igs-intro-hero igs-stagger" style="--d: 0s">WE'RE GETTING MARRIED</p>
            <span class="igs-intro-heart igs-boomerang" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="32" height="32">
                    <path d="M12 21s-7-4.5-9.5-9C.5 7 4 3 8 4.5 10 5.25 12 7 12 7s2-1.75 4-2.5C20 3 23.5 7 21.5 12 19 16.5 12 21 12 21z" fill="#FFFFFF"/>
                </svg>
            </span>
            <p class="igs-intro-couple igs-stagger" style="--d: 0.15s">{{ groomNick }} &amp; {{ brideNick }}</p>
            <span class="igs-intro-pill igs-stagger" style="--d: 0.3s">{{ firstEventDate }}</span>
            <p class="igs-intro-sub igs-stagger" style="--d: 0.45s">{{ openingText.slice(0, 80) }}{{ openingText.length > 80 ? '…' : '' }}</p>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-intro-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%);
}
.igs-intro-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 12px;
    flex: 1;
}
.igs-intro-hero {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(32px, 9vw, 44px);
    color: #FFFFFF;
    letter-spacing: -0.02em;
    line-height: 1.05;
    margin: 0;
    max-width: 320px;
}
.igs-intro-heart {
    display: inline-flex;
    margin: 4px 0;
}
.igs-intro-couple {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 32px;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.01em;
}
.igs-intro-pill {
    display: inline-block;
    background: rgba(0,0,0,0.25);
    color: #FFFFFF;
    border-radius: 9999px;
    padding: 8px 16px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.08em;
}
.igs-intro-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    color: rgba(255,255,255,0.85);
    margin: 8px 0 0;
    line-height: 1.5;
    max-width: 280px;
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
.igs-boomerang {
    animation: igs-boomerang 1.2s ease-in-out infinite alternate;
}
@keyframes igs-boomerang {
    from { transform: translateY(-5px) scale(0.98); }
    to   { transform: translateY(5px)  scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-boomerang { animation: none; }
}
</style>
