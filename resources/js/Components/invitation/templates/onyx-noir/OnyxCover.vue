<script setup>
defineProps({
    coverUrl:     { type: String,  default: null },
    groomNick:    { type: String,  default: '' },
    brideNick:    { type: String,  default: '' },
    eventDate:    { type: String,  default: '' },
    musicPlaying: { type: Boolean, default: false },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<template>
    <div class="onyx-cover">
        <div
            class="onyx-cover-photo"
            :style="coverUrl ? { backgroundImage: `url(${coverUrl})` } : { background: '#1a1a1a' }"
        />
        <div class="onyx-cover-overlay"/>
        <div class="onyx-cover-marble"/>

        <span class="onyx-corner onyx-corner--tl" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
                <path d="M2 18 L2 2 L18 2"/>
                <path d="M6 14 L6 6 L14 6"/>
                <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
            </svg>
        </span>
        <span class="onyx-corner onyx-corner--tr" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
                <path d="M2 18 L2 2 L18 2"/>
                <path d="M6 14 L6 6 L14 6"/>
                <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
            </svg>
        </span>
        <span class="onyx-corner onyx-corner--bl" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
                <path d="M2 18 L2 2 L18 2"/>
                <path d="M6 14 L6 6 L14 6"/>
                <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
            </svg>
        </span>
        <span class="onyx-corner onyx-corner--br" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
                <path d="M2 18 L2 2 L18 2"/>
                <path d="M6 14 L6 6 L14 6"/>
                <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
            </svg>
        </span>

        <button class="onyx-cover-music" @click.stop="emit('toggle-music')" aria-label="Toggle musik">
            {{ musicPlaying ? '&#9834;' : '&#9835;' }}
        </button>

        <div class="onyx-cover-content">
            <p class="onyx-cover-eyebrow">THE WEDDING OF</p>
            <h1 class="onyx-cover-names">{{ groomNick }} &amp; {{ brideNick }}</h1>
            <span class="onyx-rule"/>
            <p class="onyx-cover-date">{{ eventDate }}</p>
            <button class="onyx-btn onyx-cover-cta" @click="emit('open')">BUKA UNDANGAN</button>
        </div>
    </div>
</template>

<style scoped>
.onyx-cover {
    position: fixed; inset: 0; z-index: 30;
    overflow: hidden;
    color: #f5f5f0;
}
.onyx-cover-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.onyx-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(10,10,10,0.55) 0%, rgba(10,10,10,0.85) 100%);
}
.onyx-cover-marble {
    position: absolute; inset: 0;
    background: url('/images/templates/onyx-noir/veins.svg') center/cover no-repeat;
    mix-blend-mode: overlay;
    opacity: 0.4;
    pointer-events: none;
}
.onyx-corner { position: absolute; width: 48px; height: 48px; pointer-events: none; }
.onyx-corner svg { width: 100%; height: 100%; }
.onyx-corner--tl { top: 24px; left: 24px; }
.onyx-corner--tr { top: 24px; right: 24px; transform: scaleX(-1); }
.onyx-corner--bl { bottom: 24px; left: 24px; transform: scaleY(-1); }
.onyx-corner--br { bottom: 24px; right: 24px; transform: scale(-1, -1); }

.onyx-cover-music {
    position: absolute; top: 24px; right: 96px;
    width: 40px; height: 40px;
    border: 1px solid #d4af37;
    background: transparent;
    border-radius: 50%;
    color: #d4af37;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    z-index: 2;
}
.onyx-cover-content {
    position: relative; z-index: 1;
    height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 18px;
    padding: 0 32px;
    text-align: center;
}
.onyx-cover-eyebrow {
    font-family: 'Tenor Sans', sans-serif;
    color: #d4af37;
    letter-spacing: 0.4em;
    font-size: 12px;
    margin: 0;
}
.onyx-cover-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-weight: 400;
    color: #f5f5f0;
    font-size: 56px;
    line-height: 1.1;
    margin: 0;
}
@media (max-width: 480px) {
    .onyx-cover-names { font-size: 40px; }
}
.onyx-rule {
    display: block;
    width: 60px; height: 1px;
    background: #d4af37;
}
.onyx-cover-date {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    color: #f5f5f0;
    margin: 0;
}
.onyx-cover-cta { margin-top: 16px; }
.onyx-btn {
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: #d4af37;
    font-family: 'Tenor Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid #d4af37;
    cursor: pointer;
    transition: color 0.3s ease, background 0.3s ease;
}
.onyx-btn:hover { background: #d4af37; color: #0a0a0a; }
@media (prefers-reduced-motion: reduce) {
    .onyx-btn { transition: none; }
}
</style>
