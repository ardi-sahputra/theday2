<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import StoryFrame from './StoryFrame.vue'
import BrandWatermark from '../BrandWatermark.vue'

defineProps({
    brandName:     { type: String, default: 'Theday' },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
    closingText:   { type: String, default: '' },
    showWatermark: { type: Boolean, default: false },
})
const emit = defineEmits(['replay', 'share'])
</script>

<template>
    <StoryFrame story-key="closing" story-theme="dark">
        <template #backdrop>
            <div class="igs-outro-bg"/>
        </template>
        <div class="igs-outro-stack">
            <p class="igs-outro-brand igs-stagger" style="--d: 0s">{{ brandName }}</p>
            <h2 class="igs-outro-hero igs-stagger" style="--d: 0.15s">THAT'S A WRAP</h2>
            <p class="igs-outro-sub igs-stagger" style="--d: 0.3s">{{ groomNick }} &amp; {{ brideNick }}</p>
            <p class="igs-outro-text igs-stagger" style="--d: 0.45s">{{ closingText }}</p>
            <div class="igs-outro-ctas igs-stagger" style="--d: 0.6s">
                <button type="button" class="igs-outro-replay" @click="emit('replay')" aria-label="Replay story">
                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" class="igs-boomerang">
                        <path d="M12 5V2L7 6l5 4V7c3.3 0 6 2.7 6 6s-2.7 6-6 6-6-2.7-6-6H4c0 4.4 3.6 8 8 8s8-3.6 8-8-3.6-8-8-8z" fill="currentColor"/>
                    </svg>
                    REPLAY STORY
                </button>
                <button type="button" class="igs-outro-share" @click="emit('share')" aria-label="Share invitation">
                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                        <path d="M14 3l7 7-7 7v-4c-4 0-7 1.5-9 5 1-7 5-10 9-10V3z" fill="currentColor"/>
                    </svg>
                    SHARE
                </button>
            </div>
            <div v-if="showWatermark" class="igs-outro-watermark">
                <BrandWatermark :height="16" :muted="true"/>
            </div>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-outro-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%);
}
.igs-outro-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
    flex: 1;
    justify-content: center;
    color: #FFFFFF;
}
.igs-outro-brand {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.01em;
}
.igs-outro-hero {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 40px;
    color: #FFFFFF;
    margin: 8px 0 0;
    letter-spacing: -0.04em;
    line-height: 1;
}
.igs-outro-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 24px;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.02em;
}
.igs-outro-text {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    color: rgba(255,255,255,0.85);
    line-height: 1.5;
    max-width: 320px;
    margin: 8px 0 0;
}
.igs-outro-ctas {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.igs-outro-replay,
.igs-outro-share {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    border-radius: 9999px;
    padding: 12px 18px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.12em;
    min-height: 44px;
    cursor: pointer;
}
.igs-outro-replay {
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #FFFFFF;
}
.igs-outro-share {
    background: #FFFFFF;
    color: #191919;
}
.igs-outro-watermark {
    margin-top: 20px;
    opacity: 0.6;
}
.igs-stagger {
    opacity: 0;
    transform: scale(0.95) translateY(6px);
    transition: opacity 0.5s ease-out var(--d, 0s), transform 0.5s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: scale(1) translateY(0);
}
.igs-boomerang {
    animation: igs-outro-boom 1.4s ease-in-out infinite alternate;
}
@keyframes igs-outro-boom {
    from { transform: rotate(-10deg); }
    to   { transform: rotate(10deg); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-boomerang { animation: none; transform: none; }
}
</style>
