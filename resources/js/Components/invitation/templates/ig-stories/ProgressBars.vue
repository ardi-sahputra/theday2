<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    count:       { type: Number,  required: true },
    currentIdx:  { type: Number,  required: true },
    duration:    { type: Number,  default: 6 },
    isPaused:    { type: Boolean, default: false },
    autoAdvance: { type: Boolean, default: true },
})
const emit = defineEmits(['complete'])

const durationCss = computed(() => `${Math.max(1, Math.min(20, props.duration))}s`)

function onFillEnd(idx) {
    if (idx === props.currentIdx && props.autoAdvance) emit('complete')
}
</script>

<template>
    <div class="igs-progress" :style="{ '--igs-story-duration': durationCss }" aria-hidden="true">
        <div
            v-for="i in count"
            :key="i"
            class="igs-progress-segment"
            :class="{
                'igs-progress-segment--completed': (i - 1) < currentIdx,
                'igs-progress-segment--active':    (i - 1) === currentIdx && autoAdvance,
                'igs-progress-segment--paused':    isPaused,
                'igs-progress-segment--idle':      (i - 1) === currentIdx && !autoAdvance,
            }"
        >
            <span class="igs-progress-segment__fill" @animationend="onFillEnd(i - 1)"/>
        </div>
    </div>
</template>

<style scoped>
.igs-progress {
    display: flex;
    gap: 4px;
    width: 100%;
}
.igs-progress-segment {
    flex: 1;
    height: 2.5px;
    background: rgba(255,255,255,0.30);
    border-radius: 9999px;
    overflow: hidden;
    position: relative;
}
.igs-progress-segment__fill {
    position: absolute;
    inset: 0;
    background: #FFFFFF;
    transform: scaleX(0);
    transform-origin: left center;
    border-radius: inherit;
}
.igs-progress-segment--completed .igs-progress-segment__fill {
    transform: scaleX(1);
    animation: none;
}
.igs-progress-segment--active .igs-progress-segment__fill {
    animation: igs-progress-fill var(--igs-story-duration, 6s) linear forwards;
}
.igs-progress-segment--paused .igs-progress-segment__fill {
    animation-play-state: paused;
}
.igs-progress-segment--idle .igs-progress-segment__fill {
    transform: scaleX(0);
    animation: none;
}
@keyframes igs-progress-fill {
    from { transform: scaleX(0); }
    to   { transform: scaleX(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-progress-segment--active .igs-progress-segment__fill {
        animation: none;
        transform: scaleX(0);
    }
    .igs-progress-segment--completed .igs-progress-segment__fill {
        transform: scaleX(1);
    }
}
</style>
