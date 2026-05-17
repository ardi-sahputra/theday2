<script setup>
import { computed } from 'vue'
import CelestialStarMap    from './CelestialStarMap.vue'
import CelestialZodiacPair from './CelestialZodiacPair.vue'
import { formatLatLabel, formatLngLabel, STAR_MAP_PLACE, STAR_MAP_TZ_LABEL } from './constants.js'

const props = defineProps({
    dateTime:   { type: String,  default: null },
    groomSign:  { type: String,  default: null },
    brideSign:  { type: String,  default: null },
    groomName:  { type: String,  default: '' },
    brideName:  { type: String,  default: '' },
    showCoords: { type: Boolean, default: true },
    showLines:  { type: Boolean, default: true },
    mapStyle:   { type: String,  default: 'classic' },
})

const tagline = computed(() => {
    if (!props.dateTime) return 'A CELESTIAL MAP'
    const d = new Date(props.dateTime)
    const fmt = new Intl.DateTimeFormat('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
    }).format(d)
    return `THE SKY ON ${fmt.toUpperCase()}`
})

const timeLabel = computed(() => {
    if (!props.dateTime) return ''
    const d = new Date(props.dateTime)
    return new Intl.DateTimeFormat('en-GB', {
        hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta',
    }).format(d)
})

const fallbackMode = computed(() => props.dateTime ? null : 'generic')
</script>

<template>
    <section class="ac-hero ac-section">
        <p class="ac-hero-tagline">{{ tagline }}</p>

        <div class="ac-hero-stage">
            <div class="ac-hero-zodiac ac-hero-zodiac--left">
                <CelestialZodiacPair v-if="groomSign" :groom-sign="groomSign" :bride-sign="null"/>
            </div>
            <CelestialStarMap
                :date-time="dateTime"
                :show-lines="showLines"
                :style="mapStyle"
                :fallback="fallbackMode"
                class="ac-hero-map"
            />
            <div class="ac-hero-zodiac ac-hero-zodiac--right">
                <CelestialZodiacPair v-if="brideSign" :groom-sign="null" :bride-sign="brideSign"/>
            </div>
        </div>

        <p v-if="showCoords && dateTime" class="ac-hero-coords">
            {{ formatLatLabel() }} · {{ formatLngLabel() }} · {{ timeLabel }} {{ STAR_MAP_TZ_LABEL }} · {{ STAR_MAP_PLACE }}
        </p>
        <p v-else-if="!dateTime" class="ac-hero-coords">A CELESTIAL MAP</p>

        <h2 class="ac-hero-names">{{ groomName }} &amp; {{ brideName }}</h2>
    </section>
</template>

<style scoped>
.ac-hero {
    position: relative;
    padding: 96px 24px 64px;
    text-align: center;
    background-image:
        radial-gradient(circle at 50% 30%, rgba(125, 111, 155, 0.18), transparent 60%),
        url('/images/templates/astronomy-celestial/nebula-wash.webp');
    background-size: cover;
    background-position: center;
    background-color: var(--ac-navy-deep, #0a1929);
    background-blend-mode: screen;
    color: var(--ac-ivory, #e8e3d3);
}
.ac-hero-tagline {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold, #d4af37);
    font-size: 12px;
    letter-spacing: 0.4em;
    margin: 0 0 32px;
}
.ac-hero-stage {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 32px;
    margin: 0 auto;
    max-width: 800px;
}
.ac-hero-zodiac { flex: 0 0 auto; }
.ac-hero-map  { flex: 1 1 480px; max-width: 480px; }
.ac-hero-coords {
    margin: 32px 0 16px;
    font-family: 'JetBrains Mono', monospace;
    color: rgba(232, 227, 211, 0.7);
    font-size: 12px;
    letter-spacing: 0.2em;
}
.ac-hero-names {
    margin: 16px 0 0;
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory, #e8e3d3);
    font-size: 28px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}
@media (max-width: 720px) {
    .ac-hero-stage {
        flex-direction: column;
        gap: 24px;
    }
    .ac-hero-zodiac { order: 2; }
    .ac-hero-zodiac--left  { order: 2; }
    .ac-hero-zodiac--right { order: 3; }
    .ac-hero-map { order: 1; max-width: min(480px, 90vw); }
    .ac-hero-names { font-size: 22px; }
}
</style>
