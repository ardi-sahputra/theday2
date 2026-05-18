<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <Teleport to="body">
        <Transition name="th-chest">
            <div v-if="open" class="th-chest-overlay" role="dialog" aria-modal="true" aria-labelledby="th-chest-title">
                <div class="th-chest-stage" :class="{ 'th-chest--open': revealed }">
                    <div class="th-chest-art">
                        <span v-for="(s, i) in sparkles" :key="i" class="th-sparkle"
                              :style="{ '--sparkle-x': s.x + 'px', '--sparkle-y': s.y + 'px',
                                        left: s.left + '%', top: s.top + '%',
                                        transitionDelay: s.delay + 'ms' }">
                            <img src="/images/templates/treasure-hunt/sparkle.svg" alt="" aria-hidden="true"/>
                        </span>
                        <img src="/images/templates/treasure-hunt/treasure-chest.svg"
                             class="th-chest-svg" alt="" aria-hidden="true"/>
                    </div>
                    <h2 id="th-chest-title" class="th-chest__title">Anda telah menemukan harta sesungguhnya</h2>
                    <p class="th-chest__msg">— kehadiran Anda di hari bahagia kami.</p>
                    <button class="th-chest__close" type="button" @click="$emit('close')">TUTUP</button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue'
const props = defineProps({ open: { type: Boolean, default: false } })
defineEmits(['close'])
const revealed = ref(false), sparkles = ref([])
function makeSparkles() {
    sparkles.value = Array.from({ length: 16 }, () => ({
        left: 40 + Math.random() * 20, top: 30 + Math.random() * 25,
        x: (Math.random() - 0.5) * 120, y: (Math.random() - 0.5) * 120,
        delay: Math.floor(Math.random() * 300),
    }))
}
watch(() => props.open, async (v) => {
    if (v) {
        makeSparkles(); revealed.value = false
        await nextTick()
        requestAnimationFrame(() => requestAnimationFrame(() => { revealed.value = true }))
    } else { revealed.value = false }
})
</script>

<style scoped>
.th-chest-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.7);
    display: grid; place-items: center; z-index: 120; padding: 16px;
}
.th-chest-stage {
    background: var(--th-parchment, #E8D5A0);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 0 1px var(--th-ink-faded, #6B4F38), 0 24px 60px rgba(0,0,0,0.6);
    padding: 28px 28px 32px; width: min(440px, calc(100vw - 32px));
    text-align: center; border-radius: 4px;
}
.th-chest-art { position: relative; width: 240px; height: 200px; margin: 0 auto 16px; }
.th-chest-svg { width: 100%; height: 100%; display: block; }
:global(.th-chest--open .th-chest-lid) { transform: rotateX(-90deg); }
:global(.th-chest-lid) {
    transform-origin: bottom center; transform: rotateX(0deg);
    transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}
:global(.th-chest-coins) {
    opacity: 0; transform: translateY(20px) scale(0.5); transform-origin: center center;
    transition: opacity 0.4s ease-out 0.6s, transform 0.4s ease-out 0.6s;
}
:global(.th-chest--open .th-chest-coins) { opacity: 1; transform: translateY(0) scale(1); }
.th-sparkle {
    position: absolute; width: 18px; height: 18px;
    opacity: 0; transform: scale(0); pointer-events: none;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.6s ease-out;
}
.th-sparkle img { width: 100%; height: 100%; display: block; }
.th-chest--open .th-sparkle {
    opacity: 1; transform: scale(1) translate(var(--sparkle-x, 0), var(--sparkle-y, 0));
}
.th-chest__title {
    font-family: 'IM Fell English', serif; font-style: italic; font-size: 22px;
    color: var(--th-ink, #3D2817); margin: 0 0 8px;
}
.th-chest__msg {
    font-family: 'Crimson Text', serif; font-size: 16px;
    color: var(--th-ink-faded, #6B4F38); margin: 0 0 20px;
}
.th-chest__close {
    font-family: 'Pirata One', cursive; font-size: 18px; letter-spacing: 0.15em;
    background: var(--th-parchment-light, #F2E2B5); color: var(--th-gold-deep, #9E7E3E);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 0 1px var(--th-parchment-dark, #C8B077);
    padding: 10px 32px; border-radius: 0; cursor: pointer;
}
.th-chest__close:hover, .th-chest__close:focus-visible {
    background: var(--th-parchment, #E8D5A0); outline: none;
}
.th-chest-enter-active, .th-chest-leave-active { transition: opacity 0.3s ease; }
.th-chest-enter-from, .th-chest-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    :global(.th-chest-lid), :global(.th-chest-coins) { transition: opacity 0.3s ease; transform: none; }
    :global(.th-chest--open .th-chest-lid) { opacity: 0; }
    .th-sparkle { transition: opacity 0.3s ease; transform: none; }
}
</style>
