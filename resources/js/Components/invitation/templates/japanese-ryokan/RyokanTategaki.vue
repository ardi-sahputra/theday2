<script setup>
defineProps({
    text:     { type: String, required: true },
    size:     { type: [Number, String], default: 14 },
    color:    { type: String, default: '#1c2e4a' },
    revealed: { type: Boolean, default: false },
})
</script>

<template>
    <div
        class="ryokan-tategaki"
        :class="{ 'is-revealed': revealed }"
        :style="{
            fontSize: (typeof size === 'number' ? size + 'px' : size),
            color,
        }"
    >{{ text }}</div>
</template>

<style scoped>
.ryokan-tategaki {
    writing-mode: vertical-rl;
    text-orientation: upright;
    font-family: 'Sawarabi Mincho', 'Shippori Mincho B1', 'Noto Serif JP', serif;
    letter-spacing: 0.2em;
    line-height: 1.6;
    clip-path: inset(0 0 0 0);
    transition: clip-path 1.5s cubic-bezier(0.65, 0, 0.35, 1);
}
.ryokan-tategaki:not(.is-revealed) {
    clip-path: inset(0 0 100% 0);
}
@media (prefers-reduced-motion: reduce) {
    .ryokan-tategaki,
    .ryokan-tategaki:not(.is-revealed) {
        clip-path: none;
        transition: none;
    }
}
</style>
