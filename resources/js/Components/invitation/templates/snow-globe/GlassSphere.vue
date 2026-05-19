<script setup>
defineProps({
    size:    { type: Number, default: 360 },
})
</script>

<template>
    <div class="sg-sphere" :style="{ width: size + 'px', height: size + 'px' }">
        <svg
            class="sg-sphere-svg"
            :viewBox="`0 0 ${size} ${size}`"
            aria-hidden="true"
            preserveAspectRatio="xMidYMid meet"
        >
            <defs>
                <radialGradient :id="`sg-highlight-${size}`" cx="32%" cy="28%" r="55%">
                    <stop offset="0%"   stop-color="rgba(250,250,245,0.42)"/>
                    <stop offset="55%"  stop-color="rgba(250,250,245,0.08)"/>
                    <stop offset="100%" stop-color="rgba(250,250,245,0)"/>
                </radialGradient>
                <radialGradient :id="`sg-irid-${size}`" cx="70%" cy="78%" r="60%">
                    <stop offset="0%"   stop-color="rgba(164,197,219,0.18)"/>
                    <stop offset="100%" stop-color="rgba(164,197,219,0)"/>
                </radialGradient>
                <radialGradient :id="`sg-vignette-${size}`" cx="50%" cy="62%" r="60%">
                    <stop offset="70%"  stop-color="rgba(5,8,19,0)"/>
                    <stop offset="100%" stop-color="rgba(5,8,19,0.55)"/>
                </radialGradient>
                <clipPath :id="`sg-clip-${size}`">
                    <circle :cx="size / 2" :cy="size / 2" :r="size / 2 - 2"/>
                </clipPath>
            </defs>
            <!-- Interior slot (scene + snow) clipped to circle -->
            <g :clip-path="`url(#sg-clip-${size})`">
                <foreignObject :x="0" :y="0" :width="size" :height="size">
                    <div class="sg-sphere-inner" xmlns="http://www.w3.org/1999/xhtml">
                        <slot/>
                    </div>
                </foreignObject>
            </g>
            <!-- Iridescence (low priority, under highlight) -->
            <circle
                :cx="size / 2" :cy="size / 2" :r="size / 2 - 2"
                :fill="`url(#sg-irid-${size})`"
                pointer-events="none"
            />
            <!-- Vignette darkening at bottom edge -->
            <circle
                :cx="size / 2" :cy="size / 2" :r="size / 2 - 2"
                :fill="`url(#sg-vignette-${size})`"
                pointer-events="none"
            />
            <!-- Specular highlight (animated rotation 8s alternate) -->
            <circle
                :cx="size / 2" :cy="size / 2" :r="size / 2 - 2"
                :fill="`url(#sg-highlight-${size})`"
                class="sg-glass-highlight"
                pointer-events="none"
            />
            <!-- Outer edge ring -->
            <circle
                :cx="size / 2" :cy="size / 2" :r="size / 2 - 1"
                fill="none"
                stroke="rgba(164,197,219,0.35)"
                stroke-width="1.5"
                pointer-events="none"
            />
        </svg>
    </div>
</template>

<style scoped>
.sg-sphere {
    position: relative;
    border-radius: 50%;
    overflow: visible;
}
.sg-sphere-svg {
    display: block;
    width: 100%;
    height: 100%;
    overflow: visible;
}
.sg-sphere-inner {
    position: relative;
    width: 100%;
    height: 100%;
}
.sg-glass-highlight {
    transform-origin: center;
    animation: sg-glass-rotate 8s ease-in-out infinite alternate;
}
@keyframes sg-glass-rotate {
    0%   { transform: rotate(-8deg); }
    100% { transform: rotate(8deg); }
}
@media (prefers-reduced-motion: reduce) {
    .sg-glass-highlight { animation: none; transform: rotate(0deg); }
}
</style>
