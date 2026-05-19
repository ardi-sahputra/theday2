<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed } from 'vue'
import YearDigitRoll from './YearDigitRoll.vue'
import MilestoneCard from './MilestoneCard.vue'

const props = defineProps({
    currentYear:     { type: Number, required: true },
    activeMilestone: { type: Object, default: null },
    isPostWedding:   { type: Boolean, default: false },
    weddingDate:     { type: String,  default: '' },
    coverUrl:        { type: String,  default: null },
    groomName:       { type: String,  default: '' },
    brideName:       { type: String,  default: '' },
})

const displayYear = computed(() => Math.floor(props.currentYear))

const weddingCard = computed(() => ({
    year:        displayYear.value,
    title:       `${props.groomName} & ${props.brideName}`,
    description: props.weddingDate ? `Akad & Resepsi · ${props.weddingDate}` : 'Hari yang kami nanti',
    photo_url:   props.coverUrl,
}))
</script>

<template>
    <section
        class="ys-hero"
        :class="{ 'ys-hero--shrunken': isPostWedding }"
    >
        <div class="ys-hero-year">
            <YearDigitRoll
                :year="displayYear"
                :size="isPostWedding ? 'large' : 'huge'"
            />
            <span v-if="isPostWedding" class="ys-hero-year-tag">THE BIG DAY</span>
        </div>

        <div class="ys-hero-stage">
            <Transition name="ys-card" mode="out-in">
                <MilestoneCard
                    v-if="isPostWedding"
                    :key="`w-${displayYear}`"
                    :milestone="weddingCard"
                />
                <MilestoneCard
                    v-else
                    :key="activeMilestone ? `m-${activeMilestone.year}-${activeMilestone.title}` : 'empty'"
                    :milestone="activeMilestone"
                />
            </Transition>
        </div>
    </section>
</template>

<style scoped>
.ys-hero {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 48px 20px 24px;
    min-height: 70vh;
    transition: min-height 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.ys-hero--shrunken { min-height: 50vh; }

.ys-hero-year {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-align: center;
}
.ys-hero-year-tag {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: #C9A961;
    letter-spacing: 0.4em;
}

.ys-hero-stage {
    display: flex;
    justify-content: center;
    width: 100%;
}

@media (min-width: 768px) {
    .ys-hero {
        flex-direction: row;
        align-items: center;
        gap: 48px;
        padding: 72px 64px 40px;
    }
    .ys-hero-year { flex: 0 0 45%; }
    .ys-hero-stage { flex: 1; }
}

/* Card crossfade */
.ys-card-enter-active { transition: opacity 0.4s ease-out, transform 0.4s ease-out; }
.ys-card-leave-active { transition: opacity 0.3s ease-in, transform 0.3s ease-in; }
.ys-card-enter-from   { opacity: 0; transform: scale(1.05); }
.ys-card-leave-to     { opacity: 0; transform: scale(0.95); }

@media (prefers-reduced-motion: reduce) {
    .ys-hero { transition: none; }
    .ys-card-enter-active, .ys-card-leave-active { transition: opacity 0.2s ease; }
    .ys-card-enter-from, .ys-card-leave-to { transform: none; }
}
</style>
