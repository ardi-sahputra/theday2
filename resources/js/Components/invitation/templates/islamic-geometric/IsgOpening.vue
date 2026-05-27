<script setup>
import { ref, onMounted } from 'vue'
import IsgKhatam      from './IsgKhatam.vue'
import IsgArabesqueBg from './IsgArabesqueBg.vue'

defineProps({
    showTranslation: { type: Boolean, default: true },
})
const emit = defineEmits(['proceed'])

const dividerOn     = ref(false)
const subOn         = ref(false)
const reducedMotion = ref(false)

// Bismillah - exact Unicode from spec Appendix
const bismillahText = 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'

onMounted(() => {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion.value) {
        dividerOn.value = true
        subOn.value = true
        setTimeout(() => emit('proceed'), 1200)
        return
    }
    setTimeout(() => { dividerOn.value = true }, 1200)
    setTimeout(() => { subOn.value = true   }, 1300)
    setTimeout(() => emit('proceed'),          1600)
})

function skip() { emit('proceed') }
</script>

<template>
    <div class="isg-opening" @click="skip">
        <IsgArabesqueBg intensity="subtle" class="isg-opening-bg" />
        <div class="isg-opening-stage">
            <IsgKhatam :size="200" animated class="isg-opening-khatam" />

            <p class="isg-bismillah" dir="rtl" lang="ar">{{ bismillahText }}</p>

            <span class="isg-opening-divider" :class="{ 'isg-divider-drawn': dividerOn }"></span>

            <p class="isg-opening-translation" :class="{ 'isg-translation-shown': subOn }">
                In the name of Allah, the Most Gracious, the Most Merciful
            </p>
        </div>
    </div>
</template>

<style scoped>
.isg-opening {
    position: fixed; inset: 0; z-index: 40;
    min-height: 100dvh;
    display: grid; place-items: center;
    background: var(--isg-ivory, #f5efe3);
    cursor: pointer;
    overflow: hidden;
}
.isg-opening-bg { position: absolute; inset: 0; }
.isg-opening-stage {
    position: relative;
    text-align: center;
    padding: 24px;
    max-width: 480px;
    z-index: 1;
}
.isg-opening-khatam {
    color: var(--isg-gold, #c9a961);
    width: clamp(160px, 24vw, 200px);
    height: clamp(160px, 24vw, 200px);
    margin-bottom: 32px;
}

.isg-bismillah {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: clamp(22px, 4vw, 28px);
    color: var(--isg-emerald, #0e4d3d);
    direction: rtl;
    line-height: 1.8;
    opacity: 0;
    clip-path: inset(0 100% 0 0);
    animation: isg-bismillah-reveal 1000ms ease-out 400ms forwards;
    margin: 0;
}
@keyframes isg-bismillah-reveal {
    0%   { opacity: 0; clip-path: inset(0 100% 0 0); }
    20%  { opacity: 1; clip-path: inset(0 100% 0 0); }
    100% { opacity: 1; clip-path: inset(0 0 0 0); }
}

.isg-opening-divider {
    display: inline-block;
    width: 60px;
    height: 1px;
    background: var(--isg-gold, #c9a961);
    margin: 24px 0;
    transform: scaleX(0);
    transition: transform 400ms ease-out;
}
.isg-divider-drawn { transform: scaleX(1); }

.isg-opening-translation {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted, #6b6b6b);
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 300ms ease-out, transform 300ms ease-out;
    margin: 0;
}
.isg-translation-shown { opacity: 1; transform: none; }

@media (prefers-reduced-motion: reduce) {
    .isg-bismillah { animation: none; opacity: 1; clip-path: none; }
    .isg-opening-divider { transform: scaleX(1); transition: none; }
    .isg-opening-translation { opacity: 1; transform: none; transition: none; }
}
</style>
