<script setup>
import { ref } from 'vue'
import PostalStamp    from './PostalStamp.vue'
import PostalPostmark from './PostalPostmark.vue'

const props = defineProps({
    guestName:     { type: String, default: 'Tamu Undangan' },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
    originCity:    { type: String, default: 'JAKARTA' },
    firstEventDate:{ type: String, default: '' },
})
const emit = defineEmits(['open'])

const opening = ref(false)

function openEnvelope() {
    if (opening.value) return
    opening.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('open'), reduced ? 250 : 1400)
}

const initials = (props.groomNick?.[0] ?? 'A') + '&' + (props.brideNick?.[0] ?? 'B')
</script>

<template>
    <div class="vp-envelope-screen">
        <p class="vp-env-prompt">Tap amplop untuk membuka</p>

        <button
            type="button"
            class="vp-envelope"
            :class="{ 'vp-envelope--opening': opening }"
            @click="openEnvelope"
            :aria-label="opening ? 'Membuka amplop' : 'Tap untuk membuka undangan'"
        >
            <span class="vp-envelope-body">
                <img
                    src="/images/templates/vintage-postal/airmail-envelope.svg"
                    class="vp-envelope-bg"
                    alt=""
                    draggable="false"
                />

                <PostalStamp
                    class="vp-env-stamp"
                    :city="originCity"
                    :date="firstEventDate"
                    :rotate="-4"
                />

                <PostalPostmark
                    class="vp-env-postmark"
                    variant="par-avion"
                    :date="firstEventDate"
                />

                <span class="vp-env-address">
                    <span class="vp-env-addr-line">Kepada Yth,</span>
                    <span class="vp-env-addr-name">{{ guestName }}</span>
                    <span class="vp-env-addr-line">di tempat</span>
                </span>

                <span class="vp-env-from">
                    <span>FROM: {{ groomNick }} &amp; {{ brideNick }}</span>
                    <span class="vp-env-from-city">{{ originCity }}</span>
                </span>
            </span>

            <span class="vp-envelope-flap" aria-hidden="true"/>
            <span class="vp-envelope-paper" aria-hidden="true">
                <span class="vp-env-paper-text">{{ groomNick }} &amp; {{ brideNick }}</span>
            </span>
            <span class="vp-envelope-seal" aria-hidden="true">
                <img src="/images/templates/vintage-postal/wax-seal.png" alt="" draggable="false"/>
                <span class="vp-env-seal-tag">{{ initials }}</span>
            </span>
        </button>
    </div>
</template>

<style scoped>
.vp-envelope-screen {
    position: fixed; inset: 0; z-index: 40;
    background:
        url('/images/templates/vintage-postal/paper-aged-1.webp') center/cover no-repeat,
        #e8dcc4;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 24px;
    padding: 24px;
    overflow: hidden;
}
.vp-env-prompt {
    font-family: 'Homemade Apple', cursive;
    color: #5c4a3a;
    font-style: italic;
    font-size: 18px;
    margin: 0;
}
.vp-envelope {
    position: relative;
    width: 90vw; max-width: 420px;
    aspect-ratio: 7/4;
    background: transparent;
    border: none; padding: 0;
    cursor: pointer;
    transform-style: preserve-3d;
    transition: transform 0.3s ease-out;
}
.vp-envelope:hover { transform: rotate(1deg); }
.vp-envelope--opening { transform: rotate(3deg); }
.vp-envelope-body {
    position: absolute; inset: 0;
    display: block;
}
.vp-envelope-bg {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    user-select: none; pointer-events: none;
}
.vp-env-stamp    { position: absolute; top: 16px; right: 24px; }
.vp-env-postmark { position: absolute; top: 32px; right: 64px; width: 84px; height: 84px; }
.vp-env-address {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -30%);
    display: flex; flex-direction: column; align-items: center;
    text-align: center;
    font-family: 'Homemade Apple', cursive;
    color: #3a2d1f;
    line-height: 1.4;
    width: 70%;
}
.vp-env-addr-line { font-size: 14px; opacity: 0.75; }
.vp-env-addr-name { font-size: 22px; margin: 2px 0; }
.vp-env-from {
    position: absolute;
    bottom: 16px; left: 24px;
    display: flex; flex-direction: column;
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 10px;
    color: #3a2d1f;
    letter-spacing: 1px;
    text-align: left;
}
.vp-env-from-city { padding-left: 36px; }

/* Flap */
.vp-envelope-flap {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 56%;
    background: linear-gradient(180deg, #d8c8a0 0%, #c8b890 100%);
    clip-path: polygon(0 0, 100% 0, 50% 100%);
    transform-origin: top center;
    transform: rotateX(0deg);
    transition: transform 0.7s cubic-bezier(0.65, 0, 0.35, 1) 0.2s;
    pointer-events: none;
}
.vp-envelope--opening .vp-envelope-flap { transform: rotateX(160deg); }

/* Paper that slides out */
.vp-envelope-paper {
    position: absolute;
    top: 12px; left: 12px; right: 12px; bottom: 12px;
    background:
        url('/images/templates/vintage-postal/paper-aged-2.webp') center/cover no-repeat,
        #f4ead5;
    border: 1px solid rgba(92, 74, 58, 0.4);
    display: flex; align-items: center; justify-content: center;
    transform: translateY(0) scale(1);
    transition: transform 1.2s ease-in 0.5s, opacity 0.4s ease 1.4s;
    pointer-events: none;
}
.vp-env-paper-text {
    font-family: 'Homemade Apple', cursive;
    color: #3a2d1f; font-size: 22px;
}
.vp-envelope--opening .vp-envelope-paper {
    transform: translateY(-90vh) scale(1.05);
    opacity: 0;
}

/* Wax seal */
.vp-envelope-seal {
    position: absolute;
    top: 38%; left: 50%;
    transform: translate(-50%, -50%) scale(1);
    width: 80px; height: 80px;
    transition: transform 0.25s ease-in, opacity 0.25s ease-in;
    pointer-events: none;
}
.vp-envelope-seal img {
    width: 100%; height: 100%; object-fit: contain;
    filter: drop-shadow(0 2px 4px rgba(58,45,31,0.4));
}
.vp-env-seal-tag {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Special Elite', monospace;
    color: #f4ead5; font-size: 14px;
}
.vp-envelope--opening .vp-envelope-seal {
    transform: translate(-50%, -50%) scale(0);
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .vp-envelope,
    .vp-envelope-flap,
    .vp-envelope-paper,
    .vp-envelope-seal {
        transition: opacity 0.2s ease !important;
        transform: none !important;
    }
    .vp-envelope--opening .vp-envelope-flap,
    .vp-envelope--opening .vp-envelope-paper,
    .vp-envelope--opening .vp-envelope-seal { opacity: 0; }
}
</style>
