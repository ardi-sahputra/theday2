<script setup>
import LetterpressDivider from './LetterpressDivider.vue'

defineProps({
    monogramText: { type: String, default: 'A & B' },
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    fullDate:     { type: String, default: '' },
    venueName:    { type: String, default: '' },
    fontTitle:    { type: String, default: 'Playfair Display' },
})
const emit = defineEmits(['open'])
</script>

<template>
    <div class="lp-cover">
        <div class="lp-cover-frame">
            <p class="lp-cover-label lp-stagger" style="--d: 0.05s">THE WEDDING OF</p>
            <h1 class="lp-cover-name lp-stagger" :style="{ fontFamily: fontTitle, '--d': '0.15s' }">{{ groomName }}</h1>
            <span class="lp-cover-amp lp-stagger" style="--d: 0.25s">&amp;</span>
            <h1 class="lp-cover-name lp-stagger" :style="{ fontFamily: fontTitle, '--d': '0.35s' }">{{ brideName }}</h1>
            <LetterpressDivider class="lp-stagger" style="--d: 0.45s" />
            <p class="lp-cover-date  lp-stagger" style="--d: 0.55s">{{ fullDate }}</p>
            <p v-if="venueName" class="lp-cover-venue lp-stagger" style="--d: 0.62s">{{ venueName }}</p>
            <button class="lp-btn lp-stagger" style="--d: 0.75s" @click="emit('open')">BUKA UNDANGAN</button>
        </div>
    </div>
</template>

<style scoped>
.lp-cover {
    position: fixed; inset: 0; z-index: 30;
    background: var(--lp-paper, #f9f6f0);
    display: grid; place-items: center;
    padding: 32px;
    overflow: hidden;
}
.lp-cover-frame {
    width: 100%;
    max-width: 560px;
    border: 1px solid var(--lp-gold, #c9a961);
    outline: 1px solid var(--lp-gold, #c9a961);
    outline-offset: 4px;
    padding: 56px 32px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.lp-stagger {
    opacity: 0;
    transform: translateY(14px);
    animation: lp-rise 700ms cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes lp-rise { to { opacity: 1; transform: none; } }

.lp-cover-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted, #666);
    margin: 0 0 16px;
}
.lp-cover-name {
    font-size: clamp(36px, 8vw, 56px);
    color: var(--lp-ink, #1a1a1a);
    letter-spacing: 0.04em;
    line-height: 1.1;
    margin: 0;
}
.lp-cover-amp {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 32px;
    color: var(--lp-gold, #c9a961);
    margin: 4px 0;
}
.lp-cover-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink, #1a1a1a);
    margin: 12px 0 4px;
}
.lp-cover-venue {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--lp-ink-muted, #666);
    margin: 0 0 24px;
}
.lp-btn {
    margin-top: 16px;
    background: transparent;
    color: var(--lp-ink, #1a1a1a);
    border: 1px solid var(--lp-gold, #c9a961);
    padding: 14px 32px;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 200ms ease-out, color 200ms ease-out, border-color 200ms ease-out;
}
.lp-btn:hover {
    background: var(--lp-gold, #c9a961);
    color: var(--lp-paper, #f9f6f0);
}
.lp-btn:focus-visible { outline: 2px solid var(--lp-gold, #c9a961); outline-offset: 2px; }
@media (prefers-reduced-motion: reduce) {
    .lp-stagger { animation: none; opacity: 1; transform: none; }
    .lp-btn { transition: none; }
}
</style>
