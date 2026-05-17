<script setup>
import PostalCard       from './PostalCard.vue'
import PostalTypewriter from './PostalTypewriter.vue'

defineProps({
    openingText:    { type: String, default: '' },
    firstEventDate: { type: String, default: '' },
    travelCity:     { type: String, default: 'JAKARTA' },
    typewriterSpeed:{ type: String, default: 'normal' },
})
</script>

<template>
    <section class="vp-section vp-hero vp-reveal">
        <PostalCard
            paper="aged-1"
            :rotation="-1"
            :postmark="{ variant: 'par-avion', date: firstEventDate, position: 'center-top' }"
            :stamps="[
                { theme: 'love',     position: 'tl', rotate: -6 },
                { city:  travelCity, position: 'tr', rotate:  6 },
                { theme: 'forever',  position: 'bl', rotate:  4 },
                { theme: 'wedding',  position: 'br', rotate: -4 },
            ]"
            :washi="{ pattern: 'striped', position: 'bottom' }"
        >
            <template #header>
                <h2 class="vp-hero-heading">Sebuah Kabar Bahagia</h2>
            </template>
            <PostalTypewriter
                v-if="openingText"
                class="vp-hero-body"
                :text="openingText"
                :speed="typewriterSpeed"
                :skippable="true"
                mode="typing"
            />
        </PostalCard>
    </section>
</template>

<style scoped>
.vp-hero { padding: 56px 12px 32px; }
.vp-hero-heading {
    font-family: 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    color: #3a2d1f;
    font-size: 26px;
    margin: 0;
    text-align: center;
}
.vp-hero-body {
    display: block;
    margin-top: 16px;
    font-family: 'Courier Prime', 'Courier New', monospace;
    color: #3a2d1f;
    font-size: 15px;
    line-height: 1.85;
}
</style>
