<script setup>
import BelleEiffelParallax from './BelleEiffelParallax.vue'

defineProps({
    openingText:   { type: String, default: '' },
    coverPhotoUrl: { type: String, default: null },
    eiffelVisible: { type: Boolean, default: true },
})
</script>

<template>
    <section class="bp-hero">
        <div
            v-if="!eiffelVisible && coverPhotoUrl"
            class="bp-hero-photo"
            :style="{ backgroundImage: `url(${coverPhotoUrl})` }"
            aria-hidden="true"
        />
        <BelleEiffelParallax v-if="eiffelVisible" class="bp-hero-eiffel" :intensity="1"/>

        <img
            class="bp-hero-wash"
            src="/images/templates/belle-epoque/wash-blush.webp"
            alt=""
            aria-hidden="true"
            loading="eager"
        />

        <div class="bp-hero-content">
            <h2 class="bp-hero-welcome">Bienvenue à notre mariage</h2>
            <p v-if="openingText" class="bp-hero-opening">{{ openingText }}</p>

            <div class="bp-hero-scrollcue" aria-hidden="true">
                <span>Faites défiler</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </div>
        </div>
    </section>
</template>

<style scoped>
.bp-hero {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(180deg, #fdf6ed 0%, #f7e9dc 70%, #f7e9dc 100%);
    overflow: hidden;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 64px 24px;
    text-align: center;
}
.bp-hero-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    opacity: 0.35;
}
.bp-hero-eiffel { z-index: 1; }
.bp-hero-wash {
    position: absolute; left: 0; right: 0; bottom: 0;
    width: 100%; height: 40%;
    object-fit: cover;
    mix-blend-mode: multiply;
    opacity: 0.55;
    pointer-events: none;
    z-index: 2;
}
.bp-hero-content {
    position: relative; z-index: 3;
    max-width: 580px; margin: auto;
    display: flex; flex-direction: column; align-items: center; gap: 18px;
}
.bp-hero-welcome {
    font-family: 'Italianno', cursive;
    font-size: clamp(40px, 8vw, 64px);
    color: #c08a8a;
    margin: 0; line-height: 1;
}
.bp-hero-opening {
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    font-size: 18px; line-height: 1.7;
    color: #3d3d3d;
    margin: 0;
}
.bp-hero-scrollcue {
    margin-top: 28px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    color: #b8860b;
    font-family: 'Cormorant SC', serif;
    font-size: 12px; letter-spacing: 0.22em;
    text-transform: uppercase;
    animation: bp-cue-bounce 2s ease-in-out infinite alternate;
}
@keyframes bp-cue-bounce {
    from { transform: translateY(0); }
    to   { transform: translateY(4px); }
}
@media (prefers-reduced-motion: reduce) {
    .bp-hero-scrollcue { animation: none; }
}
</style>
