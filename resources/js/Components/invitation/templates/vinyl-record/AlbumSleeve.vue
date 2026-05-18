<script setup>
import { ref } from 'vue'

const props = defineProps({
    guestName:       { type: String, default: 'Tamu Undangan' },
    coupleInitials:  { type: String, default: 'A & B' },
    albumTitle:      { type: String, default: 'THE WEDDING SESSIONS' },
    year:            { type: String, default: '2026' },
    sideALabel:      { type: String, default: 'SIDE A · 12 TRACKS · 33⅓ RPM' },
})
const emit = defineEmits(['proceed'])

const opening = ref(false)

function openSleeve() {
    if (opening.value) return
    opening.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('proceed'), reduced ? 200 : 900)
}
</script>

<template>
    <div class="vr-sleeve-screen">
        <div class="vr-sleeve-bg"/>
        <div class="vr-sleeve-stage">
            <p class="vr-sleeve-eyebrow">LP · UNDANGAN PERNIKAHAN</p>

            <button
                type="button"
                class="vr-sleeve"
                :class="{ 'vr-sleeve--opening': opening }"
                @click="openSleeve"
                :aria-label="opening ? 'Membuka sleeve' : 'Tap untuk keluarkan piringan'"
            >
                <span class="vr-sleeve-cardboard">
                    <span class="vr-sleeve-stripe">
                        <span class="vr-sleeve-stripe-text">{{ albumTitle }}</span>
                    </span>
                    <span class="vr-sleeve-monogram">{{ coupleInitials }}</span>
                    <span class="vr-sleeve-bottom">
                        <span class="vr-sleeve-side">{{ sideALabel }}</span>
                        <span class="vr-sleeve-year">{{ year }}</span>
                    </span>
                </span>
                <span class="vr-sleeve-vinyl" aria-hidden="true">
                    <svg viewBox="0 0 100 200" width="36" height="72">
                        <path
                            d="M0 0 A 100 100 0 0 1 0 200 L 0 0 Z"
                            fill="#111111"
                        />
                        <circle cx="0" cy="100" r="22" fill="#C73E3A"/>
                        <circle cx="0" cy="100" r="20" fill="#F5E6CC"/>
                    </svg>
                </span>
            </button>

            <p class="vr-sleeve-greet">Kepada Yang Terhormat,</p>
            <p class="vr-sleeve-guest">{{ guestName }}</p>

            <button type="button" class="vr-sleeve-cta" @click="openSleeve">
                <span>KELUARKAN PIRINGAN</span>
                <svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                    <path d="M3 8h9M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.4"/>
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.vr-sleeve-screen {
    position: fixed; inset: 0; z-index: 40;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    color: #F5E6CC;
}
.vr-sleeve-bg {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, #1a1410 0%, #0a0807 100%);
}
.vr-sleeve-stage {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center;
    gap: 18px;
    padding: 40px 24px;
    max-width: 480px;
    text-align: center;
}
.vr-sleeve-eyebrow {
    font-family: 'Inter', sans-serif;
    color: #D8C8A8;
    font-size: 11px;
    letter-spacing: 0.4em;
    margin: 0;
}
.vr-sleeve {
    position: relative;
    width: 360px; height: 360px;
    background: transparent;
    border: 0;
    padding: 0;
    cursor: pointer;
    transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.6s ease-out 0.3s;
    will-change: transform, opacity;
}
@media (max-width: 480px) {
    .vr-sleeve { width: 280px; height: 280px; }
}
.vr-sleeve--opening { transform: translateX(-80px) scale(0.95) rotate(-3deg); opacity: 0; }

.vr-sleeve-cardboard {
    position: absolute; inset: 0;
    background: #F5E6CC;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(184,144,47,0.05), transparent 60%),
        radial-gradient(circle at 80% 80%, rgba(92,58,33,0.07), transparent 50%);
    box-shadow: 0 24px 40px -12px rgba(0,0,0,0.6), 0 8px 16px -4px rgba(0,0,0,0.4);
    border-radius: 2px;
    overflow: hidden;
    display: flex; flex-direction: column;
    justify-content: space-between;
}
.vr-sleeve-stripe {
    background: #B8902F;
    color: #1a1a1a;
    padding: 6px 14px;
    display: flex; align-items: center; justify-content: center;
}
.vr-sleeve-stripe-text {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 13px;
    letter-spacing: 0.3em;
}
.vr-sleeve-monogram {
    flex: 1;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 96px;
    color: #1a1a1a;
    line-height: 1;
}
.vr-sleeve-bottom {
    display: flex; justify-content: space-between;
    padding: 12px 14px;
    border-top: 1px solid rgba(199,62,58,0.4);
}
.vr-sleeve-side {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 11px;
    letter-spacing: 0.18em;
    color: #C73E3A;
}
.vr-sleeve-year {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.18em;
    color: #5C3A21;
}
.vr-sleeve-vinyl {
    position: absolute;
    top: 50%; right: -18px;
    transform: translateY(-50%);
    transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
}
.vr-sleeve--opening .vr-sleeve-vinyl { transform: translate(120px, -50%) scale(1.5); }

.vr-sleeve-greet {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 18px;
    color: #F5E6CC;
    margin: 12px 0 0;
}
.vr-sleeve-guest {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 22px;
    color: #B8902F;
    margin: 0;
}
.vr-sleeve-cta {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent;
    border: 1px solid #B8902F;
    color: #B8902F;
    padding: 12px 28px;
    min-height: 44px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 14px;
    letter-spacing: 0.3em;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
    margin-top: 6px;
    border-radius: 2px;
}
.vr-sleeve-cta:hover { background: #B8902F; color: #0a0807; }

@media (prefers-reduced-motion: reduce) {
    .vr-sleeve, .vr-sleeve-vinyl { transition: opacity 0.2s ease; }
    .vr-sleeve--opening { transform: none; opacity: 0; }
    .vr-sleeve--opening .vr-sleeve-vinyl { transform: translateY(-50%); }
    .vr-sleeve-cta { transition: none; }
}
</style>
