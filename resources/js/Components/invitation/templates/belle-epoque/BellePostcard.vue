<script setup>
import { ref } from 'vue'
import BelleStamp from './BelleStamp.vue'

defineProps({
    guestName:       { type: String, default: 'Cher invité' },
    groomNick:       { type: String, default: '' },
    brideNick:       { type: String, default: '' },
    coupleInitials:  { type: String, default: 'A & B' },
    destinationCity: { type: String, default: 'PARIS' },
    weddingDate:     { type: String, default: '' },
})
const emit = defineEmits(['open'])

const isMailing = ref(false)

function mail() {
    if (isMailing.value) return
    isMailing.value = true
    // Match CSS keyframe duration (0.9s); reduced-motion fallback is ~0.3s
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)')
    const t = mq.matches ? 320 : 920
    setTimeout(() => emit('open'), t)
}
</script>

<template>
    <section class="bp-postcard-stage">
        <article
            class="bp-postcard"
            :class="{ 'is-mailing': isMailing }"
            role="button"
            tabindex="0"
            @click="mail"
            @keydown.enter.space.prevent="mail"
            :aria-label="`Ouvrir l'invitation de ${groomNick} et ${brideNick}`"
        >
            <BelleStamp
                motif="paris"
                :city="destinationCity"
                :date="weddingDate"
                :rotate="4"
                class="bp-postcard-stamp"
            />

            <div class="bp-postcard-floral" aria-hidden="true">
                <img src="/images/templates/belle-epoque/peony-divider.svg" alt="" loading="lazy"/>
            </div>

            <h1 class="bp-postcard-bonjour">Bonjour &amp; Bienvenue</h1>

            <p class="bp-postcard-line">Vous êtes invité au mariage de</p>
            <p class="bp-postcard-couple">{{ groomNick }} &amp; {{ brideNick }}</p>

            <p class="bp-postcard-guest">{{ guestName }}</p>

            <div class="bp-postcard-divider" aria-hidden="true"/>
            <p class="bp-postcard-cta">Cliquez pour ouvrir →</p>
        </article>
    </section>
</template>

<style scoped>
.bp-postcard-stage {
    position: fixed; inset: 0; z-index: 40;
    background:
        url('/images/templates/belle-epoque/paper-cream.svg') center/512px repeat,
        #f7e9dc;
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
}
.bp-postcard {
    position: relative;
    width: min(420px, 92vw);
    padding: 32px 28px;
    background: #fdf6ed;
    border: 1px solid #b8860b;
    box-shadow: 0 12px 40px rgba(184, 134, 11, 0.18);
    transform: rotate(-3deg);
    transform-origin: center;
    cursor: pointer;
    text-align: center;
    font-family: 'EB Garamond', Georgia, serif;
    color: #3d3d3d;
    will-change: transform, opacity;
}
.bp-postcard:focus-visible {
    outline: 2px solid #c08a8a;
    outline-offset: 4px;
}

.bp-postcard-stamp {
    position: absolute;
    top: -22px; right: -10px;
    width: 80px; height: 96px;
}
.bp-postcard-floral {
    width: 90px; height: 60px;
    margin: 0 auto 8px;
    opacity: 0.75;
}
.bp-postcard-floral img {
    width: 100%; height: 100%; object-fit: contain;
}
.bp-postcard-bonjour {
    margin: 4px 0 14px;
    font-family: 'Italianno', cursive;
    font-size: 56px; line-height: 1;
    color: #c08a8a;
    font-weight: 400;
}
.bp-postcard-line {
    margin: 0 0 6px;
    font-family: 'Cormorant SC', 'Cormorant Garamond', serif;
    font-size: 13px; letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #3d3d3d;
}
.bp-postcard-couple {
    margin: 0 0 18px;
    font-family: 'Italianno', cursive;
    font-size: 40px; color: #3d3d3d;
}
.bp-postcard-guest {
    margin: 0 0 18px;
    font-style: italic;
    color: #7a5a4a;
    font-size: 15px;
}
.bp-postcard-divider {
    width: 60px; height: 0; margin: 14px auto;
    border-top: 1px dashed #b8860b;
}
.bp-postcard-cta {
    margin: 0;
    font-style: italic;
    color: #c08a8a;
    font-size: 15px;
}

/* ── tilt + mail animation ── */
@keyframes bp-postcard-mail {
    0%   { transform: rotate(-3deg) translateX(0);     opacity: 1; }
    22%  { transform: rotate(5deg)  translateX(0);     opacity: 1; }
    70%  { transform: rotate(10deg) translateX(-80%);  opacity: 1; }
    100% { transform: rotate(12deg) translateX(-120%); opacity: 0; }
}
.bp-postcard.is-mailing {
    animation: bp-postcard-mail 0.9s ease-in forwards;
    pointer-events: none;
}

@media (prefers-reduced-motion: reduce) {
    .bp-postcard.is-mailing {
        animation: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
}
</style>
