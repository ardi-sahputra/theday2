<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useReveal } from '@/Composables/useReveal.js';

const props = defineProps({
    invitation:   { type: Object, required: true },
    primaryColor: { type: String, default: '#92A89C' },
    fontFamily:   { type: String, default: 'Playfair Display' },
});

const el = ref(null);
useReveal(el);

// Computed so the live editor preview reflects edits as the user types.
const details = computed(() => props.invitation.details ?? {});
const closingText = computed(() => details.value.closing_text
    ?? 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir. Atas kehadiran dan doa restu Anda, kami ucapkan terima kasih.');

// ── Countdown ────────────────────────────────────────────────────────────────
// Target = first upcoming event date
const targetDate = computed(() => {
    const events = props.invitation.events ?? [];
    const upcoming = events
        .map(e => new Date(e.event_date + (e.start_time ? `T${e.start_time}` : 'T00:00')))
        .filter(d => d > new Date())
        .sort((a, b) => a - b);
    return upcoming[0] ?? null;
});

const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
let timer;

function updateCountdown() {
    if (!targetDate.value) return;
    const diff = targetDate.value - Date.now();
    if (diff <= 0) {
        countdown.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
        return;
    }
    countdown.value = {
        days:    Math.floor(diff / 86400000),
        hours:   Math.floor((diff % 86400000) / 3600000),
        minutes: Math.floor((diff % 3600000)  / 60000),
        seconds: Math.floor((diff % 60000)    / 1000),
    };
}

onMounted(() => {
    updateCountdown();
    timer = setInterval(updateCountdown, 1000);
});
onUnmounted(() => clearInterval(timer));

const pad = (n) => String(n).padStart(2, '0');
</script>

<template>
    <section
        class="py-20 px-8 text-center relative overflow-hidden"
        :style="{ backgroundColor: primaryColor + '10' }"
    >
        <!-- Background decoration -->
        <div class="inv-float-x absolute -bottom-20 -right-20 w-64 h-64 rounded-full opacity-10"
             :style="{ backgroundColor: primaryColor }"/>
        <div class="inv-float absolute -top-16 -left-16 w-48 h-48 rounded-full opacity-[0.08]"
             :style="{ backgroundColor: primaryColor }"/>

        <div ref="el" class="reveal relative z-10 max-w-sm mx-auto space-y-10">

            <!-- Closing text -->
            <div class="space-y-4">
                <div class="flex items-center justify-center gap-2">
                    <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                    <svg class="inv-breathe w-4 h-4" :style="{ color: primaryColor }" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                </div>

                <p class="text-base text-stone-600 leading-relaxed italic" :style="{ fontFamily }">
                    {{ closingText }}
                </p>

                <!-- Names sign-off -->
                <div class="pt-2">
                    <p class="text-xs tracking-[0.2em] uppercase text-stone-400 mb-2">Dengan cinta,</p>
                    <p class="text-2xl font-semibold text-stone-800" :style="{ fontFamily }">
                        {{ details.groom_name ?? '—' }} &amp; {{ details.bride_name ?? '—' }}
                    </p>
                </div>
            </div>

            <!-- Countdown -->
            <div v-if="targetDate" class="space-y-3">
                <p class="text-xs tracking-widest uppercase text-stone-400">Menghitung Hari</p>
                <div class="grid grid-cols-4 gap-2">
                    <div
                        v-for="(item, label) in { hari: countdown.days, jam: countdown.hours, menit: countdown.minutes, detik: countdown.seconds }"
                        :key="label"
                        class="rounded-2xl py-3 px-1 transition-transform duration-300 hover:scale-105"
                        :style="{ backgroundColor: primaryColor + '15' }"
                    >
                        <p class="text-2xl font-bold text-stone-800 tabular-nums" :style="{ fontFamily }">
                            <span :key="pad(item)" class="inv-tick inline-block">{{ pad(item) }}</span>
                        </p>
                        <p class="text-xs text-stone-400 mt-0.5">{{ label }}</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-4 space-y-2">
                <div class="flex items-center justify-center gap-2">
                    <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                    <div class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: primaryColor }"/>
                    <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                </div>
                <p class="text-xs text-stone-400">
                    Dibuat dengan
                    <span :style="{ color: primaryColor }">♥</span>
                    menggunakan TheDay
                </p>
            </div>
        </div>
    </section>
</template>
