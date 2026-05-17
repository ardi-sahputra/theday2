<script setup>
import TuscanyOliveDivider from './TuscanyOliveDivider.vue'

defineProps({
    groomName:      { type: String, default: '' },
    brideName:      { type: String, default: '' },
    firstEventDate: { type: String, default: '' },
    quoteText:      { type: String, default: '' },
    italianOn:      { type: Boolean, default: true },
})
</script>

<template>
    <section class="tv-hero">
        <div class="tv-hero-inner">
            <div class="tv-hero-eyebrow">
                <span class="tv-rule"/>
                <span v-if="italianOn" class="tv-hero-italian">L'AMORE</span>
                <span class="tv-rule"/>
            </div>
            <p class="tv-hero-sub">Cinta</p>

            <h1 class="tv-hero-names">
                <span>{{ groomName }}</span>
                <span class="tv-hero-amp">&amp;</span>
                <span>{{ brideName }}</span>
            </h1>

            <TuscanyOliveDivider :width="180" class="tv-hero-divider"/>

            <span v-if="firstEventDate" class="tv-hero-date-pill">{{ firstEventDate }}</span>

            <p v-if="quoteText" class="tv-hero-quote">&ldquo; {{ quoteText }} &rdquo;</p>
        </div>
    </section>
</template>

<style scoped>
.tv-hero {
    position: relative;
    padding: 96px 24px 64px;
    text-align: center;
}
.tv-hero-inner {
    max-width: 720px; margin: 0 auto;
    display: flex; flex-direction: column; align-items: center;
    gap: 16px;
}
.tv-hero-eyebrow {
    display: flex; align-items: center; gap: 12px;
}
.tv-rule { display: block; width: 32px; height: 1px; background: #c97b4a; }
.tv-hero-italian {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    color: #722f2f;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
}
.tv-hero-sub {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    color: rgba(58,42,28,0.75);
    font-size: 14px;
    margin: 0;
}
.tv-hero-names {
    font-family: 'Italianno', cursive;
    color: #722f2f;
    font-size: 96px;
    line-height: 1.05;
    margin: 0;
    display: flex; flex-direction: column; align-items: center;
}
@media (min-width: 768px) { .tv-hero-names { font-size: 120px; } }
.tv-hero-amp {
    color: #c97b4a;
    font-size: 0.7em;
}
.tv-hero-divider { margin: 8px 0; color: #8b9d6f; }
.tv-hero-date-pill {
    display: inline-block;
    background: #8b9d6f;
    color: #f4e4c1;
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    padding: 8px 18px;
    border-radius: 999px;
}
.tv-hero-quote {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(58,42,28,0.75);
    font-size: 18px;
    line-height: 1.6;
    max-width: 480px;
    margin: 8px 0 0;
}
</style>
