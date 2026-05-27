<template>
    <section class="bot-section bot-hero">
        <div class="bot-section-inner">
            <div class="bot-hero__wreath">
                <BotanicalWreathSvg/>
            </div>
            <header class="bot-section-header">
                <span class="bot-rule" aria-hidden="true"/>
                <h2 class="bot-section-title">{{ openingLabel }}</h2>
                <span class="bot-rule" aria-hidden="true"/>
            </header>
            <p v-if="openingText" class="bot-hero__body">
                <span class="bot-hero__dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
            </p>
        </div>
    </section>
</template>

<script setup>
import BotanicalWreathSvg from './BotanicalWreathSvg.vue'

defineProps({
    openingText:  { type: String, default: '' },
    openingLabel: { type: String, default: 'PROLOG' },
})
</script>

<style scoped>
.bot-section { position: relative; padding: 56px 24px; }
.bot-section-inner { max-width: 560px; margin: 0 auto; text-align: center; }
.bot-hero__wreath { width: 96px; height: 96px; margin: 0 auto 24px; }
.bot-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 16px; margin-bottom: 32px;
}
.bot-rule { flex: 0 0 32px; height: 1px; background: var(--bot-sage); opacity: 0.6; }
.bot-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--bot-sage-deep);
    margin: 0;
}
.bot-hero__body {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 18px;
    line-height: 1.85;
    text-align: left;
    margin: 0;
}
.bot-hero__dropcap {
    float: left;
    font-size: 48px;
    line-height: 1;
    color: var(--bot-sage-deep);
    margin: 4px 12px 0 0;
    font-style: italic;
}
@media (min-width: 768px) { .bot-section { padding: 96px 48px; } }
</style>
