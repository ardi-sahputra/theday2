<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
defineProps({
    events: { type: Array, default: () => [] },
})

function dayName(dateStr) {
    if (!dateStr) return ''
    try {
        const d = new Date(dateStr)
        return d.toLocaleDateString('id-ID', { weekday: 'long' }).toUpperCase()
    } catch { return '' }
}
function formattedDate(ev) {
    return ev.event_date_formatted ?? ev.event_date ?? ''
}
</script>

<template>
    <section
        class="sw-slide sw-slide-schedule"
        data-slide-key="schedule"
        :style="{
            '--sw-bg-from':       '#0066FF',
            '--sw-bg-to':         '#00D4FF',
            '--sw-bg-direction':  '145deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">LISTENING SCHEDULE</span>
                <span class="sw-slide-counter">04 / 10</span>
            </header>
            <h2 class="sw-slide-title">YOUR UPCOMING DROPS</h2>

            <div class="sw-drops">
                <article
                    v-for="(ev, idx) in events"
                    :key="ev.id ?? idx"
                    class="sw-drop-card"
                    :style="{ '--d': (idx * 0.15).toFixed(2) + 's' }"
                >
                    <span class="sw-drop-pill">
                        DROP #{{ String(idx + 1).padStart(2, '0') }} · {{ dayName(ev.event_date) || 'COMING SOON' }}
                    </span>
                    <h3 class="sw-drop-name">{{ (ev.event_name ?? '').toUpperCase() }}</h3>
                    <p class="sw-drop-date">{{ formattedDate(ev) }}</p>
                    <p class="sw-drop-time">
                        <span v-if="ev.start_time">{{ ev.start_time }}</span>
                        <span v-if="ev.end_time"> – {{ ev.end_time }}</span>
                        <span v-if="ev.timezone"> · {{ ev.timezone }}</span>
                    </p>
                    <p v-if="ev.venue_name || ev.venue_address || ev.location" class="sw-drop-address">
                        {{ ev.venue_name ? ev.venue_name + ' · ' : '' }}{{ ev.venue_address ?? ev.location ?? '' }}
                    </p>
                    <a
                        v-if="ev.maps_url"
                        :href="ev.maps_url" target="_blank" rel="noopener"
                        class="sw-drop-maps"
                    >OPEN MAPS ↗</a>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-drops { display: flex; flex-direction: column; gap: 20px; margin-top: 24px; }
.sw-drop-card {
    background: rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 16px;
    padding: 24px;
    display: flex; flex-direction: column; gap: 8px;
    opacity: 0;
    transform: translateY(40px);
    transition:
        opacity 0.6s ease-out var(--d, 0s),
        transform 0.6s ease-out var(--d, 0s);
}
:global(.sw-visible) .sw-drop-card { opacity: 1; transform: translateY(0); }
.sw-drop-pill {
    align-self: flex-start;
    display: inline-block;
    background: #FFFFFF;
    color: #0066FF;
    padding: 5px 12px;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
}
.sw-drop-name {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(32px, 6vw, 44px);
    line-height: 1;
    margin: 4px 0 0;
    letter-spacing: -0.02em;
}
.sw-drop-date {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    margin: 4px 0 0;
}
.sw-drop-time {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    margin: 0;
}
.sw-drop-address {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    opacity: 0.72;
    margin: 4px 0 0;
    line-height: 1.5;
}
.sw-drop-maps {
    align-self: flex-start;
    margin-top: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.18);
    color: #FFFFFF;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-decoration: none;
    transition: background 0.2s ease;
}
.sw-drop-maps:hover { background: rgba(255,255,255,0.3); }
@media (prefers-reduced-motion: reduce) {
    .sw-drop-card { opacity: 1; transform: none; transition: none; }
    .sw-drop-maps { transition: none; }
}
</style>
