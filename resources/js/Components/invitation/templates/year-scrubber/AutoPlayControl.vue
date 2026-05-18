<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    isPlaying: { type: Boolean, default: false },
    speed:     { type: Number,  default: 1 },
    disabled:  { type: Boolean, default: false },
})
const emit = defineEmits(['play', 'pause', 'update:speed'])

const speeds = [0.5, 1, 2]
const ariaLabel = computed(() => props.isPlaying ? 'Jeda autoplay' : 'Mulai autoplay')

function toggle() {
    if (props.disabled) return
    if (props.isPlaying) emit('pause')
    else                 emit('play')
}
function pickSpeed(s) {
    if (props.disabled) return
    emit('update:speed', s)
}
</script>

<template>
    <div class="ys-autoplay" :class="{ 'is-disabled': disabled }" :aria-disabled="disabled || null">
        <button
            type="button"
            class="ys-autoplay-btn"
            :aria-label="ariaLabel"
            :aria-pressed="isPlaying"
            :disabled="disabled"
            @click="toggle"
        >
            <svg v-if="!isPlaying" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <path d="M7 4 L20 12 L7 20 Z" fill="#FAF8F2"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <rect x="6"  y="5" width="4" height="14" fill="#FAF8F2"/>
                <rect x="14" y="5" width="4" height="14" fill="#FAF8F2"/>
            </svg>
        </button>

        <div class="ys-speed-group" role="group" aria-label="Kecepatan autoplay">
            <button
                v-for="s in speeds"
                :key="s"
                type="button"
                class="ys-speed-pill"
                :class="{ 'is-active': Math.abs(speed - s) < 0.001 }"
                :aria-pressed="Math.abs(speed - s) < 0.001"
                :disabled="disabled"
                @click="pickSpeed(s)"
            >{{ s }}&times;</button>
        </div>

        <span v-if="disabled" class="ys-autoplay-note" role="status">
            Autoplay dimatikan (reduced motion)
        </span>
    </div>
</template>

<style scoped>
.ys-autoplay {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(8px);
    border-radius: 999px;
    box-shadow: 0 4px 16px rgba(26,46,74,0.12);
}
.ys-autoplay.is-disabled { opacity: 0.7; }

.ys-autoplay-btn {
    width: 48px; height: 48px;
    border-radius: 50%;
    border: 1.5px solid #A88840;
    background: #C9A961;
    color: #FAF8F2;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}
.ys-autoplay-btn:hover:not(:disabled) { background: #A88840; }
.ys-autoplay-btn:focus-visible { outline: 2px solid #1A2E4A; outline-offset: 2px; }
.ys-autoplay-btn:disabled { cursor: not-allowed; }

.ys-speed-group { display: inline-flex; gap: 4px; }
.ys-speed-pill {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid transparent;
    background: transparent;
    color: #2A4063;
    cursor: pointer;
    letter-spacing: 0.05em;
    min-height: 44px;
    min-width: 44px;
    transition: background 0.2s ease, color 0.2s ease;
}
.ys-speed-pill:hover:not(:disabled) { background: rgba(201,169,97,0.18); }
.ys-speed-pill.is-active {
    background: #1A2E4A;
    color: #FAF8F2;
    border-color: #1A2E4A;
}
.ys-speed-pill:focus-visible { outline: 2px solid #C9A961; outline-offset: 2px; }

.ys-autoplay-note {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    color: #A39E94;
    letter-spacing: 0.05em;
}
@media (prefers-reduced-motion: reduce) {
    .ys-autoplay-btn, .ys-speed-pill { transition: none; }
}
</style>
