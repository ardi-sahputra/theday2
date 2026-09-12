<script setup>
defineProps({
    groomNick:  { type: String, default: '' },
    brideNick:  { type: String, default: '' },
    eventDate:  { type: String, default: '' },
    guestName:  { type: String, default: '' },
    primary:    { type: String, default: '#7A6A52' },
    accent:     { type: String, default: '#8C9B7E' },
    fontTitle:  { type: String, default: 'Cormorant Garamond' },
    fontHeading:{ type: String, default: 'Cormorant Garamond' },
    animating:  { type: Boolean, default: false },
})

const emit = defineEmits(['open'])
</script>

<template>
    <div class="sk-cover">
        <img class="sk-cover-botany sk-cover-botany--l" src="/images/templates/sketchbook/botany-left.png" alt="" aria-hidden="true" draggable="false"/>
        <img class="sk-cover-botany sk-cover-botany--r" src="/images/templates/sketchbook/botany-right.png" alt="" aria-hidden="true" draggable="false"/>

        <div class="sk-cover-inner">
            <p class="sk-cover-label sk-stagger" style="--d: 0.05s">Buku Sketsa Pernikahan</p>
            <h1
                class="sk-cover-title sk-stagger"
                style="--d: 0.18s"
                :style="{ fontFamily: fontTitle, color: primary }"
            >{{ groomNick }} &amp; {{ brideNick }}</h1>
            <p v-if="eventDate" class="sk-cover-date sk-stagger" style="--d: 0.3s" :style="{ fontFamily: fontHeading }">{{ eventDate }}</p>
            <p v-if="guestName" class="sk-cover-guest sk-stagger" style="--d: 0.4s">Kepada Yth. {{ guestName }}</p>

            <button
                type="button"
                class="sk-cover-btn sk-stagger"
                style="--d: 0.52s"
                :style="{ background: primary }"
                :disabled="animating"
                @click="emit('open')"
            >{{ animating ? 'Membuka…' : 'Buka Buku Sketsa' }}</button>
        </div>
    </div>
</template>

<style scoped>
.sk-cover {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: default;
}
.sk-cover-botany {
    position: absolute;
    bottom: 0;
    width: clamp(130px, 20vw, 260px);
    opacity: 0.55;
    pointer-events: none;
    user-select: none;
    z-index: 0;
}
.sk-cover-botany--l { left: clamp(-40px, -2vw, 0px); bottom: 0; }
.sk-cover-botany--r { right: clamp(-30px, -1vw, 10px); bottom: -2%; width: clamp(110px, 16vw, 210px); }
@media (max-width: 640px) { .sk-cover-botany { display: none; } }

.sk-cover-inner { position: relative; text-align: center; padding: 24px; max-width: 420px; }
.sk-cover-label {
    font-size: 12px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(43, 39, 33, 0.58);
    margin: 0 0 12px;
}
.sk-cover-title { font-size: clamp(36px, 9vw, 56px); margin: 0 0 12px; line-height: 1.1; }
.sk-cover-date { font-size: 16px; margin: 0 0 8px; color: rgba(43, 39, 33, 0.72); }
.sk-cover-guest { font-size: 14px; margin: 0 0 24px; color: rgba(43, 39, 33, 0.58); }

.sk-cover-btn {
    border: none;
    color: #fff;
    padding: 14px 36px;
    border-radius: 999px;
    font-size: 14px;
    letter-spacing: 0.06em;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 8px 20px rgba(43, 39, 33, 0.25);
}
.sk-cover-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(43, 39, 33, 0.3); }
.sk-cover-btn:disabled { opacity: 0.7; cursor: progress; }

.sk-stagger {
    opacity: 0;
    transform: translateY(16px);
    animation: sk-rise 0.7s cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes sk-rise {
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .sk-stagger { animation: none; opacity: 1; transform: none; }
    .sk-cover-btn { transition: none; }
}
</style>
