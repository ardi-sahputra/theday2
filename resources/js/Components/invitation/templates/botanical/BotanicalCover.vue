<template>
    <div class="bot-cover" :class="{ 'bot-paper': paperTexture }">
        <span class="bot-cover__sprig bot-cover__sprig--top" aria-hidden="true">
            <BotanicalIllustration :slot="'flower-olive'"/>
        </span>

        <button
            v-if="musicEnabled"
            class="bot-cover__music"
            @click.stop="emit('toggle-music')"
            aria-label="Toggle musik"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <div class="bot-cover__content">
            <p class="bot-cover__eyebrow">{{ coverLabel }}</p>
            <BotanicalMonogram
                :text="monogramText"
                :flower-his="flowerHis"
                :flower-her="flowerHer"
                :size="96"
                class="bot-cover__monogram"
            />
            <h1 class="bot-cover__names">{{ groomNick }} &amp; {{ brideNick }}</h1>
            <span class="bot-rule" aria-hidden="true"/>
            <p class="bot-cover__date">{{ eventDate }}</p>
            <p v-if="venueLabel" class="bot-cover__venue">{{ venueLabel }}</p>
            <button class="bot-btn bot-cover__cta" @click="emit('open')">BUKA UNDANGAN</button>
        </div>

        <span class="bot-cover__sprig bot-cover__sprig--bottom" aria-hidden="true">
            <BotanicalIllustration :slot="'flower-peony'"/>
        </span>
    </div>
</template>

<script setup>
import BotanicalMonogram     from './BotanicalMonogram.vue'
import BotanicalIllustration from './BotanicalIllustration.vue'

defineProps({
    groomNick:    { type: String,  default: '' },
    brideNick:    { type: String,  default: '' },
    monogramText: { type: String,  default: 'A & B' },
    flowerHis:    { type: String,  default: 'olive' },
    flowerHer:    { type: String,  default: 'peony' },
    eventDate:    { type: String,  default: '' },
    venueLabel:   { type: String,  default: '' },
    coverLabel:   { type: String,  default: 'KAMI YANG BERBAHAGIA' },
    musicEnabled: { type: Boolean, default: false },
    musicPlaying: { type: Boolean, default: false },
    paperTexture: { type: Boolean, default: true },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<style scoped>
.bot-cover {
    position: fixed; inset: 0; z-index: 30;
    background: var(--bot-cream);
    color: var(--bot-ink);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.bot-paper {
    background-image:
        radial-gradient(rgba(60,40,20,0.018) 1px, transparent 1px),
        radial-gradient(rgba(60,40,20,0.012) 1px, transparent 1px);
    background-size: 3px 3px, 7px 7px;
    background-position: 0 0, 1px 1px;
}
.bot-cover__sprig { position: absolute; width: 48px; height: 24px; }
.bot-cover__sprig--top    { top: 32px; left: 32px; }
.bot-cover__sprig--bottom { bottom: 32px; right: 32px; transform: scale(-1, -1); }
.bot-cover__music {
    position: absolute; top: 24px; right: 24px;
    width: 36px; height: 36px;
    border: 1px solid var(--bot-sage);
    background: transparent;
    border-radius: 50%;
    color: var(--bot-sage-deep);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    z-index: 2;
}
.bot-cover__content {
    position: relative; z-index: 1;
    max-width: 480px;
    text-align: center;
    padding: 32px 24px;
    display: flex; flex-direction: column; align-items: center; gap: 14px;
}
.bot-cover__eyebrow {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
}
.bot-cover__monogram {
    animation: bot-monogram-float 4s ease-in-out infinite alternate;
    transform-origin: center;
}
@keyframes bot-monogram-float {
    0%   { transform: translateY(0) scale(1); }
    100% { transform: translateY(-3px) scale(1.01); }
}
.bot-cover__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 56px;
    line-height: 1.1;
    margin: 0;
}
@media (max-width: 480px) {
    .bot-cover__names { font-size: 40px; }
}
.bot-rule { display: block; width: 60px; height: 1px; background: var(--bot-sage); }
.bot-cover__date {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 14px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.bot-cover__venue {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 12px;
    margin: 0;
}
.bot-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--bot-sage-deep);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--bot-sage);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.bot-btn:hover { background: var(--bot-sage); color: var(--bot-cream); }
.bot-cover__cta { margin-top: 8px; }
@media (prefers-reduced-motion: reduce) {
    .bot-cover__monogram { animation: none; }
    .bot-btn { transition: none; }
}
</style>
