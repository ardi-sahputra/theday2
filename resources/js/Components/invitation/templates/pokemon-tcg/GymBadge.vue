<script setup>
import { computed } from 'vue'

const props = defineProps({
    event: { type: Object, required: true },
    index: { type: Number, default: 0 },
})

const TYPE_COLORS = ['#7B68EE', '#FF6B9D', '#FFD93D', '#4ECDC4']
const typeColor = computed(() => TYPE_COLORS[props.index % TYPE_COLORS.length])

const dateText = computed(() => {
    const d = props.event.event_date
    if (!d) return ''
    try {
        return new Date(d).toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        })
    } catch (e) {
        return d
    }
})

const timeText = computed(() => {
    const s = props.event.start_time ?? ''
    const e = props.event.end_time ?? ''
    if (s && e) return `${s} – ${e}`
    return s || e
})
</script>

<template>
    <div class="tcg-gym-badge-wrap">
        <div class="tcg-gym-badge" :style="{ '--badge-color': typeColor }">
            <svg viewBox="0 0 200 200" class="tcg-gym-badge-frame" aria-hidden="true">
                <circle cx="100" cy="100" r="94" fill="currentColor" stroke="#FFD700" stroke-width="6"/>
                <circle cx="100" cy="100" r="82" fill="none" stroke="#FFD700" stroke-width="1" opacity="0.4"/>
            </svg>
            <svg viewBox="0 0 48 48" class="tcg-gym-badge-icon" aria-hidden="true">
                <!-- Generic interlocking rings (love symbol) — NOT a gym symbol -->
                <circle cx="18" cy="24" r="9" fill="none" stroke="#FFD700" stroke-width="2.4"/>
                <circle cx="30" cy="24" r="9" fill="none" stroke="#FFD700" stroke-width="2.4"/>
            </svg>
        </div>
        <h3 class="tcg-gym-name">{{ event.event_name }}</h3>
        <p v-if="dateText" class="tcg-gym-date">{{ dateText }}</p>
        <p v-if="timeText" class="tcg-gym-time">{{ timeText }}</p>
        <p v-if="event.venue_address" class="tcg-gym-addr">{{ event.venue_address }}</p>
        <a
            v-if="event.maps_url"
            :href="event.maps_url"
            target="_blank"
            rel="noopener noreferrer"
            class="tcg-gym-maps"
        >MAPS &#9658;</a>
    </div>
</template>

<style scoped>
.tcg-gym-badge-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    padding: 12px;
}
.tcg-gym-badge {
    position: relative;
    width: 200px;
    height: 200px;
    color: var(--badge-color, #7B68EE);
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.4));
}
.tcg-gym-badge-frame,
.tcg-gym-badge-icon {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
.tcg-gym-badge-icon {
    inset: 25%;
    width: 50%;
    height: 50%;
}
.tcg-gym-name {
    margin: 8px 0 0;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    font-size: 16px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--tcg-frame-gold, #FFD700);
}
.tcg-gym-date {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--tcg-text, #F4F1E6);
}
.tcg-gym-time, .tcg-gym-addr {
    margin: 0;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--tcg-text-muted, #A6A4B8);
    max-width: 220px;
    line-height: 1.4;
}
.tcg-gym-maps {
    margin-top: 4px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    font-weight: 700;
    color: var(--tcg-frame-gold, #FFD700);
    border: 1px solid var(--tcg-frame-gold, #FFD700);
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    letter-spacing: 0.12em;
    transition: background 0.2s ease, color 0.2s ease;
}
.tcg-gym-maps:hover {
    background: var(--tcg-frame-gold, #FFD700);
    color: var(--tcg-bg, #1A1F3A);
}
@media (max-width: 480px) {
    .tcg-gym-badge { width: 160px; height: 160px; }
}
</style>
