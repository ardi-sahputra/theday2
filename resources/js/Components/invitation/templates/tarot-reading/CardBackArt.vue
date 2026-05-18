<script setup>
defineProps({
    monogram:  { type: String,  default: 'G & B' },
    watermark: { type: Boolean, default: false },
})
</script>

<template>
    <div class="tr-cb" aria-hidden="true">
        <svg class="tr-cb-svg" viewBox="0 0 320 552" preserveAspectRatio="xMidYMid slice">
            <rect width="320" height="552" fill="#2D1B4E"/>
            <rect x="10" y="10" width="300" height="532" rx="10"
                  fill="none" stroke="#D4AF37" stroke-width="3"/>
            <rect x="22" y="22" width="276" height="508" rx="6"
                  fill="none" stroke="#D4AF37" stroke-width="0.8" opacity="0.45"/>

            <!-- Corner ornaments -->
            <g fill="#D4AF37">
                <circle cx="44" cy="44" r="3"/>
                <circle cx="276" cy="44" r="3"/>
                <circle cx="44" cy="508" r="3"/>
                <circle cx="276" cy="508" r="3"/>
                <path d="M30 70 L34 78 L42 78 L36 84 L38 92 L30 88 L22 92 L24 84 L18 78 L26 78 Z" opacity="0.55"/>
                <path d="M290 70 L294 78 L302 78 L296 84 L298 92 L290 88 L282 92 L284 84 L278 78 L286 78 Z" opacity="0.55"/>
                <path d="M30 482 L34 490 L42 490 L36 496 L38 504 L30 500 L22 504 L24 496 L18 490 L26 490 Z" opacity="0.55"/>
                <path d="M290 482 L294 490 L302 490 L296 496 L298 504 L290 500 L282 504 L284 496 L278 490 L286 490 Z" opacity="0.55"/>
            </g>

            <!-- Top crescents + center star -->
            <g transform="translate(160 110)">
                <path d="M-50 0 a14 14 0 1 0 0 0.1 z" fill="#D4AF37" opacity="0.7"/>
                <path d="M50 0 a14 14 0 1 1 0 -0.1 z" fill="#D4AF37" opacity="0.7"/>
                <text text-anchor="middle" dominant-baseline="central"
                      font-size="20" fill="#D4AF37">&#10022;</text>
            </g>

            <!-- Center filigree circles -->
            <g transform="translate(160 276)" fill="none">
                <circle r="82" stroke="#D4AF37" stroke-width="1.5"/>
                <circle r="68" stroke="#D4AF37" stroke-width="0.8" opacity="0.5"/>
                <circle r="94" stroke="#D4AF37" stroke-width="0.5" opacity="0.3"/>
            </g>

            <!-- Bottom mirror -->
            <g transform="translate(160 442)">
                <path d="M-50 0 a14 14 0 1 0 0 0.1 z" fill="#D4AF37" opacity="0.7"/>
                <path d="M50 0 a14 14 0 1 1 0 -0.1 z" fill="#D4AF37" opacity="0.7"/>
                <text text-anchor="middle" dominant-baseline="central"
                      font-size="20" fill="#D4AF37">&#10022;</text>
            </g>
        </svg>

        <!-- Monogram -->
        <div class="tr-cb-monogram-wrap">
            <span class="tr-monogram">{{ monogram }}</span>
        </div>

        <!-- Watermark slot -->
        <div v-if="watermark" class="tr-cb-watermark">
            <slot name="watermark"/>
        </div>
    </div>
</template>

<style scoped>
.tr-cb {
    position: absolute;
    inset: 0;
    overflow: hidden;
    border-radius: inherit;
}
.tr-cb-svg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
}
.tr-cb-monogram-wrap {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.tr-monogram {
    font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-size: clamp(28px, 4vw, 38px);
    letter-spacing: 0.04em;
    background-image: linear-gradient(110deg,
        #9B8327 0%,
        #D4AF37 45%,
        #F3E5A0 50%,
        #D4AF37 55%,
        #9B8327 100%);
    background-size: 200% 100%;
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    animation: tr-monogram-shimmer 6s ease-in-out infinite;
}
@keyframes tr-monogram-shimmer {
    0%, 100% { background-position: 0% 0; }
    50%      { background-position: 100% 0; }
}
.tr-cb-watermark {
    position: absolute;
    bottom: 8px;
    left: 50%;
    transform: translateX(-50%);
    pointer-events: none;
    opacity: 0.6;
}
@media (prefers-reduced-motion: reduce) {
    .tr-monogram { animation: none; background-position: 50% 0; }
}
</style>
