<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import AlbumCover from './AlbumCover.vue'

defineProps({
    galleries: { type: Array, default: () => [] },
})
const emit = defineEmits(['lightbox'])

function resolveUrl(g) {
    if (typeof g === 'string') return g
    return g.image_url ?? g.file_url ?? g.url ?? null
}
function resolveCaption(g) {
    if (typeof g === 'string') return ''
    return g.caption ?? ''
}
</script>

<template>
    <section
        class="sw-slide sw-slide-gallery"
        data-slide-key="gallery"
        :style="{
            '--sw-bg-from':       '#7B2CBF',
            '--sw-bg-to':         '#B847FF',
            '--sw-bg-direction':  '135deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">ALBUM COVERS</span>
                <span class="sw-slide-counter">06 / 10</span>
            </header>
            <h2 class="sw-slide-title">YOUR YEAR IN PICTURES</h2>

            <div class="sw-album-grid">
                <div
                    v-for="(g, idx) in galleries"
                    :key="resolveUrl(g) ?? idx"
                    class="sw-album-cell"
                    :style="{ '--d': (idx * 0.06).toFixed(2) + 's' }"
                >
                    <AlbumCover
                        :photo-url="resolveUrl(g)"
                        :track-number="idx + 1"
                        :caption="resolveCaption(g)"
                        :fallback-hue="(idx * 53) % 360"
                        @lightbox="(url) => emit('lightbox', url)"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-album-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 24px;
}
@media (min-width: 768px) {
    .sw-album-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
}
.sw-album-cell {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    transition:
        opacity 0.5s ease-out var(--d, 0s),
        transform 0.5s ease-out var(--d, 0s);
}
:global(.sw-visible .sw-album-cell) { opacity: 1; transform: translateY(0) scale(1); }
@media (prefers-reduced-motion: reduce) {
    .sw-album-cell { opacity: 1; transform: none; transition: none; }
}
</style>
