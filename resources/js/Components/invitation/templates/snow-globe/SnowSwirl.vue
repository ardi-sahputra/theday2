<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    density: { type: String,  default: 'medium' },  // sparse | medium | dense
    shaking: { type: Boolean, default: false },
    tiltX:   { type: Number,  default: 0 },         // -1..1 from gyro
})

const DENSITY_MAP = { sparse: 60, medium: 90, dense: 120 }
const count = computed(() => DENSITY_MAP[props.density] ?? 90)

function makeFlakes(n) {
    return Array.from({ length: n }, (_, i) => ({
        id:       i,
        left:     Math.random() * 100,
        opacity:  0.65 + Math.random() * 0.35,
        duration: 8 + Math.random() * 6,           // 8-14s
        delay:    Math.random() * 14,
        sway:     (Math.random() - 0.5) * 60,      // -30..30px
        restY:    50 + Math.random() * 50,         // resting % for reduced motion
        variant:  1 + Math.floor(Math.random() * 5),
        swirlX:   0,
        swirlY:   0,
    }))
}

const flakes = ref(makeFlakes(count.value))

watch(count, (n) => { flakes.value = makeFlakes(n) })

watch(() => props.shaking, (val) => {
    if (!val) return
    flakes.value = flakes.value.map(f => ({
        ...f,
        swirlX: (Math.random() - 0.5) * 200,
        swirlY: -(30 + Math.random() * 70),
        delay:  Math.random() * 0.8,
    }))
})

// Gyro sway: convert tiltX into a CSS variable for snow drift.
const gyroSway = computed(() => `${(props.tiltX || 0) * 30}px`)
</script>

<template>
    <div
        class="sg-swirl"
        :class="{ 'sg-swirl--shaking': shaking }"
        :style="{ '--gyro-sway': gyroSway }"
        aria-hidden="true"
    >
        <svg width="0" height="0" style="position:absolute" aria-hidden="true">
            <defs>
                <symbol id="sg-flake-1" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="2.4" fill="currentColor"/>
                </symbol>
                <symbol id="sg-flake-2" viewBox="0 0 24 24">
                    <g fill="currentColor"><circle cx="12" cy="12" r="1.6"/><path d="M12 2v20M2 12h20M5 5l14 14M19 5l-14 14" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></g>
                </symbol>
                <symbol id="sg-flake-3" viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
                        <path d="M12 3v18M3 12h18"/>
                        <path d="M12 6l-2 2M12 6l2 2M12 18l-2-2M12 18l2-2M6 12l2-2M6 12l2 2M18 12l-2-2M18 12l-2 2"/>
                    </g>
                </symbol>
                <symbol id="sg-flake-4" viewBox="0 0 24 24">
                    <polygon points="12,4 14,10 20,12 14,14 12,20 10,14 4,12 10,10" fill="currentColor"/>
                </symbol>
                <symbol id="sg-flake-5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="1.8" fill="currentColor"/>
                </symbol>
            </defs>
        </svg>
        <span
            v-for="f in flakes"
            :key="f.id"
            class="sg-flake"
            :style="{
                left:               f.left + '%',
                '--flake-opacity':  f.opacity,
                '--fall-duration':  f.duration + 's',
                '--fall-delay':     f.delay + 's',
                '--sway':           f.sway + 'px',
                '--swirl-x':        f.swirlX + 'px',
                '--swirl-y':        f.swirlY + '%',
                '--rest-y':         f.restY + '%',
            }"
        >
            <svg viewBox="0 0 24 24" width="8" height="8" style="color: var(--sg-snow);">
                <use :href="`#sg-flake-${f.variant}`"/>
            </svg>
        </span>
    </div>
</template>

<style scoped>
.sg-swirl {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.sg-flake {
    position: absolute;
    top: 0;
    display: inline-block;
    width: 8px;
    height: 8px;
    opacity: var(--flake-opacity, 0.85);
    animation: sg-fall var(--fall-duration, 10s) linear var(--fall-delay, 0s) infinite;
    will-change: transform;
    pointer-events: none;
}
@keyframes sg-fall {
    0%   { transform: translate3d(calc(var(--gyro-sway, 0px) * 0), -10%, 0) rotateZ(0deg); }
    50%  { transform: translate3d(calc(var(--sway, 0px) + var(--gyro-sway, 0px)), 50%, 0) rotateZ(180deg); }
    100% { transform: translate3d(calc(var(--gyro-sway, 0px) * 0), 110%, 0) rotateZ(360deg); }
}

/* Shake state: violent swirl 0.6s, then re-trigger fall with random delay */
.sg-swirl--shaking .sg-flake {
    animation:
        sg-swirl 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards,
        sg-fall var(--fall-duration, 10s) linear 0.6s infinite;
}
@keyframes sg-swirl {
    0%   { transform: translate3d(0, 0, 0) rotateZ(0deg); }
    100% { transform: translate3d(var(--swirl-x, 0), var(--swirl-y, -80%), 0) rotateZ(720deg); }
}

@media (prefers-reduced-motion: reduce) {
    /* CRITICAL: snow ambient fall + shake swirl disabled — high motion sickness trigger.
       Flakes render in static resting position. */
    .sg-flake,
    .sg-swirl--shaking .sg-flake {
        animation: none;
        transform: translate3d(0, var(--rest-y, 50%), 0);
    }
}
</style>
