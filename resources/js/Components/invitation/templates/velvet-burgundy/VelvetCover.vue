<script setup>
import VelvetFiligree from './VelvetFiligree.vue'

defineProps({
    coverUrl:     { type: String,  default: null },
    groomNick:    { type: String,  default: '' },
    brideNick:    { type: String,  default: '' },
    subtitle:     { type: String,  default: 'Sebuah Undangan Pernikahan' },
    eventDate:    { type: String,  default: '' },
    musicPlaying: { type: Boolean, default: false },
    density:      { type: String,  default: 'medium' },
})

const emit = defineEmits(['open', 'toggleMusic'])
</script>

<template>
    <div class="vb-cover-root">
        <div
            class="vb-cover-bg"
            :style="coverUrl
                ? { backgroundImage: `url(${coverUrl})` }
                : { background: 'var(--vb-burgundy-deep, #3a0c0e)' }"
        />
        <div class="vb-cover-overlay"/>
        <div class="vb-cover-grain"/>

        <VelvetFiligree corner="top-l" :density="density"/>
        <VelvetFiligree corner="top-r" :density="density"/>
        <VelvetFiligree corner="bot-l" :density="density"/>
        <VelvetFiligree corner="bot-r" :density="density"/>

        <div class="vb-cover-content">
            <p class="vb-cover-subtitle">{{ subtitle }}</p>
            <h1 class="vb-cover-names">{{ groomNick }} &amp; {{ brideNick }}</h1>
            <img
                src="/images/templates/velvet-burgundy/filigree-divider.svg"
                alt=""
                aria-hidden="true"
                class="vb-cover-divider"
            />
            <p v-if="eventDate" class="vb-cover-date">{{ eventDate }}</p>
            <button class="vb-cover-cta vb-candle-glow" type="button" @click="emit('open')">
                Buka Undangan
            </button>
        </div>

        <button
            class="vb-cover-music"
            type="button"
            @click.stop="emit('toggleMusic')"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
        >
            <span aria-hidden="true">{{ musicPlaying ? '♪' : '♩' }}</span>
        </button>
    </div>
</template>

<style scoped>
.vb-cover-root {
    position: fixed;
    inset: 0;
    z-index: 55;
    overflow: hidden;
    font-family: 'Crimson Text', serif;
    color: var(--vb-cream, #f8f1e7);
}

.vb-cover-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}
.vb-cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(45,5,7,0.15) 0%, rgba(45,5,7,0.85) 90%);
}
.vb-cover-grain {
    position: absolute;
    inset: 0;
    background-image: url('/images/templates/velvet-burgundy/velvet-grain.svg');
    background-repeat: repeat;
    opacity: 0.18;
    animation: vb-grain-shimmer 8s linear infinite;
    pointer-events: none;
}

.vb-cover-content {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 48px;
    z-index: 3;
    text-align: center;
    padding: 0 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.vb-cover-subtitle {
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 4px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0;
    text-transform: uppercase;
}

.vb-cover-names {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-weight: 700;
    font-size: 44px;
    line-height: 1.1;
    margin: 4px 0;
    color: var(--vb-cream, #f8f1e7);
}

.vb-cover-divider {
    width: 200px;
    height: 24px;
    color: var(--vb-gold-soft, #d4a574);
    opacity: 0.85;
}

.vb-cover-date {
    font-family: 'Cormorant SC', serif;
    font-size: 14px;
    letter-spacing: 4px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0 0 12px;
    text-transform: uppercase;
}

.vb-cover-cta {
    margin-top: 8px;
    padding: 14px 36px;
    background: var(--vb-red-accent, #8b1a1f);
    border: 1px solid var(--vb-gold-soft, #d4a574);
    color: var(--vb-cream, #f8f1e7);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.25s ease, transform 0.2s ease;
}
.vb-cover-cta:hover {
    background: var(--vb-burgundy, #5c1a1b);
    transform: translateY(-1px);
}

.vb-cover-music {
    position: absolute;
    bottom: 16px;
    right: 16px;
    z-index: 4;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--vb-gold-soft, #d4a574);
    color: var(--vb-red-accent, #8b1a1f);
    border: none;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vb-candle-glow {
    animation: vb-candle-glow 3.5s ease-in-out infinite alternate;
}
@keyframes vb-candle-glow {
    0%, 100% { box-shadow: 0 0 8px rgba(212,165,116,0.4), 0 0 16px rgba(212,165,116,0.2); }
    50%      { box-shadow: 0 0 14px rgba(212,165,116,0.7), 0 0 28px rgba(212,165,116,0.35); }
}

@keyframes vb-grain-shimmer {
    0%   { background-position: 0 0; }
    100% { background-position: 200px 200px; }
}

@media (prefers-reduced-motion: reduce) {
    .vb-cover-grain { animation: none; }
    .vb-candle-glow { animation: none; box-shadow: 0 0 8px rgba(212,165,116,0.4); }
    .vb-cover-cta:hover { transform: none; }
}
</style>
