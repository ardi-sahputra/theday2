<script setup>
import IsgArabesqueBg from './IsgArabesqueBg.vue'
import IsgKhatam      from './IsgKhatam.vue'
import IsgCartouche   from './IsgCartouche.vue'

defineProps({
    groomName:    { type: String,  default: '' },
    brideName:    { type: String,  default: '' },
    hasArabic:    { type: Boolean, default: false },
    arabicParts:  { type: Array,   default: null },
    fullDate:     { type: String,  default: '' },
    venueName:    { type: String,  default: '' },
})
const emit = defineEmits(['open'])
</script>

<template>
    <div class="isg-cover">
        <IsgArabesqueBg intensity="subtle" class="isg-cover-bg" />
        <div class="isg-cover-stage">
            <IsgKhatam :size="48" class="isg-stagger isg-cover-khatam" style="--d: 0.05s" />
            <p class="isg-cover-label isg-stagger" style="--d: 0.15s">WALIMATUL &lsquo;URS</p>

            <IsgCartouche class="isg-stagger" style="--d: 0.25s">
                <template v-if="hasArabic && arabicParts">
                    <h1 class="isg-cover-name-ar" dir="rtl" lang="ar">{{ arabicParts[0] }}</h1>
                    <span class="isg-cover-amp-ar" dir="rtl">&#x0648;</span>
                    <h1 class="isg-cover-name-ar" dir="rtl" lang="ar">{{ arabicParts[1] }}</h1>
                </template>
                <template v-else>
                    <h1 class="isg-cover-name-latin">{{ groomName }}</h1>
                    <span class="isg-cover-amp">&amp;</span>
                    <h1 class="isg-cover-name-latin">{{ brideName }}</h1>
                </template>
            </IsgCartouche>

            <span class="isg-divider isg-stagger" style="--d: 0.45s"></span>
            <p class="isg-cover-date isg-stagger" style="--d: 0.55s">{{ fullDate }}</p>
            <p v-if="venueName" class="isg-cover-venue isg-stagger" style="--d: 0.62s">{{ venueName }}</p>
            <button class="isg-btn isg-stagger" style="--d: 0.75s" @click="emit('open')">BUKA UNDANGAN</button>
        </div>
    </div>
</template>

<style scoped>
.isg-cover {
    position: fixed; inset: 0; z-index: 30;
    background: linear-gradient(180deg, var(--isg-ivory, #f5efe3) 0%, var(--isg-ivory-warm, #ede4d2) 100%);
    display: grid; place-items: center;
    padding: 32px;
    overflow: hidden;
}
.isg-cover-bg { position: absolute; inset: 0; }
.isg-cover-stage {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 560px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.isg-cover-khatam { color: var(--isg-gold, #c9a961); }

.isg-stagger {
    opacity: 0;
    transform: translateY(14px);
    animation: isg-rise 700ms cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes isg-rise { to { opacity: 1; transform: none; } }

.isg-cover-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-gold, #c9a961);
    margin: 0 0 8px;
}
.isg-cover-name-ar {
    font-family: 'Amiri', serif;
    font-size: clamp(32px, 7vw, 44px);
    color: var(--isg-emerald, #0e4d3d);
    direction: rtl;
    line-height: 1.5;
    margin: 0;
}
.isg-cover-amp-ar {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: var(--isg-gold, #c9a961);
    direction: rtl;
}
.isg-cover-name-latin {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: clamp(32px, 7vw, 44px);
    color: var(--isg-emerald, #0e4d3d);
    margin: 0;
    line-height: 1.2;
}
.isg-cover-amp {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: var(--isg-gold, #c9a961);
}
.isg-divider {
    display: inline-block;
    width: 60px;
    height: 1px;
    background: var(--isg-gold, #c9a961);
    margin: 16px 0;
}
.isg-cover-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--isg-ink, #0a0a0a);
    margin: 12px 0 4px;
}
.isg-cover-venue {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--isg-ink-muted, #6b6b6b);
    margin: 0 0 24px;
}
.isg-btn {
    margin-top: 16px;
    background: transparent;
    color: var(--isg-emerald, #0e4d3d);
    border: 1px solid var(--isg-gold, #c9a961);
    padding: 14px 32px;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 200ms ease-out, color 200ms ease-out, border-color 200ms ease-out;
}
.isg-btn:hover {
    background: var(--isg-emerald, #0e4d3d);
    color: var(--isg-ivory, #f5efe3);
    border-color: var(--isg-emerald, #0e4d3d);
}
.isg-btn:focus-visible { outline: 2px solid var(--isg-gold, #c9a961); outline-offset: 2px; }
@media (prefers-reduced-motion: reduce) {
    .isg-stagger { animation: none; opacity: 1; transform: none; }
    .isg-btn { transition: none; }
}
</style>
