<template>
    <section class="ah-section ah-hero">
        <div class="ah-section-inner">
            <p class="ah-hero__bismillah" dir="rtl">&#1576;&#1616;&#1587;&#1618;&#1605;&#1616; &#1575;&#1604;&#1604;&#1607;&#1616; &#1575;&#1604;&#1585;&#1617;&#1614;&#1581;&#1618;&#1605;&#1614;&#1600;&#1606;&#1616; &#1575;&#1604;&#1585;&#1617;&#1614;&#1581;&#1616;&#1610;&#1618;&#1605;&#1616;</p>
            <header class="ah-section-header">
                <span class="ah-rule" aria-hidden="true"/>
                <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                <h2 class="ah-section-title">{{ openingLabel }}</h2>
                <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                <span class="ah-rule" aria-hidden="true"/>
            </header>
            <p v-if="openingText" class="ah-hero__body">
                <span class="ah-hero__dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
            </p>
        </div>
    </section>
</template>

<script setup>
defineProps({
    openingText:  { type: String, default: '' },
    openingLabel: { type: String, default: 'PEMBUKAAN' },
})
</script>

<style scoped>
.ah-section { position: relative; padding: 64px 24px; }
.ah-section-inner { max-width: 640px; margin: 0 auto; text-align: center; }
.ah-hero__bismillah {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: 28px;
    color: var(--ah-ink-decorative);
    text-align: center;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 32px;
}
.ah-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; margin: 0 auto 32px;
}
.ah-rule { flex: 0 0 32px; height: 1px; background: var(--ah-gold); opacity: 0.7; }
.ah-ornament { color: var(--ah-gold); font-size: 14px; opacity: 0.8; }
.ah-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--ah-ink);
    margin: 0;
}
.ah-hero__body {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 18px;
    line-height: 1.85;
    text-align: left;
    margin: 0;
}
.ah-hero__dropcap {
    float: left;
    font-size: 48px;
    line-height: 1;
    color: var(--ah-ink-decorative);
    margin: 4px 12px 0 0;
    font-family: 'EB Garamond', serif;
}
@media (min-width: 768px) { .ah-section { padding: 112px 56px; } }
</style>
