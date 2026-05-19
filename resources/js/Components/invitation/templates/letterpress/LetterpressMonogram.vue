<script setup>
defineProps({
    text: { type: String, default: 'A & B' },
    size: { type: Number, default: 96 },
})
</script>

<template>
    <h2 class="lp-monogram" :style="{ '--lp-monogram-size': size + 'px' }">{{ text }}</h2>
</template>

<style scoped>
.lp-monogram {
    font-family: 'Playfair Display', 'Cormorant Garamond', Georgia, serif;
    font-size: var(--lp-monogram-size, 96px);
    line-height: 1;
    color: var(--lp-ink, #1a1a1a);
    letter-spacing: 0.08em;
    text-align: center;
    margin: 0;
    text-shadow:
        1px 1px 0 rgba(255,255,255,0.85),
        -1px -1px 1px rgba(0,0,0,0.15),
        0 0 2px rgba(0,0,0,0.06);
}
</style>
