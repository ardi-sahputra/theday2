<script setup>
import { ref } from 'vue';
import { useStaggerReveal } from '@/Composables/useReveal.js';

const props = defineProps({
    invitation:   { type: Object, required: true },
    primaryColor: { type: String, default: '#92A89C' },
    fontFamily:   { type: String, default: 'Playfair Display' },
});

const el = ref(null);
useStaggerReveal(el, '.item', 140);

const details    = props.invitation.details ?? {};
const isWedding  = props.invitation.event_type === 'pernikahan';
const openingText = details.opening_text
    ?? (isWedding
        ? 'Dengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir di hari bahagia kami.'
        : 'Dengan penuh sukacita, kami mengundang Bapak/Ibu/Saudara/i untuk turut merayakan hari istimewa ini.');
</script>

<template>
    <section class="py-20 px-8 text-center bg-white">
        <div ref="el" class="max-w-sm mx-auto space-y-8">

            <!-- Top ornament -->
            <div class="reveal-scale item flex items-center justify-center gap-2">
                <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                <svg class="inv-float w-4 h-4" :style="{ color: primaryColor }" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a10 10 0 110 20A10 10 0 0112 2zm0 2a8 8 0 100 16A8 8 0 0012 4zm0 3a5 5 0 110 10A5 5 0 0112 7zm0 2a3 3 0 100 6 3 3 0 000-6z"/>
                </svg>
                <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
            </div>

            <!-- Bismillah / event opener label -->
            <div class="reveal-blur item">
                <p class="text-xs tracking-[0.3em] uppercase mb-4" :style="{ color: primaryColor }">
                    Undangan Pernikahan
                </p>
                <p class="text-base text-stone-600 leading-relaxed italic" :style="{ fontFamily }">
                    {{ openingText }}
                </p>
            </div>

            <!-- Wedding: parent names -->
            <template v-if="isWedding">
                <div class="reveal item grid grid-cols-2 gap-6 text-sm text-stone-600">
                    <div class="space-y-1">
                        <p class="font-semibold text-stone-800" :style="{ fontFamily }">
                            {{ details.groom_name }}
                        </p>
                        <p class="text-xs text-stone-400">Putra dari</p>
                        <p>{{ details.groom_parent_names ?? '—' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="font-semibold text-stone-800" :style="{ fontFamily }">
                            {{ details.bride_name }}
                        </p>
                        <p class="text-xs text-stone-400">Putri dari</p>
                        <p>{{ details.bride_parent_names ?? '—' }}</p>
                    </div>
                </div>
            </template>

            <!-- Bottom ornament -->
            <div class="reveal-scale item flex items-center justify-center gap-2">
                <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                <div class="inv-pulse w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: primaryColor }"/>
                <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
            </div>
        </div>
    </section>
</template>
