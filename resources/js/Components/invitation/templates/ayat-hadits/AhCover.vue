<template>
    <div class="ah-cover-screen">
        <AhParchmentBg intensity="subtle">
            <div class="ah-cover">
                <button
                    v-if="musicEnabled"
                    class="ah-cover__music"
                    @click.stop="emit('toggle-music')"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '&#9834;' : '&#9835;' }}</button>

                <AhCartouche :cartouche-style="cartoucheStyle" :width="360" :height="520">
                    <p class="ah-cover__bismillah" dir="rtl">&#1576;&#1616;&#1587;&#1618;&#1605;&#1616; &#1575;&#1604;&#1604;&#1607;&#1616; &#1575;&#1604;&#1585;&#1617;&#1614;&#1581;&#1618;&#1605;&#1614;&#1600;&#1606;&#1616; &#1575;&#1604;&#1585;&#1617;&#1614;&#1581;&#1616;&#1610;&#1618;&#1605;&#1616;</p>
                    <p class="ah-cover__eyebrow">UNDANGAN PERNIKAHAN</p>
                    <h1 class="ah-cover__names">{{ groomName }} &amp; {{ brideName }}</h1>
                    <p v-if="showArabicNames && (arabicGroom || arabicBride)" class="ah-cover__names-ar" dir="rtl">
                        {{ arabicGroom }} &amp; {{ arabicBride }}
                    </p>
                    <span class="ah-rule" aria-hidden="true"/>
                    <p class="ah-cover__event">{{ firstEvent?.event_name ?? 'Akad Nikah' }}</p>
                    <p class="ah-cover__date">{{ firstEventDate }}</p>
                    <p v-if="firstEvent?.venue_name" class="ah-cover__venue">{{ firstEvent.venue_name }}</p>
                    <button class="ah-btn ah-cover__cta" @click="emit('open')">BUKA UNDANGAN</button>
                </AhCartouche>
            </div>
        </AhParchmentBg>
    </div>
</template>

<script setup>
import AhParchmentBg from './AhParchmentBg.vue'
import AhCartouche   from './AhCartouche.vue'

defineProps({
    groomName:       { type: String,  default: '' },
    brideName:       { type: String,  default: '' },
    arabicGroom:     { type: String,  default: '' },
    arabicBride:     { type: String,  default: '' },
    showArabicNames: { type: Boolean, default: false },
    firstEvent:      { type: Object,  default: null },
    firstEventDate:  { type: String,  default: '' },
    cartoucheStyle:  { type: String,  default: 'ottoman' },
    musicEnabled:    { type: Boolean, default: false },
    musicPlaying:    { type: Boolean, default: false },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<style scoped>
.ah-cover-screen { position: fixed; inset: 0; z-index: 30; overflow: hidden; }
.ah-cover {
    min-height: 100%;
    display: flex; align-items: center; justify-content: center;
    padding: 32px 24px;
    color: var(--ah-ink);
}
.ah-cover__music {
    position: absolute; top: 24px; right: 24px;
    width: 36px; height: 36px;
    border: 1px solid var(--ah-gold);
    background: transparent;
    border-radius: 50%;
    color: var(--ah-ink);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    z-index: 2;
}
.ah-cover__bismillah {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: 28px;
    color: var(--ah-ink-decorative);
    text-align: center;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 24px;
    animation: ah-bismillah-glow 5s ease-in-out infinite alternate;
}
@keyframes ah-bismillah-glow {
    0%   { text-shadow: 0 0 0 transparent; }
    100% { text-shadow: 0 0 12px rgba(201, 169, 97, 0.35); }
}
.ah-cover__eyebrow {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0 0 12px;
}
.ah-cover__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 48px;
    line-height: 1.15;
    margin: 0;
}
@media (max-width: 480px) {
    .ah-cover__names { font-size: 36px; }
}
.ah-cover__names-ar {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 24px;
    margin: 8px 0 0;
    direction: rtl;
    letter-spacing: 0;
}
.ah-rule { display: block; width: 60px; height: 1px; background: var(--ah-gold); margin: 16px auto; }
.ah-cover__event {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 18px;
    margin: 0;
}
.ah-cover__date {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 16px;
    margin: 6px 0 0;
}
.ah-cover__venue {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink-soft);
    font-size: 14px;
    margin: 4px 0 0;
}
.ah-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--ah-ink);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--ah-ink);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.ah-btn:hover { background: var(--ah-ink); color: var(--ah-parchment); }
.ah-cover__cta { margin-top: 16px; }

@media (prefers-reduced-motion: reduce) {
    .ah-cover__bismillah { animation: none; }
    .ah-btn { transition: none; }
}
</style>
