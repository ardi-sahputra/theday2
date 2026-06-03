<script setup>
import { computed } from 'vue'
import Vinyl       from './Vinyl.vue'
import Tonearm     from './Tonearm.vue'
import Tracklist   from './Tracklist.vue'
import AlbumCover  from './AlbumCover.vue'
import VolumeKnob  from './VolumeKnob.vue'

const props = defineProps({
    tracks:           { type: Array,   required: true },
    currentSide:      { type: String,  default: 'A' },
    currentTrack:     { type: Object,  default: null },
    currentTrackIndex:{ type: Number,  default: -1 },
    isPlaying:        { type: Boolean, default: false },
    volume:           { type: Number,  default: 0.6 },
    audioDisabled:    { type: Boolean, default: true },
    albumTitle:       { type: String,  default: 'THE WEDDING SESSIONS' },
    labelColor:       { type: String,  default: 'red' },
    centerSub:        { type: String,  default: '2026' },
    monogram:         { type: String,  default: 'A & B' },
    isPremium:        { type: Boolean, default: false },
})
const emit = defineEmits(['select-track', 'change-volume', 'flip'])

const headerSubtitle = computed(() => props.currentTrack
    ? `${props.currentTrack.id} · ${props.currentTrack.title}`
    : 'TAP A TRACK')
</script>

<template>
    <div class="vr-turntable-layout">
        <header class="vr-header">
            <span class="vr-header-title">{{ albumTitle }}</span>
            <span class="vr-header-rule"/>
            <span class="vr-header-now">{{ headerSubtitle }}</span>
            <span
                class="vr-header-side"
                :class="`vr-header-side--${currentSide.toLowerCase()}`"
            >SIDE {{ currentSide }}</span>
        </header>

        <div class="vr-layout-grid">
            <Tracklist
                class="vr-col-tracklist"
                :tracks="tracks"
                :current-side="currentSide"
                :current-track-id="currentTrack?.id ?? null"
                @select="id => emit('select-track', id)"
                @flip="side => emit('flip', side)"
            />

            <section class="vr-col-turntable" aria-label="Turntable">
                <div class="vr-plinth">
                    <div class="vr-plinth-wood"/>
                    <div class="vr-plinth-top">
                        <div class="vr-platter">
                            <Vinyl
                                class="vr-platter-vinyl"
                                :spinning="isPlaying"
                                :label-color="labelColor"
                                :center-label-text="albumTitle"
                                :center-sub-text="centerSub"
                                :monogram="monogram"
                                :is-premium="isPremium"
                            />
                            <Tonearm
                                :track-index="currentTrackIndex"
                                :side="currentSide"
                            />
                        </div>
                        <div class="vr-plinth-controls">
                            <VolumeKnob
                                :value="volume"
                                :disabled="audioDisabled"
                                @update:value="v => emit('change-volume', v)"
                            />
                            <div class="vr-power">
                                <span
                                    class="vr-power-led"
                                    :class="{ 'vr-power-led--on': isPlaying }"
                                    aria-hidden="true"
                                />
                                <span class="vr-power-label">{{ isPlaying ? 'ON' : 'IDLE' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="vr-col-album">
                <AlbumCover :track="currentTrack">
                    <template #default="{ track }">
                        <slot :track-key="track.key"/>
                    </template>
                </AlbumCover>
            </section>
        </div>
    </div>
</template>

<style scoped>
.vr-turntable-layout {
    position: relative; z-index: 2;
    width: 100%;
    min-height: 100vh;
    display: flex; flex-direction: column;
    padding: 16px;
    box-sizing: border-box;
}
.vr-header {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(184,144,47,0.25);
    margin-bottom: 12px;
}
.vr-header-title {
    font-family: 'Bebas Neue', sans-serif;
    color: #F5E6CC;
    font-size: 18px;
    letter-spacing: 0.3em;
}
.vr-header-rule { flex: 1; height: 1px; background: rgba(184,144,47,0.3); }
.vr-header-now {
    font-family: 'Bree Serif', serif;
    color: #D8C8A8;
    font-size: 13px;
}
.vr-header-side {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 2px;
    letter-spacing: 0.2em;
    color: #F5E6CC;
}
.vr-header-side--a { background: #C73E3A; }
.vr-header-side--b { background: #5F7048; }

.vr-layout-grid {
    flex: 1;
    display: grid;
    gap: 16px;
    grid-template-columns: 1fr;
}
@media (min-width: 768px) {
    .vr-layout-grid { grid-template-columns: 44px 1fr; }
    .vr-col-album { grid-column: 1 / -1; }
}
@media (min-width: 1024px) {
    .vr-layout-grid {
        grid-template-columns: 280px minmax(0, 1fr) minmax(360px, 1fr);
        align-items: stretch;
    }
    .vr-col-album { grid-column: auto; }
}

.vr-col-turntable { min-width: 0; display: flex; align-items: center; justify-content: center; }

.vr-plinth {
    position: relative;
    width: 100%;
    max-width: 520px;
    aspect-ratio: 1 / 1;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 24px 40px -12px rgba(0,0,0,0.6), 0 8px 16px -4px rgba(0,0,0,0.4);
}
.vr-plinth-wood {
    position: absolute; inset: 0;
    background: #5C3A21 url('/images/templates/vinyl-record/wood-grain.svg') center/cover no-repeat;
}
.vr-plinth-top {
    position: absolute;
    inset: 24px;
    background: linear-gradient(180deg, #0a0a0a 0%, #050505 100%);
    border-radius: 4px;
    display: grid;
    grid-template-rows: 1fr auto;
    padding: 16px;
    gap: 12px;
    box-sizing: border-box;
}
.vr-platter {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 50%;
    background: radial-gradient(circle, #0f0f0f 0%, #0a0a0a 100%);
    margin: 0 auto;
    max-width: 100%;
    align-self: center;
    justify-self: center;
}
.vr-platter-vinyl {
    position: absolute;
    inset: 4%;
}
.vr-plinth-controls {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 12px;
    background: rgba(245,230,204,0.04);
    border-top: 1px solid rgba(184,144,47,0.2);
    border-radius: 2px;
}
.vr-power { display: inline-flex; align-items: center; gap: 8px; }
.vr-power-led {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: rgba(199,62,58,0.25);
    transition: background 0.3s ease, box-shadow 0.3s ease;
}
.vr-power-led--on {
    background: #C73E3A;
    box-shadow: 0 0 8px #C73E3A;
}
.vr-power-label {
    font-family: 'Bebas Neue', sans-serif;
    color: #D8C8A8;
    font-size: 11px;
    letter-spacing: 0.2em;
}
.vr-col-album { min-width: 0; }

@media (prefers-reduced-motion: reduce) {
    .vr-power-led { transition: none; }
}
</style>
