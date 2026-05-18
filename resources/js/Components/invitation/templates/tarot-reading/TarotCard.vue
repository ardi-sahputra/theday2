<script setup>
import { computed } from 'vue'
import HolographicFoil from './HolographicFoil.vue'
import CardBackArt    from './CardBackArt.vue'

const props = defineProps({
    roman:           { type: String,  default: '' },
    name:            { type: String,  default: '' },
    revealed:        { type: Boolean, default: false },
    index:           { type: Number,  default: 0 },
    monogramText:    { type: String,  default: 'G & B' },
    holoIntensity:   { type: String,  default: 'medium' }, // subtle|medium|legendary
    illustrationKey: { type: String,  default: '' },        // 'card-01-welcome' etc.
    legendary:       { type: Boolean, default: false },
    isWatermarked:   { type: Boolean, default: false },
})

defineEmits(['flip'])

const illustrationUrl = computed(() =>
    props.illustrationKey
        ? `/images/templates/tarot-reading/${props.illustrationKey}.svg`
        : null
)
</script>

<template>
    <article
        class="tr-card"
        :class="{ 'tr-card--flipped': revealed, 'tr-card--legendary': legendary }"
        :style="{ '--card-index': index }"
        tabindex="0"
        role="button"
        :aria-label="`${roman} — ${name}${revealed ? ', revealed' : ', tap to reveal'}`"
        :aria-pressed="revealed"
        @click="$emit('flip')"
        @keydown.enter.prevent="$emit('flip')"
        @keydown.space.prevent="$emit('flip')"
    >
        <!-- BACK FACE -->
        <div class="tr-card__face tr-card__face--back">
            <CardBackArt :monogram="monogramText" :watermark="isWatermarked">
                <template v-if="isWatermarked" #watermark>
                    <slot name="back-watermark"/>
                </template>
            </CardBackArt>
            <HolographicFoil intensity="subtle"/>
        </div>

        <!-- FRONT FACE -->
        <div class="tr-card__face tr-card__face--front">
            <!-- Filigree border overlay -->
            <svg class="tr-card__frame" viewBox="0 0 320 552" preserveAspectRatio="none"
                 fill="none" stroke="#D4AF37" stroke-width="2" aria-hidden="true">
                <rect x="6" y="6" width="308" height="540" rx="10"/>
                <rect x="14" y="14" width="292" height="524" rx="6" stroke-opacity="0.45"/>
                <g fill="#D4AF37" stroke="none">
                    <circle cx="160" cy="20"  r="2"/>
                    <circle cx="160" cy="532" r="2"/>
                    <circle cx="20"  cy="276" r="2"/>
                    <circle cx="300" cy="276" r="2"/>
                </g>
            </svg>

            <!-- Roman numeral header -->
            <header class="tr-card__roman-header">
                <span class="tr-card__roman-small">{{ roman }}</span>
                <span class="tr-card__divider">&#8212; &#10022; &#8212;</span>
            </header>

            <!-- Illustration area -->
            <div class="tr-card__illustration">
                <img
                    v-if="illustrationUrl"
                    :src="illustrationUrl"
                    :alt="`${name} — illustrated card`"
                    class="tr-card__illustration-img"
                    draggable="false"
                />
                <span v-else class="tr-card__illustration-placeholder" aria-hidden="true"/>

                <!-- Ghosted Roman numeral overlay -->
                <span class="tr-card__numeral" aria-hidden="true">{{ roman }}</span>
            </div>

            <!-- Name banner -->
            <div class="tr-card__name-banner">
                <span class="tr-card__divider">&#8212; &#10022; &#8212;</span>
                <h3 class="tr-card__name">{{ name }}</h3>
            </div>

            <!-- Content slot (section-specific UI) -->
            <div class="tr-card__content">
                <slot/>
            </div>

            <!-- Holo foil overlay -->
            <HolographicFoil :intensity="holoIntensity" :legendary="legendary"/>
        </div>
    </article>
</template>

