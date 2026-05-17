<script setup>
import { ref } from 'vue'

const props = defineProps({
    groomNick:  { type: String, default: '' },
    brideNick:  { type: String, default: '' },
    guestName:  { type: String, default: 'Tamu Tersayang' },
    italianOn:  { type: Boolean, default: true },
})

const emit = defineEmits(['open'])
const opening = ref(false)

function trigger() {
    if (opening.value) return
    opening.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('open'), reduced ? 200 : 1200)
}
</script>

<template>
    <div class="tv-gate" :class="{ 'tv-gate--open': opening }">
        <div class="tv-gate-bg" aria-hidden="true"/>

        <img class="tv-cypress-left"  src="/images/templates/tuscany-vineyard/cypress.svg"  alt="" draggable="false"/>
        <img class="tv-cypress-right" src="/images/templates/tuscany-vineyard/cypress.svg"  alt="" draggable="false"/>

        <img class="tv-wreath" src="/images/templates/tuscany-vineyard/olive-wreath.svg" alt="" draggable="false"/>

        <div class="tv-gate-stage">
            <p v-if="italianOn" class="tv-gate-italian">Benvenuti</p>
            <p class="tv-gate-sub">Sebuah undangan dari</p>
            <p class="tv-gate-names">{{ groomNick }} &amp; {{ brideNick }}</p>

            <button class="tv-gate-cta" type="button" @click="trigger">
                <span>{{ italianOn ? "Apri l'invito" : 'Buka Undangan' }}</span>
                <span aria-hidden="true">→</span>
            </button>

            <div class="tv-gate-foot">
                <span class="tv-rule"/>
                <span class="tv-gate-guest">Tamu: {{ guestName }}</span>
                <span class="tv-rule"/>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tv-gate {
    position: fixed; inset: 0; z-index: 50;
    overflow: hidden;
    background: #f4e4c1;
    display: flex; align-items: center; justify-content: center;
}
.tv-gate-bg {
    position: absolute; inset: 0;
    background: url('/images/templates/tuscany-vineyard/terracotta-bg.webp') center/cover repeat;
    opacity: 0.08;
    pointer-events: none;
}
.tv-cypress-left, .tv-cypress-right {
    position: absolute; bottom: 0;
    height: min(80vh, 600px);
    width: auto;
    color: #5f7048;
    pointer-events: none;
    z-index: 1;
}
.tv-cypress-left  { left:  0; }
.tv-cypress-right { right: 0; }

.tv-wreath {
    position: absolute;
    top: 36px; left: 50%;
    width: 120px; height: 120px;
    transform: translateX(-50%);
    opacity: 0.85;
    pointer-events: none;
    z-index: 2;
}

.tv-gate-stage {
    position: relative; z-index: 3;
    text-align: center;
    padding: 0 32px;
    max-width: 480px;
    display: flex; flex-direction: column; align-items: center;
    gap: 12px;
}
.tv-gate-italian {
    font-family: 'Italianno', cursive;
    color: #722f2f;
    font-size: 96px;
    line-height: 1;
    margin: 0;
    transform: rotate(-3deg);
}
.tv-gate-sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(58, 42, 28, 0.7);
    font-size: 16px;
    margin: 0;
}
.tv-gate-names {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 600;
    color: #a85a30;
    font-size: 32px;
    margin: 0 0 8px;
}
.tv-gate-cta {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 36px;
    background: #c97b4a;
    color: #f4e4c1;
    font-family: 'Cormorant Garamond', serif;
    font-size: 16px;
    font-weight: 500;
    letter-spacing: 0.05em;
    border: none;
    border-radius: 999px;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(58,42,28,0.18);
    transition: background-color 0.25s ease, transform 0.25s ease;
}
.tv-gate-cta:hover { background: #a85a30; transform: scale(1.02); }

.tv-gate-foot {
    display: flex; align-items: center; gap: 12px;
    margin-top: 16px;
}
.tv-rule { display: block; width: 32px; height: 1px; background: #8b9d6f; }
.tv-gate-guest {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(58,42,28,0.75);
    font-size: 14px;
}

/* Gate slide-apart animation (Section 9.1) */
@keyframes tv-gate-left  { to { transform: translateX(-110%); } }
@keyframes tv-gate-right { to { transform: translateX( 110%); } }
@keyframes tv-gate-fade  { to { opacity: 0; } }

.tv-gate--open .tv-cypress-left  { animation: tv-gate-left  1.2s cubic-bezier(0.65,0,0.35,1) forwards; }
.tv-gate--open .tv-cypress-right { animation: tv-gate-right 1.2s cubic-bezier(0.65,0,0.35,1) forwards; }
.tv-gate--open .tv-wreath        { animation: tv-gate-fade  0.6s ease forwards; }
.tv-gate--open .tv-gate-stage    { animation: tv-gate-fade  0.3s ease 0.1s forwards; }

@media (prefers-reduced-motion: reduce) {
    .tv-gate-cta { transition: none; }
    .tv-gate--open .tv-cypress-left  { animation: none; transform: translateX(-110%); }
    .tv-gate--open .tv-cypress-right { animation: none; transform: translateX( 110%); }
    .tv-gate--open .tv-wreath,
    .tv-gate--open .tv-gate-stage    { animation: none; opacity: 0; }
}
</style>
