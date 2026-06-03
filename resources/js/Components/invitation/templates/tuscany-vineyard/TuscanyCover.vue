<script setup>
import { computed } from 'vue'

const props = defineProps({
    coverPhotoUrl:  { type: String,  default: null },
    groomName:      { type: String,  default: '' },
    brideName:      { type: String,  default: '' },
    targetDate:     { type: [Date, Object, String], default: null },
    flareIntensity: { type: String,  default: 'medium' }, // subtle | medium | strong
    italianOn:      { type: Boolean, default: true },
    musicPlaying:   { type: Boolean, default: false },
    pad:            { type: Function, default: (n) => String(n).padStart(2, '0') },
})

const emit = defineEmits(['open', 'toggle-music'])

const flareOpacity = computed(() => ({
    subtle: 0.35,
    medium: 0.55,
    strong: 0.75,
}[props.flareIntensity] ?? 0.55))

const dateParts = computed(() => {
    const d = props.targetDate ? new Date(props.targetDate) : null
    if (!d || isNaN(+d)) return null
    return {
        d: props.pad(d.getDate()),
        m: props.pad(d.getMonth() + 1),
        y: d.getFullYear(),
    }
})
</script>

<template>
    <div class="tv-cover">
        <div
            class="tv-cover-photo"
            :style="coverPhotoUrl ? { backgroundImage: `url(${coverPhotoUrl})` } : { background: '#3a2a1c' }"
        />
        <div class="tv-cover-vignette" aria-hidden="true"/>
        <img
            class="tv-cover-flare tv-sun-flare"
            src="/images/templates/tuscany-vineyard/sun-flare.svg"
            :style="{ opacity: flareOpacity }"
            alt="" draggable="false"
        />

        <button
            class="tv-cover-music"
            type="button"
            @click.stop="emit('toggle-music')"
            :aria-label="musicPlaying ? 'Pause musik' : 'Putar musik'"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <div class="tv-cover-stage">
            <div class="tv-cover-eyebrow">
                <span class="tv-rule"/>
                <span v-if="italianOn" class="tv-cover-italian">L'AMORE</span>
                <span class="tv-rule"/>
            </div>

            <h1 class="tv-cover-names">
                <span>{{ groomName }}</span>
                <span class="tv-cover-amp">&amp;</span>
                <span>{{ brideName }}</span>
            </h1>

            <p v-if="dateParts" class="tv-cover-date">
                {{ dateParts.d }} · {{ dateParts.m }} · {{ dateParts.y }}
            </p>

            <button class="tv-cover-cue" type="button" @click="emit('open')">
                <span class="tv-cover-arrow" aria-hidden="true">↓</span>
                <span class="tv-cover-cue-label">{{ italianOn ? 'Scorri giù' : 'Geser ke bawah' }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.tv-cover {
    position: fixed; inset: 0; z-index: 40;
    overflow: hidden;
    color: #f4e4c1;
}
.tv-cover-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.tv-cover-vignette {
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse at center 30%, transparent 0%, rgba(58,42,28,0.35) 60%, rgba(58,42,28,0.75) 100%),
        linear-gradient(180deg, rgba(201,123,74,0.15) 0%, transparent 30%, rgba(58,42,28,0.55) 100%);
    pointer-events: none;
}
.tv-cover-flare {
    position: absolute; top: -10%; right: -10%;
    width: 60vw; height: auto;
    mix-blend-mode: screen;
    pointer-events: none;
}
.tv-sun-flare {
    animation: tv-sun-pulse 4s ease-in-out infinite alternate;
    will-change: opacity, transform;
}
@keyframes tv-sun-pulse {
    0%   { opacity: 0.7; transform: scale(1);    }
    100% { opacity: 1;   transform: scale(1.04); }
}

.tv-cover-music {
    position: absolute; top: 24px; right: 24px;
    width: 40px; height: 40px;
    background: rgba(58,42,28,0.5);
    border: 1px solid rgba(244,228,193,0.5);
    border-radius: 50%;
    color: #f4e4c1;
    cursor: pointer;
    z-index: 2;
    font-size: 16px;
    display: flex; align-items: center; justify-content: center;
}

.tv-cover-stage {
    position: relative; z-index: 1;
    height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 16px;
    padding: 0 24px;
    text-align: center;
}
.tv-cover-eyebrow {
    display: flex; align-items: center; gap: 12px;
}
.tv-rule { display: block; width: 32px; height: 1px; background: #c97b4a; }
.tv-cover-italian {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    color: #722f2f;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
}
.tv-cover-names {
    font-family: 'Italianno', cursive;
    color: #f4e4c1;
    font-size: 88px;
    line-height: 1.05;
    margin: 0;
    text-shadow: 0 2px 12px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; align-items: center;
}
@media (max-width: 480px) { .tv-cover-names { font-size: 64px; } }
.tv-cover-amp {
    font-family: 'Italianno', cursive;
    color: #c97b4a;
    font-size: 0.7em;
}
.tv-cover-date {
    font-family: 'Cormorant Garamond', serif;
    color: #f4e4c1;
    font-size: 18px;
    letter-spacing: 0.4em;
    margin: 0;
    text-shadow: 0 1px 8px rgba(0,0,0,0.5);
}

.tv-cover-cue {
    margin-top: 24px;
    background: transparent;
    border: none;
    color: #f4e4c1;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    cursor: pointer;
    font-family: 'Cormorant Garamond', serif;
}
.tv-cover-arrow {
    font-size: 28px;
    animation: tv-cue-bounce 1.4s ease-in-out infinite;
}
@keyframes tv-cue-bounce {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(8px); }
}
.tv-cover-cue-label {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(244,228,193,0.85);
    font-size: 14px;
    letter-spacing: 0.1em;
}

@media (prefers-reduced-motion: reduce) {
    .tv-sun-flare { animation: none; }
    .tv-cover-arrow { animation: none; }
}
</style>
