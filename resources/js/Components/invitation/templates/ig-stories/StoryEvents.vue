<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'
import StoryFrame from './StoryFrame.vue'

const props = defineProps({
    events:         { type: Array,  default: () => [] },
    firstEventDate: { type: String, default: '' },
})

const primary = computed(() => props.events[0] ?? null)
const more    = computed(() => Math.max(0, props.events.length - 1))

function openMaps(url) {
    if (!url) return
    window.open(url, '_blank', 'noopener,noreferrer')
}
</script>

<template>
    <StoryFrame story-key="events" story-theme="dark">
        <template #backdrop>
            <div class="igs-events-bg"/>
        </template>
        <div class="igs-events-stack" v-if="primary">
            <p class="igs-events-eye igs-stagger" style="--d: 0s">SAVE THE DATE</p>
            <h2 class="igs-events-title igs-stagger" style="--d: 0.1s">{{ primary.event_name }}</h2>
            <div class="igs-events-card igs-stagger" style="--d: 0.25s">
                <p class="igs-events-date">{{ firstEventDate }}</p>
                <p class="igs-events-time">
                    {{ primary.start_time || primary.time_start }}
                    <span v-if="primary.end_time || primary.time_end"> – {{ primary.end_time || primary.time_end }}</span>
                    <span v-if="primary.timezone"> {{ primary.timezone }}</span>
                </p>
                <p class="igs-events-addr">{{ primary.venue_address || primary.address }}</p>
                <button
                    v-if="primary.maps_url"
                    type="button"
                    class="igs-events-cta"
                    @click="openMaps(primary.maps_url)"
                    aria-label="Open location in Maps"
                >
                    OPEN MAPS ↗
                </button>
            </div>
            <p v-if="more > 0" class="igs-events-more igs-stagger" style="--d: 0.4s">
                + {{ more }} MORE EVENT{{ more > 1 ? 'S' : '' }}
            </p>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-events-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(145deg, #2196F3 0%, #00BCD4 100%);
}
.igs-events-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    flex: 1;
    justify-content: center;
}
.igs-events-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
}
.igs-events-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 28px;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.02em;
}
.igs-events-card {
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 320px;
    width: 100%;
    color: #FFFFFF;
    transform: translateY(30px);
}
:global(.igs-reveal.igs-visible) .igs-events-card {
    transform: translateY(0);
}
.igs-events-date {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 22px;
    margin: 0;
    letter-spacing: -0.01em;
}
.igs-events-time {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    color: rgba(255,255,255,0.85);
    margin: 0;
}
.igs-events-addr {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    color: rgba(255,255,255,0.75);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.igs-events-cta {
    margin-top: 8px;
    background: #FFFFFF;
    color: #1976D2;
    border: none;
    border-radius: 9999px;
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.08em;
    min-height: 44px;
    cursor: pointer;
    align-self: center;
}
.igs-events-cta:focus-visible { outline: 2px solid #FFFFFF; outline-offset: 2px; }
.igs-events-more {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: rgba(255,255,255,0.85);
    margin: 0;
    background: rgba(0,0,0,0.18);
    border-radius: 9999px;
    padding: 6px 12px;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.5s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger, .igs-events-card { opacity: 1; transform: none; transition: none; }
}
</style>
