<script setup>
import { computed } from 'vue'

const props = defineProps({
    tracks:         { type: Array,  required: true }, // already filtered by sectionEnabled
    currentSide:    { type: String, default: 'A' },
    currentTrackId: { type: [String, null], default: null },
})
const emit = defineEmits(['select', 'flip'])

const sideTracks = computed(() =>
    props.tracks.filter(t => t.side === props.currentSide)
)

function onKey(ev, idx) {
    if (ev.key === 'ArrowDown') {
        ev.preventDefault()
        const next = sideTracks.value[idx + 1]
        if (next) emit('select', next.id)
    } else if (ev.key === 'ArrowUp') {
        ev.preventDefault()
        const prev = sideTracks.value[idx - 1]
        if (prev) emit('select', prev.id)
    } else if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault()
        emit('select', sideTracks.value[idx].id)
    }
}
</script>

<template>
    <aside class="vr-tracklist" :aria-label="`Side ${currentSide} tracklist`">
        <header class="vr-tl-header">
            <span class="vr-tl-side-label">SIDE {{ currentSide }}</span>
            <span class="vr-tl-divider"/>
            <span class="vr-tl-title">TRACKLIST</span>
        </header>

        <ul class="vr-tl-list" role="listbox" :aria-label="`Side ${currentSide} tracks`">
            <li
                v-for="(track, idx) in sideTracks"
                :key="track.id"
                class="vr-track-row"
                :class="{ 'vr-track-row--active': track.id === currentTrackId }"
                role="option"
                :aria-selected="track.id === currentTrackId"
            >
                <button
                    type="button"
                    class="vr-track-btn"
                    :tabindex="track.id === currentTrackId ? 0 : -1"
                    @click="emit('select', track.id)"
                    @keydown="ev => onKey(ev, idx)"
                >
                    <span class="vr-track-id">{{ track.id }}</span>
                    <span class="vr-track-title">{{ track.title }}</span>
                    <span class="vr-track-dur">{{ track.duration }}</span>
                    <span v-if="track.id === currentTrackId" class="vr-track-eq" aria-hidden="true">
                        <span class="vr-eq-bar"/>
                        <span class="vr-eq-bar"/>
                        <span class="vr-eq-bar"/>
                    </span>
                </button>
            </li>
            <li v-if="!sideTracks.length" class="vr-tl-empty">
                Tidak ada track aktif di Side {{ currentSide }}.
            </li>
        </ul>

        <footer class="vr-tl-footer">
            <button
                type="button"
                class="vr-tl-flip"
                @click="emit('flip', currentSide === 'A' ? 'B' : 'A')"
            >
                <svg v-if="currentSide === 'A'" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                    <path d="M3 8h9M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.4"/>
                </svg>
                <svg v-else viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                    <path d="M13 8H4M7 4L3 8l4 4" fill="none" stroke="currentColor" stroke-width="1.4"/>
                </svg>
                <span>FLIP TO SIDE {{ currentSide === 'A' ? 'B' : 'A' }}</span>
            </button>
        </footer>
    </aside>
</template>

<style scoped>
.vr-tracklist {
    background: rgba(10,10,10,0.92);
    border: 1px solid rgba(184,144,47,0.25);
    color: #F5E6CC;
    display: flex; flex-direction: column;
    min-height: 0;
}
.vr-tl-header {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid rgba(184,144,47,0.25);
}
.vr-tl-side-label {
    font-family: 'Bebas Neue', sans-serif;
    color: #B8902F;
    font-size: 16px;
    letter-spacing: 0.2em;
}
.vr-tl-divider { flex: 1; height: 1px; background: rgba(184,144,47,0.3); }
.vr-tl-title {
    font-family: 'Bebas Neue', sans-serif;
    color: #D8C8A8;
    font-size: 13px;
    letter-spacing: 0.3em;
}
.vr-tl-list {
    list-style: none;
    margin: 0;
    padding: 4px 0;
    overflow-y: auto;
    flex: 1;
}
.vr-track-row {
    transition: background-color 0.2s ease, transform 0.2s ease;
}
.vr-track-row:hover {
    transform: translateY(-2px);
    background-color: rgba(184,144,47,0.08);
}
.vr-track-row--active {
    background-color: rgba(245,230,204,0.95);
    color: #1a1a1a;
}
.vr-track-btn {
    display: grid;
    grid-template-columns: 36px 1fr auto 18px;
    align-items: center;
    gap: 12px;
    width: 100%;
    min-height: 44px;
    background: transparent;
    border: 0;
    padding: 12px 18px;
    color: inherit;
    cursor: pointer;
    text-align: left;
    font: inherit;
}
.vr-track-btn:focus-visible {
    outline: 2px solid #B8902F;
    outline-offset: -2px;
}
.vr-track-id {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 14px;
    color: #B8902F;
    letter-spacing: 0.15em;
}
.vr-track-row--active .vr-track-id { color: #C73E3A; }
.vr-track-title {
    font-family: 'Bree Serif', serif;
    font-size: 15px;
    line-height: 1.3;
}
.vr-track-dur {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-variant-numeric: tabular-nums;
    color: #D8C8A8;
}
.vr-track-row--active .vr-track-dur { color: #5C3A21; }
.vr-track-eq {
    display: inline-flex; align-items: flex-end;
    gap: 1px; height: 12px; width: 16px;
}
.vr-eq-bar {
    width: 3px; background: #C73E3A;
    animation: vr-eq 0.9s ease-in-out infinite alternate;
}
.vr-eq-bar:nth-child(2) { animation-delay: 0.2s; }
.vr-eq-bar:nth-child(3) { animation-delay: 0.4s; }
@keyframes vr-eq {
    from { height: 4px; }
    to   { height: 12px; }
}
.vr-tl-empty {
    padding: 16px 18px;
    color: #D8C8A8;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
}
.vr-tl-footer {
    border-top: 1px solid rgba(184,144,47,0.25);
    padding: 12px 18px;
}
.vr-tl-flip {
    display: inline-flex; align-items: center; gap: 8px;
    width: 100%;
    min-height: 44px;
    background: transparent;
    border: 1px solid #B8902F;
    color: #B8902F;
    padding: 10px 14px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 13px;
    letter-spacing: 0.25em;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
    border-radius: 2px;
    justify-content: center;
}
.vr-tl-flip:hover { background: #B8902F; color: #0a0a0a; }
@media (prefers-reduced-motion: reduce) {
    .vr-track-row { transition: background-color 0.2s ease; }
    .vr-track-row:hover { transform: none; }
    .vr-eq-bar { animation: none; height: 8px; }
    .vr-tl-flip { transition: none; }
}
</style>
