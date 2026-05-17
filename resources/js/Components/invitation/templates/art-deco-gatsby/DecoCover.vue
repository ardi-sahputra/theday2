<script setup>
defineProps({
    coverUrl:     { type: String, default: null },
    monogram:     { type: String, default: '' },
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    eventDate:    { type: String, default: '' },
    musicPlaying: { type: Boolean, default: false },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<template>
    <div class="deco-cover deco-visible">
        <div
            class="deco-cover-bg"
            :style="coverUrl
                ? { backgroundImage: `linear-gradient(rgba(13,13,13,0.4), rgba(13,13,13,0.85)), url(${coverUrl})` }
                : { background: '#0d0d0d' }"
        />

        <!-- 4 corner brackets -->
        <span class="deco-corner deco-corner--tl"/>
        <span class="deco-corner deco-corner--tr"/>
        <span class="deco-corner deco-corner--bl"/>
        <span class="deco-corner deco-corner--br"/>

        <button
            class="deco-cover-music"
            type="button"
            @click="emit('toggle-music')"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Nyalakan musik'"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <div class="deco-cover-center">
            <div class="deco-cover-monogram">{{ monogram }}</div>
            <span class="deco-cover-line"/>
            <p class="deco-cover-eyebrow">THE WEDDING OF</p>
            <h1 class="deco-cover-names">{{ groomName }} &amp; {{ brideName }}</h1>
            <p v-if="eventDate" class="deco-cover-date">{{ eventDate }}</p>
            <svg class="deco-cover-fan" viewBox="0 0 200 80" fill="none" aria-hidden="true">
                <path d="M100 76 A 18 18 0 0 1 100 40" stroke="currentColor" stroke-width="1.5"/>
                <path d="M100 76 A 28 28 0 0 1 88 22"  stroke="currentColor" stroke-width="1.5"/>
                <path d="M100 76 A 28 28 0 0 1 112 22" stroke="currentColor" stroke-width="1.5"/>
                <path d="M100 76 A 40 40 0 0 1 72 18"  stroke="currentColor" stroke-width="1.5"/>
                <path d="M100 76 A 40 40 0 0 1 128 18" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <button type="button" class="deco-cover-cta" @click="emit('open')">
                BUKA UNDANGAN
            </button>
        </div>
    </div>
</template>

<style scoped>
.deco-cover {
    position: fixed; inset: 0; z-index: 50;
    color: #c9a961; background: #0d0d0d;
    overflow: hidden;
    font-family: 'Lato', system-ui, sans-serif;
}
.deco-cover-bg {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.deco-corner {
    position: absolute; width: 40px; height: 40px;
    border-top:  1.5px solid currentColor;
    border-left: 1.5px solid currentColor;
    opacity: 0;
    animation: deco-corner-fade 0.5s ease-out forwards;
}
.deco-corner--tl { top: 20px;    left: 20px;    animation-delay: 0.0s; }
.deco-corner--tr { top: 20px;    right: 20px;   transform: rotate(90deg);  animation-delay: 0.1s; }
.deco-corner--bl { bottom: 20px; left: 20px;    transform: rotate(-90deg); animation-delay: 0.2s; }
.deco-corner--br { bottom: 20px; right: 20px;   transform: rotate(180deg); animation-delay: 0.3s; }
@keyframes deco-corner-fade { to { opacity: 1; } }

.deco-cover-music {
    position: absolute; top: 28px; right: 72px; z-index: 3;
    width: 44px; height: 44px;
    background: transparent; border: 1.5px solid currentColor; border-radius: 0;
    color: #c9a961; font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}

.deco-cover-center {
    position: relative; z-index: 2;
    width: 100%; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center; padding: 0 24px;
}
.deco-cover-monogram {
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: clamp(72px, 18vw, 120px); color: #c9a961;
    line-height: 1; letter-spacing: 0.05em;
    animation: deco-cover-fade 0.7s ease-out 0.3s both;
}
.deco-cover-line {
    display: block; width: 80px; height: 1.5px;
    background: currentColor; margin: 18px 0;
}
.deco-cover-eyebrow {
    font-size: 11px; letter-spacing: 0.4em;
    color: rgba(244,234,213,0.85); margin: 0 0 12px;
    animation: deco-cover-fade 0.7s ease-out 0.45s both;
}
.deco-cover-names {
    font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 600;
    font-size: clamp(22px, 6vw, 32px); margin: 0;
    color: #c9a961; letter-spacing: 0.15em;
    animation: deco-cover-fade 0.7s ease-out 0.6s both;
}
.deco-cover-date {
    margin: 14px 0 0;
    font-size: 14px; letter-spacing: 0.25em;
    color: rgba(244,234,213,0.7);
    font-variant-numeric: tabular-nums;
    animation: deco-cover-fade 0.7s ease-out 0.75s both;
}
.deco-cover-fan {
    width: 120px; height: 48px; margin-top: 18px; color: #c9a961;
    animation: deco-cover-fade 0.7s ease-out 0.9s both;
}
.deco-cover-cta {
    margin-top: 28px;
    background: transparent;
    border: 1.5px solid #c9a961; border-radius: 2px;
    color: #c9a961;
    padding: 14px 32px;
    font-family: 'Lato', system-ui, sans-serif;
    font-size: 12px; font-weight: 700; letter-spacing: 0.4em;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    animation: deco-cover-pulse 2.4s ease-in-out infinite, deco-cover-fade 0.7s ease-out 1.05s both;
}
.deco-cover-cta:hover { background: #1a3a2e; color: #f4ead5; }
@keyframes deco-cover-fade {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes deco-cover-pulse {
    0%, 100% { box-shadow: 0 0 0 rgba(201,169,97,0); }
    50%      { box-shadow: 0 0 18px rgba(201,169,97,0.35); }
}
@media (prefers-reduced-motion: reduce) {
    .deco-corner,
    .deco-cover-monogram,
    .deco-cover-eyebrow,
    .deco-cover-names,
    .deco-cover-date,
    .deco-cover-fan,
    .deco-cover-cta { animation: none !important; opacity: 1 !important; transform: none !important; box-shadow: none !important; }
}
</style>
