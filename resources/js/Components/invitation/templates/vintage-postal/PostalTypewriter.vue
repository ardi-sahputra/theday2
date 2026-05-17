<script setup>
import { computed, ref, onMounted, watch } from 'vue'

const props = defineProps({
    text:      { type: String,  required: true },
    speed:     { type: String,  default: 'normal' }, // 'slow' | 'normal' | 'fast'
    skippable: { type: Boolean, default: false },
    mode:      { type: String,  default: 'typing' }, // 'typing' | 'handwriting'
    autoStart: { type: Boolean, default: true },
})

const SPEED_MS = { slow: 60, normal: 30, fast: 15 }
const msPerChar = computed(() => SPEED_MS[props.speed] ?? SPEED_MS.normal)

const chars = computed(() => Array.from(props.text ?? ''))
const skipped = ref(false)
const reducedMotion = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion.value) skipped.value = true
})

watch(() => props.text, () => { skipped.value = reducedMotion.value })

function skip() { skipped.value = true }
</script>

<template>
    <span class="vp-typewriter" :class="{ 'vp-typewriter--skipped': skipped, [`vp-typewriter--${mode}`]: true }" aria-live="polite">
        <span class="sr-only">{{ text }}</span>

        <template v-if="mode === 'typing'">
            <span
                v-for="(ch, i) in chars"
                :key="i"
                class="vp-typewriter-char"
                :style="{ animationDelay: skipped ? '0ms' : `${i * msPerChar}ms` }"
                aria-hidden="true"
            >{{ ch === ' ' ? ' ' : ch }}</span>
        </template>

        <template v-else>
            <!-- handwriting fallback (plain text, animated via parent svg path if used) -->
            <span class="vp-typewriter-handwriting" aria-hidden="true">{{ text }}</span>
        </template>

        <button
            v-if="skippable && !skipped"
            type="button"
            class="vp-typewriter-skip"
            @click="skip"
        >Lewati</button>
    </span>
</template>

<style scoped>
.vp-typewriter {
    display: inline-block;
    font-family: inherit;
    position: relative;
    line-height: 1.7;
}
.vp-typewriter-char {
    display: inline-block;
    opacity: 0;
    animation: vp-type-in 1ms linear forwards;
    white-space: pre;
}
@keyframes vp-type-in { to { opacity: 1; } }
.vp-typewriter--skipped .vp-typewriter-char {
    opacity: 1 !important;
    animation: none !important;
}
.vp-typewriter-handwriting {
    font-family: 'Homemade Apple', cursive;
    color: #3a2d1f;
}
.vp-typewriter-skip {
    position: absolute; top: -28px; right: 0;
    padding: 4px 10px;
    background: #f4ead5;
    border: 1px dashed #8b3a3a;
    color: #8b3a3a;
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
}
.vp-typewriter-skip:hover { background: #8b3a3a; color: #f4ead5; }
.sr-only {
    position: absolute !important;
    width: 1px; height: 1px;
    padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}
@media (prefers-reduced-motion: reduce) {
    .vp-typewriter-char { opacity: 1 !important; animation: none !important; }
}
</style>