<style scoped>
.tr-card {
    position: relative;
    width: 100%;
    aspect-ratio: 0.579; /* 2.75:4.75 tarot */
    transform-style: preserve-3d;
    -webkit-transform-style: preserve-3d;
    transition: transform 1s cubic-bezier(0.65, 0, 0.35, 1);
    cursor: pointer;
    will-change: transform;
    outline: none;
}
.tr-card:focus-visible {
    box-shadow: 0 0 0 3px rgba(212,175,55,0.7);
    border-radius: 14px;
}
.tr-card--flipped {
    transform: rotateY(180deg);
}

.tr-card__face {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    border-radius: 14px;
    overflow: hidden;
    background: #2D1B4E;
    border: 3px solid rgba(212,175,55,0.6);
    box-shadow: 0 12px 36px rgba(0,0,0,0.55);
}
.tr-card__face--front {
    transform: rotateY(180deg);
    display: flex;
    flex-direction: column;
    padding: 14px 14px 16px;
    color: #F5E6D3;
    font-family: 'EB Garamond', 'Garamond', Georgia, serif;
}

.tr-card__frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 2;
}

.tr-card__roman-header {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    z-index: 2;
    padding-top: 4px;
}
.tr-card__roman-small {
    font-family: 'IM Fell English', 'EB Garamond', Georgia, serif;
    font-size: clamp(20px, 3.5vw, 28px);
    color: #D4AF37;
    letter-spacing: 0.08em;
}
.tr-card__divider {
    font-family: 'IM Fell English', serif;
    font-size: 10px;
    color: rgba(212,175,55,0.65);
    letter-spacing: 0.3em;
}

.tr-card__illustration {
    position: relative;
    flex: 1 1 auto;
    margin: 8px 4px 6px;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15,11,35,0.5);
    z-index: 2;
}
.tr-card__illustration-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.tr-card__illustration-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #3D2766, #2D1B4E);
}
.tr-card__numeral {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'IM Fell English', 'EB Garamond', serif;
    font-size: clamp(80px, 22vw, 200px);
    color: #D4AF37;
    opacity: 0;
    pointer-events: none;
    user-select: none;
    z-index: 3;
    transition: opacity 1.5s ease-out 1.5s;
}
.tr-card--flipped .tr-card__numeral {
    opacity: 0.15;
}

.tr-card__name-banner {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    margin-bottom: 6px;
    z-index: 2;
}
.tr-card__name {
    margin: 0;
    font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-weight: 700;
    font-size: clamp(15px, 2.5vw, 22px);
    color: #D4AF37;
    text-align: center;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.tr-card__content {
    position: relative;
    z-index: 2;
    overflow-y: auto;
    max-height: 40%;
    padding: 6px 8px 4px;
    scrollbar-width: thin;
    scrollbar-color: rgba(212,175,55,0.4) transparent;
}
.tr-card__content::-webkit-scrollbar {
    width: 4px;
}
.tr-card__content::-webkit-scrollbar-thumb {
    background: rgba(212,175,55,0.4);
    border-radius: 2px;
}

/* Hover (desktop face-down) */
@media (hover: hover) {
    .tr-card:not(.tr-card--flipped):hover {
        transform: scale(1.04);
        box-shadow:
            0 0 0 2px #D4AF37,
            0 0 24px rgba(212,175,55,0.4),
            0 8px 32px rgba(0,0,0,0.5);
        transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
    }
}

/* Reduced motion fallback */
@media (prefers-reduced-motion: reduce) {
    .tr-card { transition: opacity 0.4s ease; transform: none !important; }
    .tr-card__face--front {
        transform: none;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .tr-card__face--back {
        opacity: 1;
        transition: opacity 0.4s ease;
    }
    .tr-card--flipped .tr-card__face--front { opacity: 1; }
    .tr-card--flipped .tr-card__face--back  { opacity: 0; }
    .tr-card__numeral { transition: opacity 0.4s ease; }
    .tr-card:not(.tr-card--flipped):hover { transform: none; box-shadow: none; }
}
</style>
