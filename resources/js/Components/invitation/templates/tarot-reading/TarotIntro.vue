<script setup>
import { ref } from 'vue'
import CardBackArt      from './CardBackArt.vue'
import MysticalAura     from './MysticalAura.vue'
import CrystalBallDecor from './CrystalBallDecor.vue'

defineProps({
    guestName:    { type: String,  default: 'Tamu Undangan' },
    monogramText: { type: String,  default: 'G & B' },
    auraEnabled:  { type: Boolean, default: true },
})

const emit = defineEmits(['proceed'])
const drawing = ref(false)

function draw() {
    if (drawing.value) return
    drawing.value = true
    // 800ms matches tr-card-draw animation duration
    setTimeout(() => emit('proceed'), 800)
}

// Stack offset config (top card highest)
const stackCards = Array.from({ length: 5 }, (_, i) => ({
    idx:   i,
    offY:  i * 2,
    rot:   (i % 2 === 0 ? -1 : 1) * (i + 1) * 0.8,
}))
</script>

<template>
    <section class="tr-intro" :class="{ 'tr-intro--drawing': drawing }">
        <MysticalAura :count="6" :enabled="auraEnabled"/>
        <CrystalBallDecor position="top-right"/>

        <div class="tr-intro__inner">
            <header class="tr-intro__header">
                <h1 class="tr-intro__title">TAROT READING</h1>
                <p class="tr-intro__subtitle">Tariklah kartumu, baca takdir kami.</p>
            </header>

            <div class="tr-intro__deck" @click="draw" role="button" tabindex="0"
                 @keydown.enter.prevent="draw" @keydown.space.prevent="draw"
                 aria-label="Tarik kartu untuk memulai">
                <div
                    v-for="c in stackCards"
                    :key="c.idx"
                    class="tr-intro__deck-card"
                    :class="{ 'tr-intro-card--drawing': drawing && c.idx === stackCards.length - 1 }"
                    :style="{
                        '--offY': c.offY + 'px',
                        '--rot':  c.rot + 'deg',
                        'z-index': c.idx,
                    }"
                >
                    <CardBackArt :monogram="monogramText"/>
                </div>
            </div>

            <div class="tr-intro__cta-wrap">
                <p class="tr-intro__greeting">Kepada {{ guestName }}</p>
                <button type="button" class="tr-btn" @click="draw" :disabled="drawing">
                    TARIK KARTU
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
.tr-intro {
    position: relative;
    min-height: 100vh;
    background: #0F0B23;
    color: #F5E6D3;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 16px;
    overflow: hidden;
}
.tr-intro__inner {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 40px;
    max-width: 480px;
    width: 100%;
}
.tr-intro__header {
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.tr-intro__title {
    margin: 0;
    font-family: 'Cinzel Decorative', 'Cinzel', 'Trajan Pro', serif;
    font-weight: 700;
    font-size: clamp(28px, 5vw, 42px);
    color: #D4AF37;
    letter-spacing: 0.18em;
}
.tr-intro__subtitle {
    margin: 0;
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    font-size: clamp(14px, 2vw, 16px);
    color: #F5E6D3;
    opacity: 0.85;
}
.tr-intro__deck {
    position: relative;
    width: min(60vw, 240px);
    aspect-ratio: 0.579;
    cursor: pointer;
    transition: transform 0.3s ease;
    outline: none;
}
.tr-intro__deck:hover { transform: scale(1.02); }
.tr-intro__deck:focus-visible { box-shadow: 0 0 0 3px rgba(212,175,55,0.7); border-radius: 14px; }
.tr-intro__deck-card {
    position: absolute;
    inset: 0;
    border-radius: 14px;
    overflow: hidden;
    transform: translateY(var(--offY, 0)) rotate(var(--rot, 0));
    box-shadow: 0 8px 24px rgba(0,0,0,0.55);
    border: 3px solid rgba(212,175,55,0.6);
}
.tr-intro-card--drawing {
    animation: tr-card-draw 0.8s cubic-bezier(0.5, 1.5, 0.5, 1) forwards;
}
@keyframes tr-card-draw {
    0%   { transform: translateY(var(--offY, 0)) rotate(var(--rot, 0)); }
    50%  { transform: translateY(-120%) rotate(-8deg); }
    100% { transform: translateY(-120%) rotate(0); opacity: 0; }
}
.tr-intro__cta-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}
.tr-intro__greeting {
    margin: 0;
    font-family: 'IM Fell English', Georgia, serif;
    font-style: italic;
    font-size: 13px;
    color: #9D8FB0;
    letter-spacing: 0.06em;
}
.tr-btn {
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: #D4AF37;
    font-family: 'Cinzel Decorative', serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid #D4AF37;
    cursor: pointer;
    transition: color 0.3s ease, background 0.3s ease;
}
.tr-btn::before {
    content: '';
    position: absolute;
    inset: -4px;
    border: 1px solid #D4AF37;
    transform: scale(1.08);
    opacity: 0;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.6s ease;
    pointer-events: none;
}
.tr-btn:hover, .tr-btn:focus-visible {
    background: #D4AF37;
    color: #0F0B23;
}
.tr-btn:hover::before, .tr-btn:focus-visible::before {
    transform: scale(1);
    opacity: 1;
}
.tr-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (prefers-reduced-motion: reduce) {
    .tr-intro__deck { transition: none; }
    .tr-intro-card--drawing {
        animation: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .tr-btn, .tr-btn::before { transition: none; }
    .tr-btn::before { display: none; }
}
@media (max-width: 480px) {
    .tr-intro__deck { width: 78vw; }
    .tr-btn { padding: 12px 24px; font-size: 11px; letter-spacing: 0.24em; }
}
</style>
