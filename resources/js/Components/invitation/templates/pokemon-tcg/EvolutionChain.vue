<script setup>
import { computed } from 'vue'
import TrainerCard from './TrainerCard.vue'

const props = defineProps({
    stories:       { type: Array,  default: () => [] },
    holoIntensity: { type: Number, default: 0.55 },
    tiltEnabled:   { type: Boolean, default: true },
})

const TYPE_ROTATION = ['romantic', 'tender', 'joyful', 'sacred']

const stages = computed(() => props.stories.map((s, i) => ({
    type:         TYPE_ROTATION[Math.min(i, 3)],
    statsLabel:   `STAGE ${i + 1}`,
    artUrl:       s.photo_url ?? null,
    name:         s.title ?? `Stage ${i + 1}`,
    description:  s.description ?? '',
    editionText:  s.date ?? '',
})))
</script>

<template>
    <div class="tcg-evo-chain">
        <template v-for="(stage, i) in stages" :key="i">
            <TrainerCard
                :type="stage.type"
                :stats-label="stage.statsLabel"
                :art-url="stage.artUrl"
                :name="stage.name"
                :description="stage.description"
                :edition-text="stage.editionText"
                :holo-intensity="holoIntensity"
                :tilt-enabled="tiltEnabled"
                size="sm"
            />
            <svg
                v-if="i < stages.length - 1"
                class="tcg-evo-arrow"
                :style="{ '--arrow-index': i }"
                viewBox="0 0 80 24"
                fill="none"
                stroke="#FFD700"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M4 12 L66 12 M58 4 L70 12 L58 20"/>
            </svg>
        </template>
    </div>
</template>

<style scoped>
.tcg-evo-chain {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    gap: 16px;
    overflow-x: auto;
    overflow-y: visible;
    padding: 8px 12px 24px;
    scroll-snap-type: x mandatory;
}
.tcg-evo-chain > :deep(.tcg-card) {
    flex: 0 0 auto;
    scroll-snap-align: center;
}
.tcg-evo-arrow {
    flex: 0 0 80px;
    width: 80px;
    height: 24px;
}
.tcg-evo-arrow path {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
    transition: stroke-dashoffset 1s ease-out;
    transition-delay: calc(var(--arrow-index, 0) * 0.15s);
}
.tcg-visible .tcg-evo-arrow path {
    stroke-dashoffset: 0;
}
@media (min-width: 961px) {
    .tcg-evo-chain {
        flex-wrap: wrap;
        justify-content: center;
        overflow-x: visible;
    }
}
@media (max-width: 720px) {
    .tcg-evo-chain {
        flex-direction: column;
    }
    .tcg-evo-arrow {
        transform: rotate(90deg);
        width: 48px;
        flex-basis: 48px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-evo-arrow path { stroke-dashoffset: 0; transition: none; }
}
</style>
