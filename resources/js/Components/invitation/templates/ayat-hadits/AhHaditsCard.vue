<template>
    <article class="ah-hadits-card">
        <header class="ah-hadits-card__header">
            <span class="ah-hadits-card__label">HADITS</span>
            <span class="ah-hadits-card__source">{{ hadits.source }}</span>
        </header>
        <p v-if="hadits.sanad" class="ah-hadits-card__sanad">{{ hadits.sanad }}</p>
        <div class="ah-hadits-card__arabic" dir="rtl">{{ hadits.matn_arabic }}</div>
        <p v-if="hadits.transliteration" class="ah-hadits-card__translit"><em>{{ hadits.transliteration }}</em></p>
        <p class="ah-hadits-card__translation">{{ hadits.translation_id }}</p>
        <footer v-if="hadits.attribution" class="ah-hadits-card__attribution">{{ hadits.attribution }}</footer>
    </article>
</template>

<script setup>
defineProps({
    hadits: {
        type: Object,
        required: true,
        // expected shape: { source, sanad, matn_arabic, transliteration, translation_id, attribution }
    },
})
</script>

<style scoped>
.ah-hadits-card {
    background: var(--ah-parchment-deep);
    border: 1px solid var(--ah-divider);
    border-top: 3px solid var(--ah-gold);
    padding: 36px 32px;
    border-radius: 2px;
    max-width: 640px;
    margin: 0 auto 32px;
}
.ah-hadits-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--ah-divider);
    padding-bottom: 12px;
}
.ah-hadits-card__label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.32em;
    color: var(--ah-gold-deep);
    text-transform: uppercase;
}
.ah-hadits-card__source {
    font-family: 'EB Garamond', serif;
    font-size: 13px;
    font-style: italic;
    color: var(--ah-ink-soft);
}
.ah-hadits-card__sanad {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--ah-ink-soft);
    margin: 0 0 16px;
    line-height: 1.65;
}
.ah-hadits-card__arabic {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: 22px;
    line-height: 1.9;
    color: var(--ah-ink);
    text-align: center;
    direction: rtl;
    margin: 24px 0;
    letter-spacing: 0;
}
.ah-hadits-card__translit {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: var(--ah-ink-soft);
    line-height: 1.7;
    margin: 0 0 12px;
    text-align: center;
}
.ah-hadits-card__translation {
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    color: var(--ah-ink);
    line-height: 1.75;
    text-align: justify;
    margin: 0 0 12px;
}
.ah-hadits-card__attribution {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.18em;
    color: var(--ah-gold-deep);
    text-transform: uppercase;
    text-align: right;
}
@media (max-width: 480px) {
    .ah-hadits-card { padding: 28px 20px; }
    .ah-hadits-card__arabic { font-size: 18px; }
}
</style>
