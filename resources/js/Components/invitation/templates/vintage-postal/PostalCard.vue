<script setup>
import { computed } from 'vue'
import PostalStamp    from './PostalStamp.vue'
import PostalPostmark from './PostalPostmark.vue'
import PostalWashiTape from './PostalWashiTape.vue'

const props = defineProps({
    paper:     { type: String,  default: 'cream' },       // 'cream' | 'aged-1' | 'aged-2' | 'aged-3' | 'light'
    rotation:  { type: Number,  default: -1 },
    postmark:  { type: Object,  default: null },          // { variant, date, city, position }
    stamps:    { type: Array,   default: () => [] },      // [{ city?, theme?, position, rotate? }]
    washi:     { type: Object,  default: null },          // { pattern, position }
    ariaLabel: { type: String,  default: null },
})

const paperUrl = computed(() => {
    const map = {
        'cream':   '/images/templates/vintage-postal/kraft.webp',
        'aged-1':  '/images/templates/vintage-postal/paper-aged-1.webp',
        'aged-2':  '/images/templates/vintage-postal/paper-aged-2.webp',
        'aged-3':  '/images/templates/vintage-postal/paper-aged-3.webp',
        'light':   '/images/templates/vintage-postal/paper-aged-1.webp',
    }
    return map[props.paper] ?? map.cream
})

const cardStyle = computed(() => ({
    backgroundImage: `url(${paperUrl.value})`,
    transform: `rotate(${props.rotation}deg)`,
}))

function positionToStyle(pos) {
    const map = {
        'tl': { top: '-18px', left:  '-12px' },
        'tr': { top: '-18px', right: '-12px' },
        'bl': { bottom: '-18px', left:  '-12px' },
        'br': { bottom: '-18px', right: '-12px' },
        'center-top':    { top: '-22px',  left: '50%', transform: 'translateX(-50%)' },
        'center-bottom': { bottom: '-22px', left: '50%', transform: 'translateX(-50%)' },
    }
    return map[pos] ?? map.tr
}
</script>

<template>
    <article class="vp-card" :style="cardStyle" :aria-label="ariaLabel">
        <PostalWashiTape
            v-if="washi"
            :pattern="washi.pattern"
            :position="washi.position ?? 'top'"
            class="vp-card-washi"
        />

        <header v-if="$slots.header" class="vp-card-header">
            <slot name="header"/>
        </header>

        <div class="vp-card-body">
            <slot/>
        </div>

        <PostalPostmark
            v-if="postmark"
            :variant="postmark.variant"
            :date="postmark.date"
            :city="postmark.city"
            class="vp-card-postmark"
            :style="positionToStyle(postmark.position ?? 'tr')"
        />

        <PostalStamp
            v-for="(s, i) in stamps"
            :key="`stamp-${i}`"
            :city="s.city"
            :theme="s.theme"
            :rotate="s.rotate ?? -3"
            class="vp-card-stamp"
            :style="positionToStyle(s.position)"
        />
    </article>
</template>

<style scoped>
.vp-card {
    position: relative;
    background-color: #e8dcc4;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border: 1px solid rgba(92, 74, 58, 0.3);
    border-radius: 4px;
    padding: 32px 28px;
    margin: 24px auto;
    max-width: 560px;
    box-shadow:
        0 1px 2px rgba(58, 45, 31, 0.18),
        0 8px 24px rgba(58, 45, 31, 0.14);
    color: #3a2d1f;
    overflow: visible;
}
.vp-card-header {
    margin-bottom: 16px;
    text-align: center;
}
.vp-card-body { position: relative; }
.vp-card-postmark { position: absolute; z-index: 2; }
.vp-card-stamp    { position: absolute; z-index: 3; }
.vp-card-washi    { position: absolute; left: 24px; right: 24px; top: -14px; z-index: 4; }
@media (max-width: 480px) {
    .vp-card { padding: 24px 20px; margin: 16px 12px; }
}
@media (prefers-reduced-motion: reduce) {
    .vp-card { transform: none !important; }
}
</style>
