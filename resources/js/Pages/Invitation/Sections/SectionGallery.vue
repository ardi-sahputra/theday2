<script setup>
import { ref } from 'vue';
import { useReveal, useStaggerReveal } from '@/Composables/useReveal.js';

const props = defineProps({
    galleries:    { type: Array,  default: () => [] },
    primaryColor: { type: String, default: '#92A89C' },
});

const heading = ref(null);
const grid    = ref(null);
useReveal(heading);
useStaggerReveal(grid, '.item', 60);

// Lightbox
const lightboxIndex = ref(null);
const lightboxOpen  = ref(false);

function openLightbox(index) {
    lightboxIndex.value = index;
    lightboxOpen.value  = true;
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    lightboxOpen.value = false;
    document.body.style.overflow = '';
}

function prevPhoto() {
    lightboxIndex.value = (lightboxIndex.value - 1 + props.galleries.length) % props.galleries.length;
}

function nextPhoto() {
    lightboxIndex.value = (lightboxIndex.value + 1) % props.galleries.length;
}

// Touch swipe support
let touchStartX = 0;
function onTouchStart(e) { touchStartX = e.touches[0].clientX; }
function onTouchEnd(e) {
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) diff > 0 ? nextPhoto() : prevPhoto();
}
</script>

<template>
    <section class="py-20 bg-white">
        <!-- Heading -->
        <div ref="heading" class="reveal-blur text-center px-6 mb-8 space-y-2">
            <div class="flex items-center justify-center gap-2">
                <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                <svg class="inv-float w-4 h-4" :style="{ color: primaryColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
            </div>
            <h2 class="text-2xl font-semibold text-stone-800" style="font-family: var(--inv-font)">Galeri Foto</h2>
        </div>

        <!-- Grid -->
        <div ref="grid" class="grid grid-cols-3 gap-0.5 px-0.5">
            <div
                v-for="(photo, i) in galleries"
                :key="photo.id"
                class="reveal-scale item aspect-square overflow-hidden cursor-pointer relative group"
                @click="openLightbox(i)"
            >
                <img
                    :src="photo.image_url"
                    :alt="photo.caption || `Foto ${i + 1}`"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 group-active:scale-105"
                    loading="lazy"
                />
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 group-active:bg-black/20 transition-colors"/>
            </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <Transition name="lightbox">
                <div
                    v-if="lightboxOpen"
                    class="fixed inset-0 z-50 bg-black flex items-center justify-center"
                    @touchstart="onTouchStart"
                    @touchend="onTouchEnd"
                >
                    <!-- Close -->
                    <button
                        @click="closeLightbox"
                        class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Counter -->
                    <div class="absolute top-4 left-1/2 -translate-x-1/2 text-white/60 text-sm">
                        {{ lightboxIndex + 1 }} / {{ galleries.length }}
                    </div>

                    <!-- Photo -->
                    <img
                        v-if="lightboxIndex !== null"
                        :src="galleries[lightboxIndex].image_url"
                        :alt="galleries[lightboxIndex].caption || ''"
                        class="max-h-screen max-w-full object-contain"
                    />

                    <!-- Caption -->
                    <div v-if="galleries[lightboxIndex]?.caption"
                         class="absolute bottom-16 left-0 right-0 text-center px-6">
                        <p class="text-white/80 text-sm">{{ galleries[lightboxIndex].caption }}</p>
                    </div>

                    <!-- Prev / Next arrows -->
                    <button
                        v-if="galleries.length > 1"
                        @click="prevPhoto"
                        class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button
                        v-if="galleries.length > 1"
                        @click="nextPhoto"
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>

<style scoped>
.lightbox-enter-active, .lightbox-leave-active { transition: opacity 0.25s; }
.lightbox-enter-from, .lightbox-leave-to       { opacity: 0; }
</style>
