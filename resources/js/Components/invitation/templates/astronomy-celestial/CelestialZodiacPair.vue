<script setup>
import { computed } from 'vue'

const props = defineProps({
    groomSign: { type: String, default: null },
    brideSign: { type: String, default: null },
})

const medallions = computed(() => {
    const out = []
    if (props.groomSign) out.push({ side: 'groom', sign: props.groomSign, delay: 0 })
    if (props.brideSign) out.push({ side: 'bride', sign: props.brideSign, delay: 0.3 })
    return out
})
</script>

<template>
    <div class="ac-zodiac-pair">
        <div
            v-for="m in medallions"
            :key="m.side"
            class="ac-zodiac-medallion"
            :style="{ '--ac-z-delay': m.delay + 's' }"
        >
            <svg viewBox="0 0 64 64" class="ac-zodiac-glyph" aria-hidden="true">
                <use :href="`/images/templates/astronomy-celestial/zodiac.svg#sign-${m.sign}`"/>
            </svg>
        </div>
    </div>
</template>

<style scoped>
.ac-zodiac-pair {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
}
.ac-zodiac-medallion {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: var(--ac-navy-panel, #1a2e4a);
    border: 1px solid var(--ac-gold, #d4af37);
    box-shadow: 0 0 24px rgba(212,175,55,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ac-gold, #d4af37);
    opacity: 0;
    transform: rotate(-45deg) scale(0.8);
    animation: ac-z-in 1s cubic-bezier(0.5, 1.5, 0.5, 1) forwards;
    animation-delay: var(--ac-z-delay, 0s);
}
.ac-zodiac-glyph {
    width: 48px;
    height: 48px;
}
@keyframes ac-z-in {
    to { opacity: 1; transform: rotate(0) scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .ac-zodiac-medallion {
        animation: none;
        opacity: 1;
        transform: none;
    }
}
@media (max-width: 480px) {
    .ac-zodiac-medallion { width: 64px; height: 64px; }
    .ac-zodiac-glyph { width: 36px; height: 36px; }
}
</style>
