<script setup>
defineProps({
    text: { type: String, default: 'A & B' },
    size: { type: Number, default: 96 },
})
</script>

<template>
    <span
        class="onyx-monogram"
        :style="{ fontSize: size + 'px', lineHeight: 1 }"
    >{{ text }}</span>
</template>

<style scoped>
.onyx-monogram {
    display: inline-block;
    font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    background-image: linear-gradient(110deg,
        #b8941f 0%,
        #d4af37 45%,
        #f3e5a0 50%,
        #d4af37 55%,
        #b8941f 100%);
    background-size: 200% 100%;
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    animation: onyx-shimmer 2.4s ease-in-out infinite;
    letter-spacing: 0.04em;
}
@keyframes onyx-shimmer {
    0%   { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
@media (prefers-reduced-motion: reduce) {
    .onyx-monogram { animation: none; background-position: 0 0; }
}
</style>
