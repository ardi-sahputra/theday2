<script setup>
import { computed } from 'vue'
import BrandWatermark from '../BrandWatermark.vue'

const props = defineProps({
    material:     { type: String,  default: 'wood' },   // wood | gold | silver | crystal
    monogramText: { type: String,  default: 'A & B' },
    width:        { type: Number,  default: 400 },
    showWatermark:{ type: Boolean, default: true },
})

const fillMap = {
    wood:    '#6B4226',
    gold:    '#C9A961',
    silver:  '#8E8E93',
    crystal: 'rgba(164,197,219,0.35)',
}
const darkMap = {
    wood:    '#3D2614',
    gold:    '#8C7338',
    silver:  '#5C5C60',
    crystal: 'rgba(5,8,19,0.4)',
}
const baseFill = computed(() => fillMap[props.material] ?? fillMap.wood)
const baseDark = computed(() => darkMap[props.material] ?? darkMap.wood)
</script>

<template>
    <div class="sg-base-wrap" :style="{ width: width + 'px' }">
        <svg viewBox="0 0 600 140" class="sg-base" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
            <defs>
                <linearGradient id="sg-base-shade" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   :stop-color="baseFill"/>
                    <stop offset="100%" :stop-color="baseDark"/>
                </linearGradient>
            </defs>
            <!-- Trapezoid plinth -->
            <path d="M40 8 L560 8 L580 132 L20 132 Z" fill="url(#sg-base-shade)" stroke="#3D2614" stroke-width="1"/>
            <!-- Gold trim band (top edge, animated sweep) -->
            <foreignObject x="40" y="0" width="520" height="12">
                <div class="sg-base-trim" xmlns="http://www.w3.org/1999/xhtml"/>
            </foreignObject>
            <!-- Carved grooves -->
            <line x1="30"  y1="48" x2="570" y2="48" stroke="#3D2614" stroke-width="1" opacity="0.7"/>
            <line x1="28"  y1="96" x2="572" y2="96" stroke="#3D2614" stroke-width="1" opacity="0.7"/>
            <!-- Center plaque oval -->
            <ellipse cx="300" cy="72" rx="120" ry="22" fill="#3D2614" stroke="#C9A961" stroke-width="1.2"/>
            <!-- Monogram engraving -->
            <text
                x="300" y="80"
                class="sg-monogram-engrave"
                text-anchor="middle"
                font-family="'Italianno', 'Great Vibes', cursive"
                font-size="34"
                fill="#C9A961"
            >{{ monogramText }}</text>
            <!-- Watermark (free tier only) -->
            <foreignObject v-if="showWatermark" x="495" y="108" width="85" height="20">
                <div xmlns="http://www.w3.org/1999/xhtml" class="sg-watermark">
                    <BrandWatermark :height="14" muted/>
                </div>
            </foreignObject>
        </svg>
    </div>
</template>

<style scoped>
.sg-base-wrap {
    display: block;
    margin: 0 auto;
    line-height: 0;
}
.sg-base {
    display: block;
    width: 100%;
    height: auto;
    filter: drop-shadow(0 14px 22px rgba(0, 0, 0, 0.55));
}
.sg-monogram-engrave {
    letter-spacing: 0.06em;
    text-shadow: 0 1px 0 rgba(0,0,0,0.6);
}
.sg-base-trim {
    width: 100%;
    height: 12px;
    background-image: linear-gradient(90deg,
        var(--sg-gold-dim, #8C7338) 0%,
        var(--sg-gold, #C9A961) 40%,
        var(--sg-fire-deep, #E0B870) 50%,
        var(--sg-gold, #C9A961) 60%,
        var(--sg-gold-dim, #8C7338) 100%);
    background-size: 250% 100%;
    background-position: 100% 0%;
    animation: sg-base-sweep 5s linear infinite;
}
@keyframes sg-base-sweep {
    0%   { background-position: 100% 0%; }
    100% { background-position: -100% 0%; }
}
.sg-watermark {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    opacity: 0.6;
}
@media (prefers-reduced-motion: reduce) {
    .sg-base-trim { animation: none; background-position: 50% 0%; }
}
</style>
