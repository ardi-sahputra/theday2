<script setup>
import PostalPostmark from './PostalPostmark.vue'
import PostalTypewriter from './PostalTypewriter.vue'

defineProps({
    coverUrl:      { type: String, default: null },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
    firstEventDate:{ type: String, default: '' },
    musicPlaying:  { type: Boolean, default: false },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<template>
    <div class="vp-cover">
        <div
            class="vp-cover-photo"
            :style="coverUrl ? { backgroundImage: `url(${coverUrl})` } : { background: '#5c4a3a' }"
        />
        <div class="vp-cover-tone"/>
        <div class="vp-cover-frame"/>

        <PostalPostmark
            class="vp-cover-postmark"
            variant="posted"
            :date="firstEventDate"
        />

        <span class="vp-cover-firstclass">FIRST CLASS &middot; No. 001</span>

        <button class="vp-cover-music" @click.stop="emit('toggle-music')" aria-label="Toggle musik">
            {{ musicPlaying ? '&#9834;' : '&#9835;' }}
        </button>

        <div class="vp-cover-bottom">
            <PostalTypewriter
                class="vp-cover-names"
                :text="`${groomNick} & ${brideNick}`"
                mode="handwriting"
                :skippable="false"
            />
            <span class="vp-cover-sd">Save the Date</span>
            <p class="vp-cover-date">{{ firstEventDate }}</p>
            <button class="vp-cover-cta" @click="emit('open')">BUKA KARTU POS</button>
        </div>
    </div>
</template>

<style scoped>
.vp-cover {
    position: fixed; inset: 0; z-index: 30;
    overflow: hidden;
    color: #f4ead5;
    background: #3a2d1f;
}
.vp-cover-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    filter: sepia(45%) brightness(0.92);
}
.vp-cover-tone {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(92,74,58,0.18) 0%, rgba(92,74,58,0.55) 100%);
}
.vp-cover-frame {
    position: absolute; inset: 16px;
    border: 12px solid #d8c8a0;
    box-shadow:
        inset 0 0 0 4px transparent,
        inset 0 0 0 5px #5c4a3a;
    pointer-events: none;
}
.vp-cover-postmark {
    position: absolute; top: 48px; right: 48px;
    width: 96px; height: 96px;
    transform: rotate(-8deg);
}
.vp-cover-firstclass {
    position: absolute; top: 48px; left: 48px;
    padding: 6px 12px;
    background: #f4ead5;
    color: #3a2d1f;
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 11px;
    letter-spacing: 2px;
}
.vp-cover-music {
    position: absolute; top: 48px; right: 168px;
    width: 40px; height: 40px;
    border: 1px solid #f4ead5;
    background: transparent;
    border-radius: 50%;
    color: #f4ead5;
    cursor: pointer;
    z-index: 2;
}
.vp-cover-bottom {
    position: absolute;
    left: 0; right: 0; bottom: 48px;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    padding: 0 24px;
    text-align: center;
}
.vp-cover-names {
    font-family: 'Homemade Apple', cursive;
    color: #f4ead5;
    font-size: 36px;
}
.vp-cover-sd {
    display: inline-block;
    padding: 8px 18px;
    background: #8b3a3a;
    color: #f4ead5;
    font-family: 'Special Elite', monospace;
    font-size: 14px;
    letter-spacing: 4px;
    text-transform: uppercase;
}
.vp-cover-date {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    color: #f4ead5;
    font-size: 22px;
    margin: 4px 0 0;
}
.vp-cover-cta {
    margin-top: 12px;
    padding: 14px 28px;
    background: #f4ead5;
    color: #8b3a3a;
    border: 1px solid #8b3a3a;
    font-family: 'Special Elite', monospace;
    font-size: 12px;
    letter-spacing: 3px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.vp-cover-cta:hover { background: #8b3a3a; color: #f4ead5; }
@media (max-width: 480px) {
    .vp-cover-postmark { top: 32px; right: 32px; width: 72px; height: 72px; }
    .vp-cover-music    { top: 32px; right: 116px; }
    .vp-cover-firstclass { top: 32px; left: 32px; }
    .vp-cover-names { font-size: 28px; }
}
@media (prefers-reduced-motion: reduce) {
    .vp-cover-cta { transition: none; }
}
</style>
