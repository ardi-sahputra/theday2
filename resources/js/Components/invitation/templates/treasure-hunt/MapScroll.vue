<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <section class="th-scroll" :class="{ 'th-scroll--opening': opening }" aria-labelledby="th-scroll-cta">
        <div class="th-scroll__stage">
            <p class="th-scroll__hook">THE TREASURE MAP OF</p>
            <div class="th-scroll__paper-wrap">
                <svg class="th-scroll__svg" viewBox="0 0 800 480" aria-hidden="true">
                    <defs>
                        <linearGradient id="th-scroll-paper-grad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#F2E2B5"/><stop offset="50%" stop-color="#E8D5A0"/><stop offset="100%" stop-color="#C8B077"/>
                        </linearGradient>
                        <radialGradient id="th-wax-grad" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#A02E1B"/><stop offset="80%" stop-color="#8B1A1F"/><stop offset="100%" stop-color="#5C0F12"/>
                        </radialGradient>
                    </defs>
                    <g class="th-scroll-roll-left">
                        <rect x="20" y="140" width="80" height="200" rx="40" fill="#9E7E3E"/>
                        <rect x="32" y="140" width="56" height="200" rx="28" fill="#C8B077"/>
                    </g>
                    <g class="th-scroll-roll-right">
                        <rect x="700" y="140" width="80" height="200" rx="40" fill="#9E7E3E"/>
                        <rect x="712" y="140" width="56" height="200" rx="28" fill="#C8B077"/>
                    </g>
                    <rect class="th-scroll-paper" x="100" y="160" width="600" height="160"
                          fill="url(#th-scroll-paper-grad)" stroke="#A88A4F" stroke-width="2"/>
                    <g class="th-scroll-rope">
                        <path d="M380 130 Q400 220 380 350" stroke="#A02E1B" stroke-width="6" fill="none"/>
                        <path d="M420 130 Q400 220 420 350" stroke="#8B1A1F" stroke-width="6" fill="none"/>
                    </g>
                    <g class="th-scroll-wax">
                        <circle cx="400" cy="240" r="34" fill="url(#th-wax-grad)" stroke="#5C0F12" stroke-width="2"/>
                        <text x="400" y="248" text-anchor="middle" fill="#F2E2B5"
                              font-family="IM Fell English, serif" font-style="italic" font-size="18">{{ coupleInitials }}</text>
                    </g>
                </svg>
            </div>
            <p class="th-scroll__greeting">"Kepada Petualang yang Terhormat,"</p>
            <p class="th-scroll__guest">{{ guestName }}</p>
            <button id="th-scroll-cta" type="button" class="th-scroll__cta"
                    :disabled="opening" @click="onOpen">BUKA GULUNGAN</button>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue'
defineProps({
    guestName:      { type: String, default: 'Tamu Undangan' },
    coupleInitials: { type: String, default: 'A & B' },
})
const emit = defineEmits(['proceed'])
const opening = ref(false)
function onOpen() {
    if (opening.value) return
    opening.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('proceed'), reduced ? 250 : 1500)
}
</script>

<style scoped>
.th-scroll {
    position: fixed; inset: 0; background: var(--th-ink, #3D2817);
    color: var(--th-parchment, #E8D5A0);
    display: grid; place-items: center; overflow: hidden; z-index: 20;
}
.th-scroll__stage { text-align: center; padding: 24px 16px; max-width: 92vw; }
.th-scroll__hook {
    font-family: 'Pirata One', cursive; color: var(--th-gold-flourish, #C9A961);
    letter-spacing: 0.2em; font-size: 14px; margin: 0 0 16px;
}
.th-scroll__paper-wrap { width: min(420px, 80vw); margin: 0 auto; aspect-ratio: 800 / 480; position: relative; }
.th-scroll__svg { width: 100%; height: 100%; display: block; }
.th-scroll-rope { transition: opacity 0.3s ease-out; }
.th-scroll--opening .th-scroll-rope { opacity: 0; }
.th-scroll-paper {
    transform-origin: center center;
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s, opacity 0.4s ease-out 1.1s;
}
.th-scroll--opening .th-scroll-paper { transform: scaleX(1.4) scaleY(1.1); opacity: 0; }
.th-scroll-roll-left, .th-scroll-roll-right {
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s, opacity 0.4s ease-out 1.2s;
}
.th-scroll--opening .th-scroll-roll-left  { transform: translateX(-120vw); opacity: 0; }
.th-scroll--opening .th-scroll-roll-right { transform: translateX( 120vw); opacity: 0; }
.th-scroll-wax { transition: opacity 0.6s ease-out 0.4s; }
.th-scroll--opening .th-scroll-wax { opacity: 0; }
.th-scroll__greeting { font-family: 'Crimson Text', serif; font-style: italic;
    color: rgba(232,213,160,0.7); margin: 18px 0 4px; font-size: 15px; }
.th-scroll__guest { font-family: 'IM Fell English', serif;
    color: var(--th-parchment, #E8D5A0); font-size: 22px; margin: 0 0 24px; }
.th-scroll__cta {
    font-family: 'Pirata One', cursive; color: var(--th-gold-flourish, #C9A961);
    background: transparent; border: 2px solid var(--th-gold-flourish, #C9A961);
    box-shadow: inset 0 0 0 1px rgba(201,169,97,0.5);
    padding: 12px 36px; letter-spacing: 0.15em; font-size: 18px;
    border-radius: 0; cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.th-scroll__cta:hover, .th-scroll__cta:focus-visible {
    background: var(--th-gold-flourish, #C9A961); color: var(--th-ink, #3D2817); outline: none;
}
.th-scroll__cta[disabled] { opacity: 0.6; cursor: default; }
@media (prefers-reduced-motion: reduce) {
    .th-scroll-rope, .th-scroll-paper, .th-scroll-roll-left, .th-scroll-roll-right, .th-scroll-wax {
        transition: opacity 0.2s ease; transform: none;
    }
    .th-scroll--opening .th-scroll-paper,
    .th-scroll--opening .th-scroll-roll-left,
    .th-scroll--opening .th-scroll-roll-right,
    .th-scroll--opening .th-scroll-wax { opacity: 0; }
}
</style>
