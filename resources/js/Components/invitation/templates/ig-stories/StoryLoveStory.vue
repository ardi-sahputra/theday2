<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'
import StoryFrame from './StoryFrame.vue'

const props = defineProps({
    stories: { type: Array, default: () => [] },
})

const visible = computed(() => props.stories.slice(0, 3))
</script>

<template>
    <StoryFrame story-key="love_story" story-theme="dark">
        <template #backdrop>
            <div class="igs-love-bg"/>
        </template>
        <div class="igs-love-stack">
            <p class="igs-love-eye igs-stagger" style="--d: 0s">OUR JOURNEY</p>
            <h2 class="igs-love-title igs-stagger" style="--d: 0.1s">HOW WE STARTED</h2>
            <div class="igs-love-deck">
                <article
                    v-for="(s, i) in visible"
                    :key="i"
                    class="igs-love-card"
                    :class="{ 'igs-love-card--top': i === 0, 'igs-boomerang': i === 0 }"
                    :style="`--card-idx: ${i}; --d: ${0.1 + i * 0.15}s`"
                >
                    <p class="igs-love-card-date">{{ s.date }}</p>
                    <p class="igs-love-card-title">{{ s.title }}</p>
                    <p class="igs-love-card-desc">{{ (s.description || '').slice(0, 80) }}{{ (s.description || '').length > 80 ? '…' : '' }}</p>
                </article>
            </div>
            <p class="igs-love-hint igs-stagger" style="--d: 0.6s">TAP →</p>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-love-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(170deg, #fbc2eb 0%, #a18cd1 100%);
}
.igs-love-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    flex: 1;
}
.igs-love-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
}
.igs-love-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 28px;
    color: #FFFFFF;
    margin: 0 0 12px;
    letter-spacing: -0.02em;
}
.igs-love-deck {
    position: relative;
    width: 100%;
    max-width: 320px;
    height: 200px;
    margin: 8px auto;
}
.igs-love-card {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.95);
    border-radius: 12px;
    padding: 16px;
    color: #191919;
    text-align: left;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    opacity: 0;
    transform: translateY(20px) scale(0.95) translateX(calc(var(--card-idx) * 12px));
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-love-card {
    opacity: 1;
    transform: translateY(0) scale(calc(1 - var(--card-idx) * 0.04)) translateX(calc(var(--card-idx) * 12px));
}
.igs-love-card--top { z-index: 3; }
.igs-love-card:nth-child(2) { z-index: 2; opacity: 0.85; }
.igs-love-card:nth-child(3) { z-index: 1; opacity: 0.65; }
.igs-love-card-date {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: #6b6b6b;
    margin: 0 0 4px;
    text-transform: uppercase;
}
.igs-love-card-title {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 18px;
    color: #191919;
    margin: 0 0 6px;
    letter-spacing: -0.01em;
}
.igs-love-card-desc {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    line-height: 1.5;
    color: #4a4a4a;
    margin: 0;
}
.igs-love-hint {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: rgba(255,255,255,0.85);
    margin: 0;
    align-self: flex-end;
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
    animation: igs-boomerang 2.4s ease-in-out infinite alternate;
}
@keyframes igs-boomerang {
    from { translate: 0 -3px; }
    to   { translate: 0  3px; }
}
@media (prefers-reduced-motion: reduce) {
    .igs-love-card, .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-love-card:nth-child(2) { opacity: 0.85; }
    .igs-love-card:nth-child(3) { opacity: 0.65; }
    .igs-boomerang { animation: none; }
}
</style>
