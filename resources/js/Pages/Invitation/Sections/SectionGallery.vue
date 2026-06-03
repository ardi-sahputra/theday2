<script setup>
import { ref } from 'vue';
import { useReveal } from '@/Composables/useReveal.js';
import GallerySection from '@/Components/invitation/sections/GallerySection.vue';

defineProps({
    galleries:    { type: Array,  default: () => [] },
    primaryColor: { type: String, default: '#92A89C' },
    layout:       { type: String, default: 'grid' },   // grid | masonry | carousel | polaroid
});

const heading = ref(null);
useReveal(heading);
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

        <GallerySection :galleries="galleries" :layout="layout" :primary-color="primaryColor" />
    </section>
</template>
