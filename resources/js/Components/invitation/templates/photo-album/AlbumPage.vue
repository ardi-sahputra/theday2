<script setup>
const props = defineProps({
    side:       { type: String, default: 'single' },   // left|right|single
    pageNumber: { type: [Number, String], default: '' },
    ariaLabel:  { type: String, default: null },
})
</script>

<template>
    <article
        class="pa-page"
        :class="[`pa-page--${side}`]"
        :aria-label="ariaLabel"
    >
        <div class="pa-page-body">
            <slot/>
        </div>
        <span v-if="pageNumber !== ''" class="pa-page-number" :class="`pa-page-number--${side}`">
            {{ pageNumber }}
        </span>
    </article>
</template>

<style scoped>
.pa-page {
    position: relative;
    background-color: #1a1410;
    background-image: url('/images/templates/photo-album/black-paper.webp');
    background-size: 600px 600px;
    background-repeat: repeat;
    color: #f4ead5;
    padding: 28px 22px;
    min-height: 100%;
    box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.65);
    overflow: hidden;
}
.pa-page--left  { box-shadow: inset -16px 0 32px rgba(0, 0, 0, 0.55), inset 0 0 60px rgba(0, 0, 0, 0.65); }
.pa-page--right { box-shadow: inset  16px 0 32px rgba(0, 0, 0, 0.55), inset 0 0 60px rgba(0, 0, 0, 0.65); }

.pa-page-body { position: relative; z-index: 10; min-height: 100%; }

.pa-page-number {
    position: absolute;
    bottom: 12px;
    font-family: 'Cormorant SC', serif;
    font-style: italic;
    font-size: 13px;
    color: #d4a574;
    letter-spacing: 2px;
    z-index: 11;
}
.pa-page-number--left   { left:  18px; }
.pa-page-number--right  { right: 18px; }
.pa-page-number--single { right: 18px; }

@media (min-width: 1024px) {
    .pa-page { padding: 48px 40px; min-height: 720px; }
}
</style>
