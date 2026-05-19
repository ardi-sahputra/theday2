<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import TrackRow from './TrackRow.vue'
import { computed } from 'vue'

const props = defineProps({
    stories:       { type: Array,    default: () => [] },
    mockDuration:  { type: Function, default: (i) => `${3 + (i % 4)}:${String((i * 17) % 60).padStart(2, '0')}` },
})

// Cap to 5 rows per spec
const visibleStories = computed(() => props.stories.slice(0, 5))
</script>

<template>
    <section
        class="sw-slide sw-slide-songs"
        data-slide-key="top-songs"
        :style="{
            '--sw-bg-from':       '#FFCB3E',
            '--sw-bg-to':         '#FF6B35',
            '--sw-bg-direction':  '160deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">TOP SONGS</span>
                <span class="sw-slide-counter">03 / 10</span>
            </header>
            <h2 class="sw-slide-title">YOUR MOST PLAYED MOMENTS</h2>

            <div v-if="visibleStories.length" class="sw-tracks">
                <TrackRow
                    v-for="(story, idx) in visibleStories"
                    :key="story.id ?? idx"
                    :index="idx"
                    :title="story.title ?? 'Untitled track'"
                    :subtitle="story.date ?? story.subtitle ?? ''"
                    :duration="mockDuration(idx)"
                    :thumbnail-url="story.photo_url ?? null"
                    :fallback-hue="(idx * 47) % 360"
                />
            </div>

            <p v-else class="sw-empty">
                Belum ada lagu favorit. Tambah love story di customize wizard.
            </p>
        </div>
    </section>
</template>

<style scoped>
.sw-tracks { display: flex; flex-direction: column; gap: 4px; margin-top: 24px; }
.sw-empty {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    color: rgba(255,255,255,0.85);
    text-align: center;
    margin: 32px 0 0;
}
</style>
