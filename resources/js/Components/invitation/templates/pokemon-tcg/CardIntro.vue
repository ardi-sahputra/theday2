<script setup>
import { ref } from 'vue'

defineProps({
    guestName:     { type: String, default: 'Tamu Undangan' },
    holoIntensity: { type: Number, default: 0.55 },
})
const emit = defineEmits(['proceed'])

const flipped = ref(false)

function flip() {
    if (flipped.value) return
    flipped.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('proceed'), reduced ? 280 : 1200)
}
</script>

<template>
    <div class="tcg-intro-screen">
        <p class="tcg-intro-eyebrow">UNDANGAN PERNIKAHAN</p>

        <button
            type="button"
            class="tcg-card-flip"
            :class="{ 'tcg-card-flip--flipped': flipped }"
            @click="flip"
            :aria-label="flipped ? 'Membuka kartu' : 'Ketuk kartu untuk membuka'"
        >
            <span class="tcg-card-face tcg-card-back">
                <img
                    src="/images/templates/pokemon-tcg/card-back.svg"
                    alt=""
                    draggable="false"
                />
            </span>
            <span class="tcg-card-face tcg-card-front">
                <span class="tcg-card-front-mono">T</span>
                <span class="tcg-card-front-label">THEDAY &middot; LEGENDARY EDITION</span>
            </span>
        </button>

        <p class="tcg-intro-hint">Ketuk kartu untuk membuka</p>
        <p class="tcg-intro-guest">Kepada: <strong>{{ guestName }}</strong></p>

        <button type="button" class="tcg-intro-cta" @click="flip">FLIP CARD</button>
    </div>
</template>

<style scoped>
.tcg-intro-screen {
    position: fixed; inset: 0; z-index: 40;
    background: #1A1F3A;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 18px;
    padding: 32px 20px;
    overflow: hidden;
}
.tcg-intro-eyebrow {
    margin: 0 0 8px;
    font-family: 'Cinzel', serif;
    color: #F4F1E6;
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
}
.tcg-card-flip {
    position: relative;
    width: 260px;
    height: 364px;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    transform-style: preserve-3d;
    transition: transform 1.2s cubic-bezier(0.65, 0, 0.35, 1);
    transform: rotateY(180deg);
}
.tcg-card-flip--flipped { transform: rotateY(0deg); }
.tcg-card-face {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    border-radius: 22px;
    overflow: hidden;
}
.tcg-card-back  { transform: rotateY(180deg); }
.tcg-card-back img { width: 100%; height: 100%; object-fit: cover; display: block; }
.tcg-card-front {
    background: linear-gradient(135deg, #252B4A, #1A1F3A);
    border: 4px solid #FFD700;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.tcg-card-front-mono {
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 96px;
    color: #FFD700;
    line-height: 1;
}
.tcg-card-front-label {
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.32em;
    color: #FFD700;
    text-transform: uppercase;
}
.tcg-intro-hint {
    margin: 6px 0 0;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #A6A4B8;
}
.tcg-intro-guest {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-style: italic;
    font-size: 16px;
    color: #F4F1E6;
}
.tcg-intro-guest strong {
    color: #FFD700;
    font-weight: 600;
    margin-left: 6px;
}
.tcg-intro-cta {
    margin-top: 12px;
    padding: 14px 36px;
    background: #FFD700;
    color: #1A1F3A;
    border: none;
    border-radius: 6px;
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 14px;
    letter-spacing: 0.24em;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
}
.tcg-intro-cta:hover {
    background: #FFE66B;
    transform: translateY(-1px);
}
@media (prefers-reduced-motion: reduce) {
    .tcg-card-flip { transition: opacity 0.25s ease; transform: none; }
    .tcg-card-back { display: none; }
}
</style>
