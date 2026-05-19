<script setup>
import { onMounted, ref } from 'vue'
import DustMotes from './DustMotes.vue'

defineProps({
    groomNick: { type: String, default: '' },
    brideNick: { type: String, default: '' },
    guestName: { type: String, default: 'Tamu Undangan' },
})

const emit = defineEmits(['proceed'])

const beamRadius = ref(600)
const reduced = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    reduced.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reduced.value) {
        beamRadius.value = 200
        return
    }
    // Fade-to-dark: start large, ease down to 200 over 1.5s
    const start = 600
    const target = 200
    const duration = 1500
    const t0 = performance.now()
    function step(t) {
        const p = Math.min(1, (t - t0) / duration)
        const eased = 1 - Math.pow(1 - p, 3)
        beamRadius.value = start + (target - start) * eased
        if (p < 1) requestAnimationFrame(step)
    }
    setTimeout(() => requestAnimationFrame(step), 100)
})

function proceed() { emit('proceed') }
</script>

<template>
    <div class="fl-intro-screen" @click.self="proceed">
        <div
            class="fl-intro-beam"
            :style="{ '--fl-intro-radius': beamRadius + 'px' }"
            aria-hidden="true"
        />
        <DustMotes :enabled="!reduced" :count="8"/>

        <div class="fl-intro-stage" @click.stop>
            <p class="fl-intro-eyebrow">THE WEDDING OF</p>
            <h1 class="fl-intro-names">{{ groomNick }} &amp; {{ brideNick }}</h1>
            <p class="fl-intro-script">a love story in the dark</p>
            <span class="fl-intro-rule" aria-hidden="true"/>
            <p class="fl-intro-greet">Kepada <em>{{ guestName }}</em>,</p>
            <p class="fl-intro-instruction">Geser cahaya untuk menemukan kisah kami&hellip;</p>
            <button type="button" class="fl-intro-cta" @click="proceed">
                BUKA RUANG GELAP
            </button>
        </div>
    </div>
</template>

<style scoped>
.fl-intro-screen {
    position: fixed; inset: 0; z-index: 40;
    background: #000000;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    cursor: pointer;
}

.fl-intro-beam {
    --fl-intro-radius: 200px;
    position: absolute; inset: 0;
    background: radial-gradient(
        circle at 50% 50%,
        rgba(255, 213, 128, 0.16) 0px,
        rgba(255, 213, 128, 0.08) calc(var(--fl-intro-radius) * 0.5),
        transparent var(--fl-intro-radius)
    );
    pointer-events: none;
}

.fl-intro-stage {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    padding: 32px 24px;
    max-width: 420px; text-align: center;
    cursor: default;
}

.fl-intro-eyebrow {
    font-family: 'Cinzel', serif;
    color: #C9A961;
    font-size: 12px;
    letter-spacing: 0.4em;
    margin: 0 0 4px;
}

.fl-intro-names {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-weight: 600;
    font-size: 32px;
    color: #F5E6CC;
    margin: 0;
}

.fl-intro-script {
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: #F2C4B8;
    margin: 0;
}

.fl-intro-rule {
    display: block; width: 40px; height: 1px;
    background: #C9A961;
    margin: 4px auto;
}

.fl-intro-greet,
.fl-intro-instruction {
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    color: #F5E6CC;
    font-size: 14px;
    margin: 0;
}

.fl-intro-greet em { color: #C9A961; font-style: italic; }

.fl-intro-cta {
    margin-top: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 28px;
    min-height: 44px;
    min-width: 44px;
    background: transparent;
    color: #C9A961;
    font-family: 'Cinzel', serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid #C9A961;
    border-radius: 2px;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.fl-intro-cta:hover { background: #C9A961; color: #000000; }
.fl-intro-cta:focus { outline: 2px solid #C9A961; outline-offset: 2px; }

@media (prefers-reduced-motion: reduce) {
    .fl-intro-cta { transition: none; }
}
</style>
