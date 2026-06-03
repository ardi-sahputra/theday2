<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import InvitationRenderer from '@/Components/invitation/InvitationRenderer.vue';

const props = defineProps({
    template:   { type: Object, required: true },
    invitation: { type: Object, required: true },
    messages:   { type: Array,  default: () => [] },
    isGuest:    { type: Boolean, default: true },
});

const primary = computed(() => props.invitation.config?.primary_color ?? '#92A89C');

// Bottom CTA only shows once the visitor has opened the invitation —
// otherwise it collides with the fixed opening gate (which has 0 in-flow height).
const opened = ref(false);

function useTemplate() {
    router.visit(`/use-template/${props.template.id}`);
}
</script>

<template>
    <Head :title="`Demo: ${template.name} — TheDay`"/>

    <!-- ── Demo banner (sticky top, above everything) ───────────── -->
    <div
        class="sticky top-0 z-[100] w-full backdrop-blur-md"
        style="background: linear-gradient(90deg, rgba(44,26,14,0.55), rgba(74,44,24,0.55))"
    >
        <div class="max-w-5xl mx-auto px-4 py-2.5 flex items-center gap-3 flex-wrap sm:flex-nowrap">
            <!-- Icon + text -->
            <div class="flex items-center gap-2 text-[#B8C7BF]/70 text-xs font-medium flex-1 min-w-0">
                <svg class="w-4 h-4 flex-shrink-0 text-[#92A89C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span class="truncate">
                    Preview demo template
                    <span class="font-bold text-[#B8C7BF]">"{{ template.name }}"</span>
                    — data yang ditampilkan adalah contoh.
                </span>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <a
                    href="/templates"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-[#B8C7BF]/70/70 hover:text-[#B8C7BF]/50 transition-colors"
                >
                    ← Galeri
                </a>
                <button
                    @click="useTemplate"
                    class="px-4 py-1.5 rounded-lg text-xs font-bold text-stone-900 transition-all hover:opacity-90 active:scale-95"
                    :style="`background-color: ${primary}`"
                >
                    Gunakan Template
                </button>
            </div>
        </div>
    </div>

    <!-- ── Full invitation renderer (demo mode) ─────── -->
    <InvitationRenderer
        :invitation="invitation"
        :messages="messages"
        :is-demo="true"
        :auto-open="false"
        @opened="v => opened = v"
    />

    <!-- ── Bottom CTA banner (only after the gate is opened) ─────── -->
    <div
        v-if="opened"
        class="py-8 px-6 text-center"
        style="background: linear-gradient(180deg, transparent, #2C1A0E22)"
    >
        <div class="max-w-sm mx-auto space-y-3">
            <p class="text-sm font-semibold text-stone-700">Suka dengan template ini?</p>
            <p class="text-xs text-stone-400">Mulai buat undangan sekarang — gratis, tanpa kartu kredit.</p>
            <button
                @click="useTemplate"
                class="w-full py-3 rounded-2xl text-sm font-bold text-white transition-all hover:opacity-90 active:scale-[0.98] shadow-lg"
                :style="`background-color: ${primary}`"
            >
                Gunakan Template "{{ template.name }}"
            </button>
            <a href="/templates" class="block text-xs text-stone-400 hover:text-stone-600 transition-colors mt-1">
                ← Lihat template lainnya
            </a>
        </div>
    </div>
</template>

<style>
/* Reuse the same global styles from invitation */
:root { --inv-primary: #92A89C; --inv-font: 'Playfair Display', serif; }
.reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.7s ease, transform 0.7s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }
@keyframes spin-slow { to { transform: rotate(360deg); } }
.animate-spin-slow { animation: spin-slow 4s linear infinite; }
</style>
