<script setup>
const props = defineProps({
    anchors:    { type: Array,  default: () => [] },
    discovered: { type: Object, default: () => new Set() },
})

function isDiscovered(key) {
    return props.discovered.has?.(key) ?? false
}

function jumpTo(key) {
    if (typeof document === 'undefined') return
    const el = document.querySelector(`.fl-section-anchor[data-section-key="${key}"]`)
    if (!el) return
    el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    // Move focus so BeamMask focusin handler snaps beam to anchor
    setTimeout(() => el.focus({ preventScroll: true }), 320)
}
</script>

<template>
    <div class="fl-minimap" role="navigation" aria-label="Peta posisi section">
        <button
            v-for="anchor in anchors"
            :key="anchor.key"
            type="button"
            class="fl-minimap-dot"
            :class="{ 'fl-minimap-dot--discovered': isDiscovered(anchor.key) }"
            :style="{ left: anchor.pos.x + '%', top: anchor.pos.y + '%' }"
            :aria-label="`Section ${anchor.key}${isDiscovered(anchor.key) ? ' — ditemukan' : ' — belum ditemukan'}`"
            @click="jumpTo(anchor.key)"
        />
    </div>
</template>

<style scoped>
.fl-minimap {
    position: fixed; right: 24px; bottom: 24px;
    width: 160px; height: 100px;
    background: url('/images/templates/flashlight/minimap-bg.svg') center/100% 100% no-repeat;
    backdrop-filter: blur(4px);
    z-index: 70;
    pointer-events: auto;
}

@media (max-width: 480px) {
    .fl-minimap { width: 120px; height: 80px; right: 16px; bottom: 16px; }
}

.fl-minimap-dot {
    position: absolute;
    width: 8px; height: 8px;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    background: #3A3A3A;
    border: none;
    padding: 0;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.fl-minimap-dot:hover  { background: #5A4D38; }
.fl-minimap-dot:focus  { outline: 1px solid #C9A961; outline-offset: 2px; }

.fl-minimap-dot--discovered {
    background: #C9A961;
    animation: fl-dot-pulse 1.5s ease-in-out infinite;
}

@keyframes fl-dot-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1);   opacity: 0.7; }
    50%      { transform: translate(-50%, -50%) scale(1.3); opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .fl-minimap-dot--discovered { animation: none; opacity: 1; }
}
</style>
