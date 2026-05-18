<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed, ref } from 'vue'
import StoryFrame from './StoryFrame.vue'

const props = defineProps({
    galleries: { type: Array, default: () => [] },
})
const emit = defineEmits(['view-all'])

const visible = computed(() => props.galleries.slice(0, 6))
const totalMore = computed(() => Math.max(0, props.galleries.length - 6))
const lightboxUrl = ref(null)

function openLightbox(url) { lightboxUrl.value = url }
function closeLightbox()   { lightboxUrl.value = null }
function urlOf(g) { return g?.image_url ?? g?.file_url ?? g?.url ?? (typeof g === 'string' ? g : null) }
</script>

<template>
    <StoryFrame story-key="gallery" story-theme="dark">
        <template #backdrop>
            <div class="igs-gallery-bg">
                <div class="igs-gallery-grid">
                    <button
                        v-for="(g, i) in visible"
                        :key="i"
                        type="button"
                        class="igs-gallery-cell"
                        :class="{ 'igs-boomerang': i === 2 }"
                        :style="`--d: ${i * 0.05}s`"
                        :aria-label="`Open photo ${i + 1}`"
                        @click="openLightbox(urlOf(g))"
                    >
                        <img :src="urlOf(g)" :alt="`Photo ${i + 1}`" loading="lazy"/>
                    </button>
                </div>
            </div>
        </template>
        <div class="igs-gallery-stack">
            <p class="igs-gallery-eye igs-stagger" style="--d: 0s">GALLERY</p>
            <div class="igs-gallery-bottom-card igs-stagger" style="--d: 0.2s">
                <p class="igs-gallery-title">OUR MOMENTS</p>
                <p class="igs-gallery-caption">Tap any photo to expand</p>
                <button
                    v-if="totalMore > 0"
                    type="button"
                    class="igs-gallery-view-all"
                    :aria-label="`View all ${totalMore + visible.length} photos`"
                    @click="emit('view-all')"
                >VIEW ALL {{ totalMore + visible.length }} PHOTOS</button>
            </div>
        </div>
        <Teleport to="body">
            <div v-if="lightboxUrl" class="igs-gallery-lightbox" @click="closeLightbox" role="dialog" aria-modal="true">
                <img :src="lightboxUrl" alt="Photo"/>
                <button type="button" class="igs-gallery-lightbox-close" aria-label="Close lightbox" @click.stop="closeLightbox">×</button>
            </div>
        </Teleport>
    </StoryFrame>
</template>

<style scoped>
.igs-gallery-bg {
    position: absolute;
    inset: 0;
    background: #000000;
}
.igs-gallery-grid {
    position: absolute;
    inset: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: repeat(3, 1fr);
    gap: 2px;
}
.igs-gallery-cell {
    border: none;
    padding: 0;
    background: #1a1a1a;
    cursor: pointer;
    overflow: hidden;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-gallery-cell {
    opacity: 1;
    transform: translateY(0);
}
.igs-gallery-cell img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
}
.igs-gallery-stack {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
    align-items: center;
}
.igs-gallery-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
    text-shadow: 0 2px 8px rgba(0,0,0,0.6);
}
.igs-gallery-bottom-card {
    width: calc(100% - 24px);
    max-width: 320px;
    background: rgba(0,0,0,0.55);
    border-radius: 12px;
    padding: 16px;
    color: #FFFFFF;
    text-align: center;
}
.igs-gallery-title {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 20px;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
}
.igs-gallery-caption {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: rgba(255,255,255,0.85);
    margin: 0 0 8px;
}
.igs-gallery-view-all {
    background: rgba(255,255,255,0.18);
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 8px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    min-height: 36px;
    cursor: pointer;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
.igs-boomerang {
    animation: igs-boomerang-y 2s ease-in-out infinite alternate;
}
@keyframes igs-boomerang-y {
    from { translate: 0 -4px; }
    to   { translate: 0  4px; }
}
.igs-gallery-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.95);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.igs-gallery-lightbox img {
    max-width: 95vw;
    max-height: 90vh;
    object-fit: contain;
}
.igs-gallery-lightbox-close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    color: #FFFFFF;
    border: none;
    font-size: 28px;
    cursor: pointer;
}
@media (prefers-reduced-motion: reduce) {
    .igs-gallery-cell, .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-boomerang { animation: none; }
}
</style>
