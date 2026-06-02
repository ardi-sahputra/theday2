<script setup>
import { ref } from 'vue';
import { useReveal, useStaggerReveal } from '@/Composables/useReveal.js';

const props = defineProps({
    events:       { type: Array,  default: () => [] },
    primaryColor: { type: String, default: '#92A89C' },
    fontFamily:   { type: String, default: 'Playfair Display' },
});

const heading   = ref(null);
const container = ref(null);
useReveal(heading);
useStaggerReveal(container, '.event-card', 120);
</script>

<template>
    <section
        class="py-20 px-6"
        :style="{ backgroundColor: primaryColor + '08' }"
    >
        <div class="max-w-sm mx-auto space-y-8">

            <!-- Heading -->
            <div ref="heading" class="reveal-blur text-center space-y-2">
                <div class="flex items-center justify-center gap-2">
                    <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                    <svg class="inv-float w-4 h-4" :style="{ color: primaryColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                </div>
                <h2 class="text-2xl font-semibold text-stone-800" :style="{ fontFamily }">
                    Rangkaian Acara
                </h2>
            </div>

            <!-- Event cards -->
            <div ref="container" class="space-y-4">
                <div
                    v-for="event in events"
                    :key="event.id"
                    class="event-card reveal-scale rounded-3xl overflow-hidden bg-white shadow-sm border transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                    :style="{ borderColor: primaryColor + '30' }"
                >
                    <!-- Color strip (with sweeping shimmer) -->
                    <div
                        class="inv-shimmer h-1.5"
                        :style="{ backgroundImage: `linear-gradient(90deg, ${primaryColor}, ${primaryColor}55, ${primaryColor})` }"
                    />

                    <div class="p-5 space-y-4">
                        <!-- Event name -->
                        <h3 class="text-xl font-semibold text-stone-800" :style="{ fontFamily }">
                            {{ event.event_name }}
                        </h3>

                        <!-- Date & time row -->
                        <div class="flex items-start gap-3 text-sm text-stone-600">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                                 :style="{ backgroundColor: primaryColor + '20' }">
                                <svg class="w-4 h-4" :style="{ color: primaryColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-stone-700">{{ event.event_date_formatted }}</p>
                                <p v-if="event.start_time" class="text-stone-400 text-xs mt-0.5">
                                    {{ event.start_time }}{{ event.end_time ? ` – ${event.end_time}` : '' }} WIB
                                </p>
                            </div>
                        </div>

                        <!-- Venue row -->
                        <div class="flex items-start gap-3 text-sm text-stone-600">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                                 :style="{ backgroundColor: primaryColor + '20' }">
                                <svg class="w-4 h-4" :style="{ color: primaryColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-stone-700">{{ event.venue_name }}</p>
                                <p v-if="event.venue_address" class="text-stone-400 text-xs mt-0.5 leading-relaxed">
                                    {{ event.venue_address }}
                                </p>
                            </div>
                        </div>

                        <!-- Maps button -->
                        <a
                            v-if="event.maps_url"
                            :href="event.maps_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center justify-center gap-2 w-full py-2.5 rounded-2xl text-sm font-medium transition-all active:scale-95"
                            :style="{ backgroundColor: primaryColor + '15', color: primaryColor }"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            Lihat Peta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
