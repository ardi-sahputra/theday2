<script setup>
import { Head } from '@inertiajs/vue3';
defineProps({
    detectedIp: { type: String, default: null },
});
</script>

<template>
    <Head title="Sedang Maintenance — TheDay" />

    <div
        class="min-h-screen flex flex-col items-center justify-center px-4"
        style="background: linear-gradient(160deg, #F4F7F5 0%, #EBF0EC 60%, #E8EDE9 100%)"
    >
        <!-- Decorative top-left blobs -->
        <div
            class="pointer-events-none fixed top-0 left-0 w-72 h-72 opacity-20"
            style="background: radial-gradient(circle at top left, #92A89C, transparent 70%)"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none fixed bottom-0 right-0 w-64 h-64 opacity-15"
            style="background: radial-gradient(circle at bottom right, #C8A26B, transparent 70%)"
            aria-hidden="true"
        />

        <div class="relative w-full max-w-sm text-center">

            <!-- Icon container -->
            <div
                class="w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-7"
                style="background: rgba(146,168,156,0.15); box-shadow: 0 4px 20px rgba(146,168,156,0.2)"
            >
                <svg
                    class="w-9 h-9"
                    style="color: #92A89C"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                </svg>
            </div>

            <!-- Logo -->
            <div class="mb-7">
                <img src="/image/logo.svg" alt="TheDay" class="h-8 mx-auto" />
            </div>

            <!-- Divider ornament -->
            <div class="flex items-center justify-center gap-3 mb-7" aria-hidden="true">
                <div class="h-px w-10" style="background: linear-gradient(to right, transparent, #92A89C)"></div>
                <svg class="w-3 h-3 flex-shrink-0" style="color: #C8A26B" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
                </svg>
                <div class="h-px w-10" style="background: linear-gradient(to left, transparent, #92A89C)"></div>
            </div>

            <!-- Heading -->
            <h1
                class="text-2xl font-bold mb-3 leading-snug"
                style="font-family: 'Playfair Display', Georgia, serif; color: #2C2417"
            >
                Sedang dalam Pemeliharaan
            </h1>

            <!-- Body text -->
            <p class="text-base leading-relaxed mb-8" style="color: #73877C; font-family: 'Figtree', sans-serif">
                Kami sedang meningkatkan layanan agar momen spesialmu semakin sempurna.
                <br class="hidden sm:block" />
                Mohon kembali dalam beberapa saat.
            </p>

            <!-- Bottom divider -->
            <div class="w-10 h-px mx-auto mb-7" style="background-color: rgba(146,168,156,0.4)"></div>

            <!-- Footer tagline -->
            <p class="text-xs tracking-widest uppercase" style="color: rgba(146,168,156,0.8); font-family: 'Figtree', sans-serif; letter-spacing: 0.15em">
                TheDay &mdash; Undangan Pernikahan Digital
            </p>

            <!-- Diagnostic IP (temp) -->
            <p v-if="detectedIp" class="text-[10px] mt-3 opacity-60" style="color: #73877C">
                IP: {{ detectedIp }}
            </p>

        </div>
    </div>
</template>
