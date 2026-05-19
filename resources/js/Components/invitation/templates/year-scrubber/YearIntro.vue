<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { onMounted, onBeforeUnmount } from 'vue'

defineProps({
    groomNick: { type: String, default: '' },
    brideNick: { type: String, default: '' },
    startYear: { type: Number, default: 2018 },
    endYear:   { type: Number, default: 2026 },
})
const emit = defineEmits(['start'])

let timer = null
onMounted(() => {
    timer = setTimeout(() => emit('start'), 2500)
})
onBeforeUnmount(() => { if (timer) clearTimeout(timer) })

function go() {
    if (timer) clearTimeout(timer)
    emit('start')
}
</script>

<template>
    <div class="ys-intro">
        <p class="ys-intro-eyebrow">a love story in</p>
        <p class="ys-intro-monogram">{{ (groomNick[0] || 'A') }} &amp; {{ (brideNick[0] || 'B') }}</p>
        <div class="ys-intro-years">
            <span class="ys-intro-year">{{ startYear }}</span>
            <span class="ys-intro-arrow">&rarr;</span>
            <span class="ys-intro-year">{{ endYear }}</span>
        </div>
        <p class="ys-intro-caption">Geser garis waktu untuk menelusuri perjalanan kami.</p>
        <button type="button" class="ys-intro-cta" @click="go">MULAI MENJELAJAH</button>
    </div>
</template>

<style scoped>
.ys-intro {
    position: fixed; inset: 0; z-index: 40;
    background: #FAF8F2;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 20px; padding: 32px;
    text-align: center;
}
.ys-intro-eyebrow {
    font-family: 'Italianno', 'Allura', cursive;
    color: #C9A961;
    font-size: 28px;
    margin: 0;
    animation: ys-intro-fade 0.6s ease both;
}
.ys-intro-monogram {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #1A2E4A;
    font-size: 52px;
    line-height: 1;
    margin: 0;
    animation: ys-intro-fade 0.6s 0.15s ease both;
}
@media (min-width: 480px) {
    .ys-intro-monogram { font-size: 68px; }
}
@media (min-width: 768px) {
    .ys-intro-monogram { font-size: 80px; }
}
.ys-intro-years {
    display: inline-flex;
    align-items: baseline;
    gap: 16px;
    font-family: 'Bebas Neue', 'Oswald', sans-serif;
    font-size: clamp(64px, 14vw, 96px);
    color: #1A2E4A;
    letter-spacing: 0.04em;
    animation: ys-intro-rise 0.7s 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
}
.ys-intro-arrow { color: #C9A961; font-size: 0.55em; }
.ys-intro-caption {
    font-family: 'EB Garamond', Georgia, serif;
    color: #A39E94;
    font-size: 16px;
    max-width: 320px;
    margin: 0;
    animation: ys-intro-fade 0.6s 0.55s ease both;
}
.ys-intro-cta {
    margin-top: 8px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.3em;
    color: #1A2E4A;
    background: transparent;
    border: 1px solid #C9A961;
    padding: 14px 28px;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.25s ease, color 0.25s ease;
    animation: ys-intro-fade 0.6s 0.7s ease both;
}
.ys-intro-cta:hover  { background: #C9A961; color: #FAF8F2; }
.ys-intro-cta:focus-visible { outline: 2px solid #1A2E4A; outline-offset: 2px; }

@keyframes ys-intro-fade {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes ys-intro-rise {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: none; }
}
@media (prefers-reduced-motion: reduce) {
    .ys-intro-eyebrow, .ys-intro-monogram, .ys-intro-years, .ys-intro-caption, .ys-intro-cta {
        animation: none; opacity: 1; transform: none;
    }
    .ys-intro-cta { transition: none; }
}
</style>
