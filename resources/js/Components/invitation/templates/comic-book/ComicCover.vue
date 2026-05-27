<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { ref } from 'vue'
import HalftoneDots from './HalftoneDots.vue'
import SoundEffect  from './SoundEffect.vue'

const props = defineProps({
    coverPhoto:      { type: String,  default: null },
    groomNick:       { type: String,  default: '' },
    brideNick:       { type: String,  default: '' },
    eventDate:       { type: String,  default: '' },
    issueNumber:     { type: String,  default: '001' },
    coverTitle:      { type: String,  default: 'THE WEDDING' },
    coverPrice:      { type: String,  default: 'Rp25.000' },
    guestName:       { type: String,  default: 'Tamu Undangan' },
    sfxEnabled:      { type: Boolean, default: true },
    hatchingEnabled: { type: Boolean, default: true },
})
const emit = defineEmits(['open'])

const opening = ref(false)

function onOpen() {
    if (opening.value) return
    opening.value = true
    setTimeout(() => emit('open'), 1200)
}
</script>

<template>
    <section class="cb-cover" :class="{ 'cb-cover--opening': opening }" @click="onOpen">
        <HalftoneDots density="medium" tint="red" :opacity="0.22" :shimmer="true"/>

        <header class="cb-cover-masthead">
            <div class="cb-cover-issue">
                <span class="cb-cover-issue-lbl">ISSUE</span>
                <span class="cb-cover-issue-num">#{{ issueNumber }}</span>
            </div>
            <h2 class="cb-cover-banner">THE WEDDING CHRONICLES</h2>
            <span class="cb-cover-price">{{ coverPrice }}</span>
        </header>

        <div class="cb-cover-hero">
            <img v-if="coverPhoto"
                 :src="coverPhoto"
                 alt=""
                 class="cb-cover-photo"
                 :style="{ filter: hatchingEnabled ? 'url(#cb-pencil-hatch)' : 'none' }"/>
            <img v-else
                 src="/images/templates/comic-book/cover-illustration.svg"
                 alt="" class="cb-cover-photo cb-cover-photo--illustration"/>

            <div class="cb-cover-sfx">
                <SoundEffect variant="kapow" size="lg" :enabled="sfxEnabled"/>
            </div>

            <div class="cb-cover-title-wrap">
                <h1 class="cb-cover-title">{{ coverTitle }}</h1>
                <p class="cb-cover-couple">{{ groomNick }} &amp; {{ brideNick }}</p>
                <p class="cb-cover-date">{{ eventDate }}</p>
            </div>
        </div>

        <div class="cb-cover-cta-wrap">
            <button type="button" class="cb-cover-cta" @click.stop="onOpen">
                <span class="cb-cover-cta-arrow" aria-hidden="true">&#9654;</span>
                OPEN ISSUE
            </button>
        </div>

        <footer class="cb-cover-foot">
            <span class="cb-cover-imprint">EST. 2026 — Theday Publishing</span>
            <span class="cb-cover-reader">READER NO. {{ guestName }}</span>
        </footer>
    </section>
</template>

<style scoped>
.cb-cover {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    background: #F9F4E2;
    border: 8px solid #0A0A0A;
    box-sizing: border-box;
    padding: 24px 20px 32px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    overflow: hidden;
    cursor: pointer;
    transform-origin: left center;
    transform-style: preserve-3d;
    backface-visibility: hidden;
    transition: transform 0.8s cubic-bezier(0.65, 0, 0.35, 1),
                opacity   0.4s ease 0.8s;
}
.cb-cover--opening {
    transform: rotateY(-90deg);
    opacity: 0;
    box-shadow: 8px 0 24px rgba(10, 10, 10, 0.18);
}

.cb-cover-masthead {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    z-index: 2;
}
.cb-cover-issue {
    width: 60px; height: 60px;
    background: #F1C453;
    border: 3px solid #0A0A0A;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cb-cover-issue-lbl {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 9px;
    color: #0A0A0A;
}
.cb-cover-issue-num {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 18px;
    color: #E63946;
    line-height: 1;
}
.cb-cover-banner {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 18px;
    letter-spacing: 0.12em;
    text-align: center;
    color: #0A0A0A;
    text-transform: uppercase;
    margin: 0;
    flex: 1;
}
.cb-cover-price {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 11px;
    color: #0A0A0A;
    flex-shrink: 0;
}

.cb-cover-hero {
    position: relative;
    flex: 1;
    border: 4px solid #0A0A0A;
    background: #FCE7E9;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    z-index: 2;
}
.cb-cover-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cb-cover-photo--illustration {
    object-fit: contain;
    object-position: center;
    background: #F9F4E2;
}
.cb-cover-sfx {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 3;
}
.cb-cover-title-wrap {
    position: relative;
    z-index: 3;
    background: linear-gradient(to top, rgba(249, 244, 226, 0.95), rgba(249, 244, 226, 0));
    width: 100%;
    padding: 24px 16px 16px;
    text-align: center;
}
.cb-cover-title {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 56px;
    line-height: 0.95;
    color: #E63946;
    -webkit-text-stroke: 2px #0A0A0A;
    paint-order: stroke fill;
    margin: 0;
    transform: rotate(-3deg);
    letter-spacing: 0.02em;
}
@media (min-width: 768px) {
    .cb-cover-title { font-size: 96px; -webkit-text-stroke-width: 4px; }
}
.cb-cover-couple {
    font-family: 'Comic Neue', 'Comic Sans MS', sans-serif;
    font-weight: 700;
    font-size: 15px;
    text-transform: uppercase;
    color: #0A0A0A;
    margin: 12px 0 6px;
    letter-spacing: 0.06em;
}
.cb-cover-date {
    display: inline-block;
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 13px;
    background: #0A0A0A;
    color: #FFFFFF;
    padding: 4px 12px;
    margin: 0;
    letter-spacing: 0.05em;
}

.cb-cover-cta-wrap { text-align: center; z-index: 2; }
.cb-cover-cta {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #E63946;
    color: #FFFFFF;
    border: 4px solid #0A0A0A;
    padding: 14px 28px;
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 20px;
    letter-spacing: 0.16em;
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.cb-cover-cta:hover { transform: scale(1.05) rotate(-2deg); }
.cb-cover-cta-arrow { font-size: 14px; }

.cb-cover-foot {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    font-size: 11px;
    color: #0A0A0A;
    z-index: 2;
}
.cb-cover-imprint { font-family: 'Bowlby One', Impact, sans-serif; opacity: 0.75; }
.cb-cover-reader  { font-family: 'Permanent Marker', cursive; }

@media (prefers-reduced-motion: reduce) {
    .cb-cover { transition: opacity 0.3s ease; }
    .cb-cover--opening { transform: none; opacity: 0; box-shadow: none; }
    .cb-cover-cta { transition: none; }
    .cb-cover-cta:hover { transform: none; }
}
</style>
