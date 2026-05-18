<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import Vinyl from './Vinyl.vue'

const props = defineProps({
    active:     { type: Boolean, default: false },
    targetSide: { type: String,  default: 'A' },
    labelColor: { type: String,  default: 'red' },
    monogram:   { type: String,  default: 'A & B' },
    centerText: { type: String,  default: 'WEDDING SESSIONS' },
    centerSub:  { type: String,  default: '2026' },
    isPremium:  { type: Boolean, default: false },
})
const emit = defineEmits(['complete'])

const stage = ref('idle') // idle | lift | flip | drop | thunk
let timers = []

function clearTimers() {
    timers.forEach(t => clearTimeout(t))
    timers = []
}

function isReducedMotion() {
    return typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

watch(() => props.active, (now) => {
    if (!now) {
        stage.value = 'idle'
        clearTimers()
        return
    }
    if (isReducedMotion()) {
        // Skip animation; commit immediately.
        timers.push(setTimeout(() => emit('complete', props.targetSide), 80))
        return
    }
    stage.value = 'lift'
    timers.push(setTimeout(() => { stage.value = 'flip'  }, 300))
    timers.push(setTimeout(() => { stage.value = 'drop'  }, 900))
    timers.push(setTimeout(() => { stage.value = 'thunk' }, 1300))
    timers.push(setTimeout(() => {
        emit('complete', props.targetSide)
    }, 1600))
})

onBeforeUnmount(clearTimers)
</script>

<template>
    <Transition name="vr-flip-fade">
        <div
            v-if="active"
            class="vr-flip"
            :class="[
                stage === 'lift'  && 'vr-flip--lift',
                stage === 'flip'  && 'vr-flip--flip',
                stage === 'drop'  && 'vr-flip--drop',
                stage === 'thunk' && 'vr-flip--thunk',
            ]"
            aria-live="polite"
            :aria-label="`Flipping to Side ${targetSide}`"
        >
            <div class="vr-flip-plinth">
                <div class="vr-flip-vinyl">
                    <Vinyl
                        :spinning="false"
                        :label-color="labelColor"
                        :center-label-text="centerText"
                        :center-sub-text="centerSub"
                        :monogram="monogram"
                        :is-premium="isPremium"
                    />
                </div>
                <p class="vr-flip-label">FLIPPING TO SIDE {{ targetSide }}</p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.vr-flip {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(10,8,7,0.88);
    display: flex; align-items: center; justify-content: center;
}
.vr-flip-plinth {
    display: flex; flex-direction: column; align-items: center;
    gap: 24px;
    transition: transform 0.1s linear;
}
.vr-flip-vinyl {
    width: 280px; height: 280px;
    transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    transform-style: preserve-3d;
    will-change: transform;
}
.vr-flip--lift  .vr-flip-vinyl { transform: translateY(-40px) scale(1.05); }
.vr-flip--flip  .vr-flip-vinyl { transform: translateY(-40px) scale(1.05) rotateY(180deg); }
.vr-flip--drop  .vr-flip-vinyl {
    transform: translateY(0) scale(1) rotateY(180deg);
    transition: transform 0.3s cubic-bezier(0.7, 0, 0.6, 1);
}
.vr-flip--thunk .vr-flip-plinth { animation: vr-thunk 0.1s ease-out; }
@keyframes vr-thunk {
    0%   { transform: translateX(0); }
    33%  { transform: translateX(-2px); }
    66%  { transform: translateX(2px); }
    100% { transform: translateX(0); }
}
.vr-flip-label {
    font-family: 'Bebas Neue', sans-serif;
    color: #B8902F;
    font-size: 14px;
    letter-spacing: 0.4em;
    margin: 0;
}
.vr-flip-fade-enter-active, .vr-flip-fade-leave-active {
    transition: opacity 0.3s ease;
}
.vr-flip-fade-enter-from, .vr-flip-fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .vr-flip-vinyl, .vr-flip-plinth { transition: none; animation: none; transform: none; }
    .vr-flip-fade-enter-active, .vr-flip-fade-leave-active { transition: none; }
}
</style>
