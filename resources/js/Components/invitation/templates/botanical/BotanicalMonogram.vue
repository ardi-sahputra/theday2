<template>
    <div class="bot-monogram" :style="{ width: `${size}px`, height: `${size}px`, '--mono-size': `${size}px` }">
        <BotanicalIllustration :slot="`flower-${flowerHis}`" class="bot-monogram__flower bot-monogram__flower--his"/>
        <span class="bot-monogram__text">{{ text }}</span>
        <BotanicalIllustration :slot="`flower-${flowerHer}`" class="bot-monogram__flower bot-monogram__flower--her"/>
    </div>
</template>

<script setup>
import BotanicalIllustration from './BotanicalIllustration.vue'

defineProps({
    text:      { type: String, required: true },
    flowerHis: { type: String, default: 'olive' },
    flowerHer: { type: String, default: 'peony' },
    size:      { type: Number, default: 96 },
})
</script>

<style scoped>
.bot-monogram {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.bot-monogram__text {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: calc(var(--mono-size, 96px) * 0.42);
    color: var(--bot-gold);
    letter-spacing: 0.02em;
    z-index: 2;
}
.bot-monogram__flower {
    position: absolute;
    top: 50%;
    width: 40%;
    height: 40%;
    transform: translateY(-50%);
    pointer-events: none;
}
.bot-monogram__flower--his { left: -8%; }
.bot-monogram__flower--her { right: -8%; transform: translateY(-50%) scaleX(-1); }
</style>
