<script setup>
import OnyxMonogram from './OnyxMonogram.vue'
import OnyxMarbleBg from './OnyxMarbleBg.vue'

defineProps({
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    monogramText: { type: String, default: 'A & B' },
    openingText:  { type: String, default: '' },
})
</script>

<template>
    <section class="onyx-hero onyx-section">
        <OnyxMarbleBg intensity="subtle"/>
        <div class="onyx-section-inner">
            <OnyxMonogram :text="monogramText" :size="120"/>
            <h2 class="onyx-hero-names">{{ groomName }} &amp; {{ brideName }}</h2>
            <span class="onyx-rule"/>
            <p v-if="openingText" class="onyx-hero-body">
                <span class="onyx-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
            </p>
        </div>
    </section>
</template>

<style scoped>
.onyx-hero {
    position: relative;
    padding: 80px 24px;
    text-align: center;
    color: #f5f5f0;
}
.onyx-section-inner {
    position: relative; z-index: 1;
    max-width: 560px;
    margin: 0 auto;
    display: flex; flex-direction: column;
    align-items: center; gap: 20px;
}
.onyx-hero-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-weight: 400;
    color: #f5f5f0;
    font-size: 32px;
    margin: 0;
    line-height: 1.2;
}
.onyx-rule { display: block; width: 60px; height: 1px; background: #d4af37; }
.onyx-hero-body {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #f5f5f0;
    font-size: 18px;
    line-height: 1.85;
    text-align: left;
    margin: 0;
}
.onyx-dropcap {
    float: left;
    font-size: 56px;
    line-height: 1;
    color: #d4af37;
    margin: 4px 12px 0 0;
    font-style: italic;
}
@media (min-width: 768px) {
    .onyx-hero { padding: 96px 48px; }
    .onyx-hero-names { font-size: 36px; }
}
@media (prefers-reduced-motion: reduce) {
    /* OnyxMarbleBg handles its own reduced-motion */
}
</style>
