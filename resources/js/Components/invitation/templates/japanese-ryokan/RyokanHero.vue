<script setup>
import RyokanSumiStroke    from './RyokanSumiStroke.vue'
import RyokanSectionHeader from './RyokanSectionHeader.vue'

defineProps({
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    openingText:  { type: String, default: '' },
    kanjiHeaders: { type: Boolean, default: true },
    kanjiDict:    { type: Object,  default: () => ({}) },
    fujiVisible:  { type: Boolean, default: false },
})
</script>

<template>
    <section class="ryokan-hero ryokan-section">
        <img
            v-if="fujiVisible"
            src="/images/templates/japanese-ryokan/fuji-silhouette.svg"
            alt=""
            class="ryokan-hero-fuji"
            aria-hidden="true"
        />
        <div class="ryokan-section-inner">
            <RyokanSectionHeader
                title="Opening"
                :kanji="kanjiDict.opening || '序'"
                :show-kanji="kanjiHeaders"
                :variant="1"
            />
            <h1 class="ryokan-hero-names">
                {{ groomName }} <span class="ryokan-hero-amp">&amp;</span> {{ brideName }}
            </h1>
            <RyokanSumiStroke :variant="4" :width="280" class="ryokan-hero-stroke" />
            <p v-if="openingText" class="ryokan-hero-body">{{ openingText }}</p>
        </div>
    </section>
</template>

<style scoped>
.ryokan-hero {
    position: relative;
    padding: 96px 24px 80px;
    overflow: hidden;
    text-align: center;
}
.ryokan-hero-fuji {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    opacity: 0.25;
    pointer-events: none;
    z-index: 0;
}
.ryokan-section-inner {
    position: relative;
    z-index: 1;
    max-width: 600px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
}
.ryokan-hero-names {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', 'Cormorant Garamond', serif;
    font-weight: 400;
    color: #1c2e4a;
    font-size: 32px;
    margin: 0;
    line-height: 1.3;
}
.ryokan-hero-amp { color: #8c6b3f; font-style: italic; }
.ryokan-hero-body {
    font-family: 'Noto Sans JP', 'Inter', sans-serif;
    color: #2d2d2d;
    font-size: 16px;
    line-height: 1.9;
    margin: 0;
    max-width: 480px;
    white-space: pre-line;
}
@media (min-width: 768px) {
    .ryokan-hero { padding: 112px 48px 96px; }
    .ryokan-hero-names { font-size: 38px; }
}
</style>
