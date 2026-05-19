<script setup>
import { computed } from 'vue'

const props = defineProps({
    sceneKey:   { type: String, default: 'opening' },
    galleries:  { type: Array,  default: () => [] },
})

// Pick up to 6 polaroid sources (cycling); fallback to placeholder grays.
const polaroidSources = computed(() => {
    const list = (props.galleries || [])
        .map(g => g.image_url ?? g.file_url)
        .filter(Boolean)
    if (list.length === 0) return Array(5).fill(null)
    return Array.from({ length: 6 }, (_, i) => list[i % list.length])
})
</script>

<template>
    <Transition name="sg-scene" mode="out-in">
        <div :key="sceneKey" class="sg-scene" :class="`sg-scene--${sceneKey}`">
            <!-- 1. opening: wrought-iron gate + couple full body -->
            <svg v-if="sceneKey === 'opening'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <!-- gate arch -->
                <path d="M60 140 V70 Q60 50 100 50 Q140 50 140 70 V140" fill="none" stroke="#C9A961" stroke-width="2.2"/>
                <path d="M70 140 V72 Q70 58 100 58 Q130 58 130 72 V140" fill="none" stroke="#C9A961" stroke-width="1.4"/>
                <line x1="100" y1="50" x2="100" y2="140" stroke="#C9A961" stroke-width="1"/>
                <line x1="80" y1="60" x2="80" y2="140" stroke="#C9A961" stroke-width="0.8"/>
                <line x1="120" y1="60" x2="120" y2="140" stroke="#C9A961" stroke-width="0.8"/>
                <!-- lantern glow -->
                <circle cx="60" cy="80" r="14" fill="rgba(244,228,193,0.35)"/>
                <circle cx="140" cy="80" r="14" fill="rgba(244,228,193,0.35)"/>
                <!-- couple silhouette holding hands -->
                <path d="M85 165 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
                <path d="M115 165 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
            </svg>

            <!-- 2. couple: 2 figures + heart -->
            <svg v-else-if="sceneKey === 'couple'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M70 165 q-2 -22 6 -30 q-1 -10 7 -10 q8 0 7 10 q8 8 6 30 z" fill="#050813"/>
                <path d="M125 165 q-2 -22 6 -30 q-1 -10 7 -10 q8 0 7 10 q8 8 6 30 z" fill="#050813"/>
                <path d="M100 70 q-7 -10 -14 -4 q-7 6 0 14 l14 14 l14 -14 q7 -8 0 -14 q-7 -6 -14 4 z" fill="#C9A961" class="sg-heart-pulse"/>
            </svg>

            <!-- 3. events: calendar pages floating -->
            <svg v-else-if="sceneKey === 'events'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <g class="sg-scene-float">
                    <rect x="35"  y="55"  width="40" height="46" rx="3" fill="#FAFAF5" stroke="#C9A961" stroke-width="1"/>
                    <rect x="35"  y="55"  width="40" height="10" fill="#C9A961"/>
                    <line x1="40" y1="80" x2="70" y2="80" stroke="#8C7338" stroke-width="0.8"/>
                    <line x1="40" y1="90" x2="65" y2="90" stroke="#8C7338" stroke-width="0.8"/>
                </g>
                <g class="sg-scene-float" style="animation-delay: -2s">
                    <rect x="125" y="70" width="40" height="46" rx="3" fill="#FAFAF5" stroke="#C9A961" stroke-width="1"/>
                    <rect x="125" y="70" width="40" height="10" fill="#C9A961"/>
                    <line x1="130" y1="95"  x2="160" y2="95"  stroke="#8C7338" stroke-width="0.8"/>
                    <line x1="130" y1="105" x2="155" y2="105" stroke="#8C7338" stroke-width="0.8"/>
                </g>
                <path d="M100 175 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
            </svg>

            <!-- 4. countdown: hourglass + 2 figures -->
            <svg v-else-if="sceneKey === 'countdown'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M80 60 L120 60 L100 100 L120 140 L80 140 L100 100 Z" fill="none" stroke="#C9A961" stroke-width="2"/>
                <path d="M82 62 L118 62" stroke="#C9A961" stroke-width="4"/>
                <path d="M82 138 L118 138" stroke="#C9A961" stroke-width="4"/>
                <!-- sand piles -->
                <path d="M88 100 Q100 75 112 100" fill="#F4E4C1"/>
                <path d="M90 132 Q100 115 110 132 Z" fill="#F4E4C1"/>
                <!-- sand grains falling -->
                <circle cx="100" cy="105" r="1" fill="#F4E4C1" class="sg-sand-1"/>
                <circle cx="100" cy="115" r="1" fill="#F4E4C1" class="sg-sand-2"/>
                <!-- 2 figures watching -->
                <path d="M48 165 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
                <path d="M148 165 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
            </svg>

            <!-- 5. love_story: winding path + hill + church + walking figure -->
            <svg v-else-if="sceneKey === 'love_story'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M0 180 Q60 120 100 130 T200 80" fill="none" stroke="#D8DAE0" stroke-width="3" stroke-linecap="round" stroke-dasharray="2 4"/>
                <!-- hills -->
                <path d="M0 180 Q60 150 110 160 T200 130 L200 200 L0 200 Z" fill="rgba(216,218,224,0.18)"/>
                <!-- church spire -->
                <path d="M170 130 L170 90 L175 80 L180 90 L180 130 Z" fill="#C9A961"/>
                <line x1="175" y1="80" x2="175" y2="74" stroke="#C9A961" stroke-width="1.5"/>
                <path d="M173 78 h4 v4 h-4 z" fill="#C9A961"/>
                <!-- walking figure -->
                <path d="M70 160 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
            </svg>

            <!-- 6. gallery: 5-8 floating polaroid -->
            <svg v-else-if="sceneKey === 'gallery'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <g v-for="(src, i) in polaroidSources" :key="i"
                   :transform="`translate(${30 + (i % 3) * 55} ${30 + Math.floor(i / 3) * 60}) rotate(${(i % 2 ? -8 : 8) + (i * 3)})`"
                   class="sg-scene-float" :style="{ animationDelay: `${-i * 1.4}s` }">
                    <rect x="0" y="0" width="36" height="44" rx="1.5" fill="#FAFAF5" stroke="#C9A961" stroke-width="0.6"/>
                    <rect x="2" y="2" width="32" height="30" fill="#3D2614"/>
                    <image v-if="src" :href="src" x="2" y="2" width="32" height="30" preserveAspectRatio="xMidYMid slice"/>
                </g>
            </svg>

            <!-- 7. rsvp: letterbox + envelope -->
            <svg v-else-if="sceneKey === 'rsvp'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <rect x="80" y="100" width="40" height="60" rx="4" fill="#6B4226" stroke="#C9A961" stroke-width="1.5"/>
                <path d="M85 110 h30" stroke="#C9A961" stroke-width="2"/>
                <circle cx="100" cy="135" r="2" fill="#C9A961"/>
                <path d="M75 95 L100 60 L125 95 L100 80 Z" fill="#FAFAF5" stroke="#C9A961" stroke-width="1" class="sg-scene-float" style="transform-origin: 100px 80px"/>
            </svg>

            <!-- 8. gift: treasure chest + gold coins -->
            <svg v-else-if="sceneKey === 'gift'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M65 130 L65 110 Q65 92 100 92 Q135 92 135 110 L135 130 Z" fill="#6B4226" stroke="#C9A961" stroke-width="1.5"/>
                <rect x="65" y="130" width="70" height="36" fill="#6B4226" stroke="#C9A961" stroke-width="1.5"/>
                <rect x="92" y="138" width="16" height="12" fill="#C9A961"/>
                <circle cx="80" cy="155" r="4" fill="#C9A961"/>
                <circle cx="120" cy="158" r="4" fill="#C9A961"/>
                <circle cx="100" cy="170" r="5" fill="#C9A961"/>
                <circle cx="70" cy="88" r="1.4" fill="#F4E4C1" class="sg-sparkle-1"/>
                <circle cx="130" cy="88" r="1.4" fill="#F4E4C1" class="sg-sparkle-2"/>
            </svg>

            <!-- 9. wishes: scrolls scattered -->
            <svg v-else-if="sceneKey === 'wishes'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <g v-for="i in 4" :key="i" :transform="`translate(${30 + (i - 1) * 40} ${60 + ((i - 1) % 2) * 50}) rotate(${(i % 2 ? -12 : 12)})`" class="sg-scene-float" :style="{ animationDelay: `${-i * 1.2}s` }">
                    <rect x="0" y="0" width="30" height="20" fill="#F4E4C1" stroke="#8C7338" stroke-width="0.6"/>
                    <line x1="4" y1="6" x2="26" y2="6" stroke="#8C7338" stroke-width="0.4"/>
                    <line x1="4" y1="11" x2="22" y2="11" stroke="#8C7338" stroke-width="0.4"/>
                    <path d="M-3 -2 q3 -2 6 0" stroke="#C9A961" stroke-width="1.5" fill="none"/>
                </g>
            </svg>

            <!-- 10. quote: open book -->
            <svg v-else-if="sceneKey === 'quote'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M30 130 Q100 110 100 90 Q100 110 170 130 Q170 100 100 80 Q30 100 30 130 Z" fill="#F4E4C1" stroke="#8C7338" stroke-width="1.2"/>
                <path d="M100 90 V130" stroke="#8C7338" stroke-width="1"/>
                <line v-for="i in 4" :key="`l-${i}`" :x1="40" :y1="98 + i * 6" :x2="92" :y2="98 + i * 6" stroke="#8C7338" stroke-width="0.5"/>
                <line v-for="i in 4" :key="`r-${i}`" :x1="108" :y1="98 + i * 6" :x2="160" :y2="98 + i * 6" stroke="#8C7338" stroke-width="0.5"/>
                <circle cx="100" cy="60" r="20" fill="rgba(244,228,193,0.25)"/>
            </svg>

            <!-- 11. music: notes + staff -->
            <svg v-else-if="sceneKey === 'music'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <g stroke="rgba(201,169,97,0.35)" stroke-width="0.6">
                    <line x1="20" y1="80"  x2="180" y2="80"/>
                    <line x1="20" y1="90"  x2="180" y2="90"/>
                    <line x1="20" y1="100" x2="180" y2="100"/>
                    <line x1="20" y1="110" x2="180" y2="110"/>
                    <line x1="20" y1="120" x2="180" y2="120"/>
                </g>
                <g class="sg-scene-float-up">
                    <ellipse cx="70" cy="120" rx="6" ry="4.5" fill="#C9A961"/>
                    <line x1="76" y1="120" x2="76" y2="80" stroke="#C9A961" stroke-width="1.6"/>
                </g>
                <g class="sg-scene-float-up" style="animation-delay: -2s">
                    <ellipse cx="130" cy="135" rx="6" ry="4.5" fill="#C9A961"/>
                    <line x1="136" y1="135" x2="136" y2="95" stroke="#C9A961" stroke-width="1.6"/>
                </g>
            </svg>

            <!-- 12. closing: floral arch + 2 figures + ribbon banner -->
            <svg v-else viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M50 150 V90 Q50 50 100 50 Q150 50 150 90 V150" fill="none" stroke="#C9A961" stroke-width="2.2"/>
                <g fill="#C9A961">
                    <circle cx="60" cy="70" r="3"/>
                    <circle cx="80" cy="55" r="3"/>
                    <circle cx="100" cy="50" r="3.5"/>
                    <circle cx="120" cy="55" r="3"/>
                    <circle cx="140" cy="70" r="3"/>
                </g>
                <rect x="60" y="42" width="80" height="16" rx="2" fill="#C9A961"/>
                <path d="M55 50 L60 42 L60 58 Z" fill="#8C7338"/>
                <path d="M145 50 L140 42 L140 58 Z" fill="#8C7338"/>
                <path d="M85 175 q-2 -22 6 -30 q-1 -10 7 -10 q8 0 7 10 q8 8 6 30 z" fill="#050813"/>
                <path d="M115 175 q-2 -22 6 -30 q-1 -10 7 -10 q8 0 7 10 q8 8 6 30 z" fill="#050813"/>
            </svg>
        </div>
    </Transition>
