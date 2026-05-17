<script setup>
import RyokanSumiStroke from './RyokanSumiStroke.vue'
import RyokanTategaki   from './RyokanTategaki.vue'

defineProps({
    title:     { type: String, required: true },
    kanji:     { type: String, default: '' },
    showKanji: { type: Boolean, default: true },
    variant:   { type: Number, default: 1 },   // sumi stroke variant 1-5
})
</script>

<template>
    <header class="ryokan-section-header">
        <RyokanTategaki
            v-if="showKanji && kanji"
            :text="kanji"
            :size="18"
            color="#1c2e4a"
            :revealed="true"
            class="ryokan-section-kanji"
        />
        <h2 class="ryokan-section-title">{{ title }}</h2>
        <RyokanSumiStroke :variant="variant" :width="220" class="ryokan-section-stroke" />
    </header>
</template>

<style scoped>
.ryokan-section-header {
    position: relative;
    text-align: center;
    margin: 0 auto 40px;
    padding: 0 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}
.ryokan-section-kanji {
    position: absolute;
    left: 4px;
    top: 8px;
    opacity: 0.55;
}
.ryokan-section-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-variant: small-caps;
    font-weight: 500;
    color: #1c2e4a;
    font-size: 22px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin: 0;
}
@media (min-width: 768px) {
    .ryokan-section-title { font-size: 28px; letter-spacing: 0.4em; }
}
@media (max-width: 480px) {
    /* On cramped mobile, hide the tategaki kanji to avoid layout cramping. */
    .ryokan-section-kanji { display: none; }
}
</style>
