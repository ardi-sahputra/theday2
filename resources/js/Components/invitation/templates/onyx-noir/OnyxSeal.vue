<script setup>
import { ref } from 'vue'
import OnyxMarbleBg from './OnyxMarbleBg.vue'

defineProps({
    guestName:    { type: String, default: 'Tamu Undangan' },
    monogramText: { type: String, default: 'A & B' },
    motif:        { type: String, default: 'geometric' },
})
const emit = defineEmits(['proceed'])

const cracked = ref(false)

function crack() {
    if (cracked.value) return
    cracked.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('proceed'), reduced ? 300 : 1600)
}
</script>

<template>
    <div class="onyx-seal-screen">
        <OnyxMarbleBg intensity="subtle"/>
        <div class="onyx-seal-stage">
            <p class="onyx-seal-eyebrow">UNDANGAN PERNIKAHAN</p>

            <button
                type="button"
                class="onyx-seal-wrap"
                :class="{ 'onyx-seal--cracked': cracked }"
                @click="crack"
                :aria-label="cracked ? 'Membuka segel' : 'Tap untuk membuka segel'"
            >
                <span class="onyx-seal-half onyx-seal-half--left">
                    <img src="/images/templates/onyx-noir/wax-seal.svg" alt="" draggable="false"/>
                </span>
                <span class="onyx-seal-half onyx-seal-half--right">
                    <img src="/images/templates/onyx-noir/wax-seal.svg" alt="" draggable="false"/>
                </span>
                <span class="onyx-seal-monogram">{{ monogramText }}</span>
            </button>

            <p class="onyx-seal-greet">Kepada Yang Terhormat,</p>
            <p class="onyx-seal-guest">{{ guestName }}</p>

            <button type="button" class="onyx-btn onyx-seal-cta" @click="crack">
                BUKA SEGEL
            </button>
        </div>
    </div>
</template>

<style scoped>
.onyx-seal-screen {
    position: fixed; inset: 0; z-index: 40;
    background: #0a0a0a;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.onyx-seal-stage {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center;
    gap: 24px; padding: 48px 24px;
    max-width: 480px; text-align: center;
}
.onyx-seal-eyebrow {
    font-family: 'Tenor Sans', sans-serif;
    color: #d4af37;
    font-size: 12px;
    letter-spacing: 0.4em;
    margin: 0 0 8px;
}
.onyx-seal-wrap {
    position: relative;
    width: 256px; height: 256px;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
}
@media (max-width: 480px) {
    .onyx-seal-wrap { width: 200px; height: 200px; }
}
.onyx-seal-half {
    position: absolute; inset: 0;
    display: block;
    transition: transform 1.2s cubic-bezier(0.7, 0, 0.84, 0),
                opacity 0.4s ease-out 1.2s;
}
.onyx-seal-half img {
    width: 100%; height: 100%; object-fit: contain;
    pointer-events: none;
}
.onyx-seal-half--left  {
    clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
    transform-origin: right center;
}
.onyx-seal-half--right {
    clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);
    transform-origin: left center;
}
.onyx-seal--cracked .onyx-seal-half--left  { transform: translateX(-40px) rotate(-12deg); opacity: 0; }
.onyx-seal--cracked .onyx-seal-half--right { transform: translateX(40px)  rotate(12deg);  opacity: 0; }
.onyx-seal-monogram {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 36px;
    color: #d4af37;
    text-shadow: 0 1px 0 rgba(0,0,0,0.6), 0 0 12px rgba(212,175,55,0.25);
    pointer-events: none;
}
.onyx-seal-greet {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #f5f5f0;
    font-size: 18px;
    margin: 16px 0 0;
}
.onyx-seal-guest {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #d4af37;
    font-size: 22px;
    margin: 0;
}
.onyx-btn {
    display: inline-block;
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: #d4af37;
    font-family: 'Tenor Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid #d4af37;
    cursor: pointer;
    transition: color 0.3s ease, background 0.3s ease;
}
.onyx-btn:hover { background: #d4af37; color: #0a0a0a; }
.onyx-seal-cta { margin-top: 8px; }
@media (prefers-reduced-motion: reduce) {
    .onyx-seal-half { transition: opacity 0.2s ease; }
    .onyx-seal--cracked .onyx-seal-half { transform: none; opacity: 0; }
    .onyx-btn { transition: none; }
}
</style>
