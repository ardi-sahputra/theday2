<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    monogramText: { type: String, required: true },
    fullDate:     { type: String, required: true },
    fontTitle:    { type: String, default: 'Playfair Display' },
})
const emit = defineEmits(['proceed'])

const pressed       = ref(false)
const dividerOn     = ref(false)
const subOn         = ref(false)
const reducedMotion = ref(false)

onMounted(() => {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion.value) {
        pressed.value = true
        dividerOn.value = true
        subOn.value = true
        setTimeout(() => emit('proceed'), 800)
        return
    }
    requestAnimationFrame(() => { pressed.value = true })
    setTimeout(() => { dividerOn.value = true }, 1000)
    setTimeout(() => { subOn.value = true }, 1200)
    setTimeout(() => emit('proceed'), 1800)
})

function skip() { emit('proceed') }
</script>

<template>
    <div class="lp-opening" @click="skip">
        <div class="lp-opening-stage">
            <h1
                class="lp-opening-monogram"
                :class="{ 'lp-deboss-pressed': pressed }"
                :style="{ fontFamily: fontTitle }"
            >{{ monogramText }}</h1>

            <div v-if="!reducedMotion" class="lp-opening-sweep"></div>

            <span class="lp-opening-divider" :class="{ 'lp-divider-drawn': dividerOn }"></span>

            <p class="lp-opening-sublabel" :class="{ 'lp-fade-in': subOn }">THE WEDDING OF</p>
            <p class="lp-opening-date"     :class="{ 'lp-fade-in': subOn }">{{ fullDate }}</p>
        </div>
    </div>
</template>

<style scoped>
.lp-opening {
    position: fixed; inset: 0; z-index: 40;
    min-height: 100dvh;
    display: grid; place-items: center;
    background: var(--lp-paper, #f9f6f0);
    cursor: pointer;
    overflow: hidden;
}
.lp-opening-stage { position: relative; text-align: center; padding: 24px; max-width: 420px; }

.lp-opening-monogram {
    font-size: clamp(96px, 18vw, 144px);
    color: var(--lp-ink, #1a1a1a);
    letter-spacing: 0.08em;
    transform: scale(1.05);
    transition: transform 600ms ease-out, text-shadow 600ms ease-out;
    text-shadow: 0 0 0 transparent;
    margin: 0;
}
.lp-deboss-pressed {
    transform: scale(1.0);
    text-shadow:
        1px 1px 0 rgba(255,255,255,0.85),
        -1px -1px 1px rgba(0,0,0,0.15),
        0 0 2px rgba(0,0,0,0.08);
}

.lp-opening-sweep {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(110deg,
        transparent 30%,
        var(--lp-gold-warm, #d4b77a) 50%,
        transparent 70%);
    transform: translateX(-100%);
    animation: lp-sweep 800ms ease-out 800ms forwards;
    mix-blend-mode: multiply;
    opacity: 0.55;
}
@keyframes lp-sweep {
    to { transform: translateX(100%); }
}

.lp-opening-divider {
    display: inline-block;
    width: 40px;
    height: 1px;
    background: var(--lp-gold, #c9a961);
    margin: 24px 0;
    transform: scaleX(0);
    transition: transform 400ms ease-out;
}
.lp-divider-drawn { transform: scaleX(1); }

.lp-opening-sublabel {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted, #666);
    margin: 0 0 8px 0;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 400ms ease-out, transform 400ms ease-out;
}
.lp-opening-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink, #1a1a1a);
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 400ms ease-out 100ms, transform 400ms ease-out 100ms;
    margin: 0;
}
.lp-fade-in { opacity: 1; transform: none; }

@media (prefers-reduced-motion: reduce) {
    .lp-opening-monogram {
        transform: none;
        text-shadow:
            1px 1px 0 rgba(255,255,255,0.85),
            -1px -1px 1px rgba(0,0,0,0.15);
        transition: none;
    }
    .lp-opening-sweep { display: none; }
    .lp-opening-divider { transform: scaleX(1); transition: none; }
    .lp-opening-sublabel, .lp-opening-date { opacity: 1; transform: none; transition: none; }
}
</style>