</template>

<style scoped>
.sg-scene {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sg-scene-svg {
    width: 92%;
    height: 92%;
}

/* Scene morph transition */
.sg-scene-enter-active, .sg-scene-leave-active {
    transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}
.sg-scene-enter-from { opacity: 0; transform: scale(0.85); }
.sg-scene-leave-to   { opacity: 0; transform: scale(1.15); }

/* Heart pulse for couple scene */
.sg-heart-pulse { animation: sg-pulse 1.8s ease-in-out infinite; transform-origin: center; }
@keyframes sg-pulse {
    0%, 100% { transform: scale(1);   filter: drop-shadow(0 0 4px rgba(201,169,97,0.6)); }
    50%      { transform: scale(1.12); filter: drop-shadow(0 0 12px rgba(201,169,97,0.95)); }
}

/* Float drift for calendar pages, polaroids, scrolls */
.sg-scene-float {
    animation: sg-drift 6s ease-in-out infinite;
    transform-origin: center;
}
@keyframes sg-drift {
    0%, 100% { transform: translateY(0)   rotate(0deg); }
    50%      { transform: translateY(-6px) rotate(1.5deg); }
}

/* Float upward for music notes */
.sg-scene-float-up { animation: sg-rise 4s ease-in-out infinite; }
@keyframes sg-rise {
    0%   { transform: translateY(20px); opacity: 0.3; }
    40%  { opacity: 1; }
    100% { transform: translateY(-20px); opacity: 0; }
}

/* Hourglass sand falling */
.sg-sand-1 { animation: sg-sand 1.4s linear infinite; }
.sg-sand-2 { animation: sg-sand 1.4s linear infinite 0.7s; }
@keyframes sg-sand {
    0%   { transform: translateY(0);    opacity: 0; }
    20%  { opacity: 1; }
    100% { transform: translateY(28px); opacity: 0; }
}

/* Treasure sparkles */
.sg-sparkle-1 { animation: sg-twinkle 1.6s ease-in-out infinite; }
.sg-sparkle-2 { animation: sg-twinkle 1.6s ease-in-out infinite 0.8s; }
@keyframes sg-twinkle {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50%      { opacity: 1;   transform: scale(1.6); }
}

@media (prefers-reduced-motion: reduce) {
    .sg-scene-enter-active, .sg-scene-leave-active {
        transition: opacity 0.2s ease;
    }
    .sg-scene-enter-from, .sg-scene-leave-to { transform: none; }
    .sg-heart-pulse,
    .sg-scene-float,
    .sg-scene-float-up,
    .sg-sand-1, .sg-sand-2,
    .sg-sparkle-1, .sg-sparkle-2 {
        animation: none;
    }
}
</style>
