<script setup>
import IsgKhatam from './IsgKhatam.vue'

defineProps({
    openingText: { type: String, default: '' },
})
</script>

<template>
    <section class="isg-section isg-opening-sect">
        <IsgKhatam :size="48" class="isg-section-orn" />
        <p class="isg-section-label">MUTIARA HIKMAH</p>
        <p v-if="openingText" class="isg-prose">{{ openingText }}</p>
    </section>
</template>

<style scoped>
.isg-section {
    position: relative;
    padding: 64px 24px;
    text-align: center;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 768px) { .isg-section { padding: 96px 56px; } }
.isg-section-orn {
    color: var(--isg-gold, #c9a961);
    display: block;
    margin: 0 auto 16px;
}
.isg-section-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-gold, #c9a961);
    margin: 0 0 24px;
}
.isg-prose {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--isg-ink, #0a0a0a);
    line-height: 1.85;
    max-width: 560px;
    margin: 0 auto;
}
</style>
