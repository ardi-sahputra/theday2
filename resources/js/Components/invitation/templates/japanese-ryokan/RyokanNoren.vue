<script setup>
import { ref } from 'vue'
import RyokanSumiStroke from './RyokanSumiStroke.vue'

const props = defineProps({
    kanji:     { type: String, default: '寿' },
    groomNick: { type: String, default: '' },
    brideNick: { type: String, default: '' },
})
const emit = defineEmits(['open'])

const parting = ref(false)

function part() {
    if (parting.value) return
    parting.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('open'), reduced ? 250 : 1400)
}
</script>

<template>
    <div class="ryokan-noren-screen" :class="{ parting }">
        <div class="ryokan-noren-grain" aria-hidden="true" />

        <div class="ryokan-noren-stage">
            <p class="ryokan-noren-greet-jp">ようこそ</p>
            <p class="ryokan-noren-greet-en">Welcome</p>

            <button
                type="button"
                class="ryokan-noren-curtain"
                @click="part"
                :aria-label="parting ? 'Membuka undangan' : 'Buka undangan'"
            >
                <span class="ryokan-noren-half ryokan-noren-left">
                    <img src="/images/templates/japanese-ryokan/noren-left.svg" alt="" draggable="false" />
                </span>
                <span class="ryokan-noren-half ryokan-noren-right">
                    <img src="/images/templates/japanese-ryokan/noren-right.svg" alt="" draggable="false" />
                </span>
                <span class="ryokan-noren-kanji">{{ kanji }}</span>
            </button>

            <p v-if="groomNick || brideNick" class="ryokan-noren-couple">
                {{ groomNick }} <span class="ryokan-noren-amp">&amp;</span> {{ brideNick }}
            </p>

            <button type="button" class="ryokan-noren-cta" @click="part">
                <span>Buka Undangan</span>
                <RyokanSumiStroke :variant="3" :width="160" class="ryokan-noren-cta-stroke" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.ryokan-noren-screen {
    position: fixed;
    inset: 0;
    z-index: 40;
    background: #f3ede4;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.ryokan-noren-grain {
    position: absolute;
    inset: 0;
    background: url('/images/templates/japanese-ryokan/washi-grain.svg') repeat;
    background-size: 200px 200px;
    opacity: 0.4;
    pointer-events: none;
}
.ryokan-noren-stage {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    padding: 48px 24px;
    max-width: 480px;
    text-align: center;
}
.ryokan-noren-greet-jp {
    font-family: 'Sawarabi Mincho', 'Noto Serif JP', serif;
    color: #1c2e4a;
    font-size: 18px;
    margin: 0;
    letter-spacing: 0.15em;
}
.ryokan-noren-greet-en {
    font-family: 'Cormorant Garamond', serif;
    color: #1c2e4a;
    font-size: 14px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
}
.ryokan-noren-curtain {
    position: relative;
    width: 320px;
    height: 360px;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
}
@media (max-width: 480px) {
    .ryokan-noren-curtain { width: 260px; height: 300px; }
}
.ryokan-noren-half {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 50%;
    transition: transform 1.4s cubic-bezier(0.65, 0, 0.35, 1);
    will-change: transform;
}
.ryokan-noren-half img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: #1c2e4a;       /* fallback while placeholder PNG is solid */
    pointer-events: none;
}
.ryokan-noren-left  { left: 0;  transform-origin: left center; }
.ryokan-noren-right { right: 0; transform-origin: right center; }
.ryokan-noren-screen.parting .ryokan-noren-left  {
    transform: translateX(-110%) skewX(-2deg);
}
.ryokan-noren-screen.parting .ryokan-noren-right {
    transform: translateX( 110%) skewX( 2deg);
}
.ryokan-noren-kanji {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    font-size: 64px;
    color: #8c6b3f;
    pointer-events: none;
    text-shadow: 0 1px 0 rgba(0, 0, 0, 0.15);
}
.ryokan-noren-couple {
    font-family: 'Cormorant Garamond', serif;
    color: #1c2e4a;
    font-size: 22px;
    font-style: italic;
    margin: 4px 0 0;
}
.ryokan-noren-amp { color: #8c6b3f; }
.ryokan-noren-cta {
    margin-top: 8px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 8px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: #1c2e4a;
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
}
.ryokan-noren-cta-stroke { display: block; }
@media (prefers-reduced-motion: reduce) {
    .ryokan-noren-half { transition: none; }
}
</style>
