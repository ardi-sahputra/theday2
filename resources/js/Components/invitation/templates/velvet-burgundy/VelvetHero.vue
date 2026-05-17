<script setup>
defineProps({
    groomName:   { type: String,  default: '' },
    brideName:   { type: String,  default: '' },
    openingText: { type: String,  default: '' },
    eventDate:   { type: String,  default: '' },
    monogram:    { type: String,  default: 'B & G' },
    paperPanels: { type: Boolean, default: true },
})
</script>

<template>
    <section
        class="vb-hero"
        :class="{ 'vb-hero--paper': paperPanels }"
    >
        <p class="vb-hero-monogram">{{ monogram }}</p>
        <h2 class="vb-hero-salam">Bismillahirrahmanirrahim</h2>
        <img
            src="/images/templates/velvet-burgundy/filigree-divider.svg"
            alt=""
            aria-hidden="true"
            class="vb-hero-divider"
        />
        <p v-if="openingText" class="vb-hero-opening">{{ openingText }}</p>
        <p v-if="eventDate" class="vb-hero-date">{{ eventDate }}</p>
        <p class="vb-hero-couple">{{ groomName }} &amp; {{ brideName }}</p>
        <p class="vb-hero-signature">— Keluarga Mempelai</p>
    </section>
</template>

<style scoped>
.vb-hero {
    padding: 56px 28px 48px;
    text-align: center;
    background: var(--vb-burgundy-deep, #3a0c0e);
    color: var(--vb-cream, #f8f1e7);
    border-bottom: 1px solid rgba(168,122,74,0.2);
}
.vb-hero--paper {
    background-image: url('/images/templates/velvet-burgundy/paper-cream.webp');
    background-size: cover;
    color: var(--vb-burgundy-deep, #3a0c0e);
}

.vb-hero-monogram {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 64px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0 0 12px;
    line-height: 1;
    text-shadow: 0 2px 8px rgba(212,165,116,0.25);
}

.vb-hero-salam {
    font-family: 'Cormorant SC', serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0 0 8px;
}

.vb-hero-divider {
    width: 220px;
    height: 24px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0 auto 16px;
    display: block;
}

.vb-hero-opening {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 17px;
    line-height: 1.75;
    max-width: 520px;
    margin: 0 auto 20px;
    white-space: pre-line;
}

.vb-hero-date {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--vb-gold-antique, #a87a4a);
    margin: 0 0 8px;
}

.vb-hero-couple {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-weight: 700;
    font-size: 26px;
    margin: 0 0 4px;
}

.vb-hero-signature {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--vb-gold-antique, #a87a4a);
    margin: 8px 0 0;
}
</style>
