<script setup>
import RyokanSumiStroke from './RyokanSumiStroke.vue'
import RyokanTategaki   from './RyokanTategaki.vue'

defineProps({
    coverPhotoUrl:  { type: String,  default: null },
    groomName:      { type: String,  default: '' },
    brideName:      { type: String,  default: '' },
    firstEventDate: { type: String,  default: '' },
    fujiVisible:    { type: Boolean, default: false },
    musicPlaying:   { type: Boolean, default: false },
})
const emit = defineEmits(['advance', 'toggle-music'])
</script>

<template>
    <div class="ryokan-cover">
        <div
            class="ryokan-cover-photo"
            :style="coverPhotoUrl
                ? { backgroundImage: `url(${coverPhotoUrl})` }
                : { background: '#1c2e4a' }"
        />
        <div class="ryokan-cover-overlay" />
        <div class="ryokan-cover-grain" aria-hidden="true" />

        <img
            v-if="fujiVisible"
            src="/images/templates/japanese-ryokan/fuji-silhouette.svg"
            alt=""
            class="ryokan-cover-fuji"
            aria-hidden="true"
        />

        <p class="ryokan-cover-mark">THE&nbsp;DAY</p>

        <button
            class="ryokan-cover-music"
            @click.stop="emit('toggle-music')"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <RyokanTategaki
            :text="firstEventDate || '・'"
            :size="14"
            color="#f3ede4"
            :revealed="true"
            class="ryokan-cover-date"
        />

        <div class="ryokan-cover-content">
            <h1 class="ryokan-cover-names">
                {{ groomName }}<br><span class="ryokan-cover-amp">&amp;</span><br>{{ brideName }}
            </h1>
            <RyokanSumiStroke :variant="2" :width="260" class="ryokan-cover-stroke" />
            <p class="ryokan-cover-tag">with their families joyfully invite you</p>
            <button class="ryokan-cover-cta" @click="emit('advance')">
                <span>Geser ke bawah</span>
                <span class="ryokan-cover-arrow">↓</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.ryokan-cover {
    position: fixed;
    inset: 0;
    z-index: 30;
    overflow: hidden;
    color: #f3ede4;
}
.ryokan-cover-photo {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}
.ryokan-cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(243, 237, 228, 0.85) 100%);
}
.ryokan-cover-grain {
    position: absolute;
    inset: 0;
    background: url('/images/templates/japanese-ryokan/washi-grain.svg') repeat;
    background-size: 200px 200px;
    opacity: 0.25;
    mix-blend-mode: multiply;
    pointer-events: none;
}
.ryokan-cover-fuji {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 28%;
    width: 100%;
    opacity: 0.55;
    pointer-events: none;
}
.ryokan-cover-mark {
    position: absolute;
    top: 18px;
    left: 20px;
    font-family: 'Cormorant Garamond', serif;
    color: #1c2e4a;
    font-size: 11px;
    letter-spacing: 0.3em;
    margin: 0;
    opacity: 0.7;
}
.ryokan-cover-music {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid rgba(28, 46, 74, 0.5);
    background: #f3ede4;
    color: #1c2e4a;
    cursor: pointer;
    z-index: 2;
}
.ryokan-cover-date {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #f3ede4;
    text-shadow: 0 1px 4px rgba(28, 46, 74, 0.6);
}
.ryokan-cover-content {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 12vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 0 24px;
    text-align: center;
    z-index: 1;
}
.ryokan-cover-names {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', 'Cormorant Garamond', serif;
    font-weight: 400;
    color: #1c2e4a;
    font-size: 44px;
    line-height: 1.1;
    margin: 0;
    text-shadow: 0 1px 12px rgba(243, 237, 228, 0.6);
}
.ryokan-cover-amp {
    color: #8c6b3f;
    font-style: italic;
    font-size: 32px;
}
.ryokan-cover-tag {
    font-family: 'Cormorant Garamond', serif;
    color: #1c2e4a;
    font-style: italic;
    font-size: 14px;
    margin: 0;
    opacity: 0.8;
}
.ryokan-cover-cta {
    background: transparent;
    border: none;
    cursor: pointer;
    color: #1c2e4a;
    font-family: 'Cormorant Garamond', serif;
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
}
.ryokan-cover-arrow {
    font-size: 18px;
    animation: ryokan-arrow-bob 2.4s ease-in-out infinite;
}
@keyframes ryokan-arrow-bob {
    0%, 100% { transform: translateY(0); opacity: 0.6; }
    50%      { transform: translateY(4px); opacity: 1; }
}
@media (max-width: 480px) {
    .ryokan-cover-names { font-size: 32px; }
}
@media (prefers-reduced-motion: reduce) {
    .ryokan-cover-arrow { animation: none; opacity: 0.8; }
}
</style>
