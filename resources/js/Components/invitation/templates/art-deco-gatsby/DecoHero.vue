<script setup>
import DecoSectionHeader from './DecoSectionHeader.vue'
import DecoSunburst      from './DecoSunburst.vue'

defineProps({
    openingText: { type: String, default: '' },
    quoteText:   { type: String, default: '' },
    monogram:    { type: String, default: '' },
    year:        { type: [String, Number], default: '' },
    rays:        { type: Number, default: 12 },
})
</script>

<template>
    <section class="deco-section deco-hero deco-reveal deco-visible">
        <div class="deco-hero-watermark" aria-hidden="true">
            <DecoSunburst :rays="rays" :size="120" :animated="false"/>
        </div>
        <DecoSectionHeader title="THE ANNOUNCEMENT"/>
        <p v-if="quoteText" class="deco-hero-quote">"{{ quoteText }}"</p>
        <p v-if="openingText" class="deco-hero-body">{{ openingText }}</p>
        <div class="deco-hero-footer">
            <span class="deco-hero-dot">·</span>
            <p class="deco-hero-est">EST. {{ year }}</p>
        </div>
    </section>
</template>

<style scoped>
.deco-hero {
    position: relative;
    padding: 64px 24px 56px;
    color: #f4ead5;
    background: #0d0d0d;
    overflow: hidden;
}
.deco-hero-watermark {
    position: absolute; top: 50%; left: 50%;
    width: 320px; height: 320px;
    transform: translate(-50%, -50%);
    color: #c9a961; opacity: 0.08;
    pointer-events: none;
}
.deco-hero-quote {
    position: relative; z-index: 1;
    font-family: 'Cormorant Garamond', serif;
    font-style: italic; font-size: 18px;
    color: #f4ead5; text-align: center;
    margin: 0 0 24px; line-height: 1.5;
}
.deco-hero-body {
    position: relative; z-index: 1;
    font-family: 'Lato', system-ui, sans-serif;
    font-size: 15px; line-height: 1.8;
    color: rgba(244,234,213,0.85);
    text-align: center; margin: 0 0 28px;
    white-space: pre-line;
}
.deco-hero-footer {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    color: #c9a961;
}
.deco-hero-dot { font-size: 22px; line-height: 1; }
.deco-hero-est {
    margin: 0; font-size: 12px; letter-spacing: 0.4em;
    font-family: 'Lato', system-ui, sans-serif;
    color: rgba(201,169,97,0.85);
}
</style>
