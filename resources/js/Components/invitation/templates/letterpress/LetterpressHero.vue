<script setup>
import LetterpressDivider from './LetterpressDivider.vue'

defineProps({
    openingText: { type: String, default: '' },
})
</script>

<template>
    <section class="lp-section lp-opening-sect">
        <LetterpressDivider />
        <p class="lp-section-label">PROLOG</p>
        <p v-if="openingText" class="lp-prose">
            <span class="lp-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
        </p>
    </section>
</template>

<style scoped>
.lp-section {
    position: relative;
    padding: 56px 24px;
    text-align: center;
}
@media (min-width: 768px) { .lp-section { padding: 96px 56px; } }
.lp-section-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted, #666);
    margin: 0 0 24px;
}
.lp-prose {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink, #1a1a1a);
    line-height: 1.85;
    max-width: 560px;
    margin: 0 auto;
    text-align: left;
}
.lp-dropcap {
    float: left;
    font-family: 'Playfair Display', serif;
    font-size: 48px;
    color: var(--lp-gold, #c9a961);
    line-height: 1;
    margin: 4px 12px 0 0;
}
</style>
