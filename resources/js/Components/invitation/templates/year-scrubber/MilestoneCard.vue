<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
defineProps({
    milestone: { type: Object, default: null },
})
</script>

<template>
    <article v-if="milestone" class="ys-card">
        <div class="ys-card-photo-frame">
            <img
                v-if="milestone.photo_url"
                :src="milestone.photo_url"
                :alt="milestone.title || ''"
                class="ys-card-photo"
                loading="lazy"
                decoding="async"
            />
            <div v-else class="ys-card-photo ys-card-photo--ph" aria-hidden="true"/>
        </div>
        <div class="ys-card-body">
            <p class="ys-card-year">{{ milestone.year }}</p>
            <h3 class="ys-card-title">{{ milestone.title || '—' }}</h3>
            <p v-if="milestone.description" class="ys-card-desc">{{ milestone.description }}</p>
        </div>
    </article>
    <div v-else class="ys-card ys-card--empty">
        <p class="ys-card-empty">Cerita perjalanan belum diisi</p>
    </div>
</template>

<style scoped>
.ys-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
    max-width: 520px;
    background: rgba(255,255,255,0.55);
    backdrop-filter: blur(6px);
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(26,46,74,0.10);
}
.ys-card-photo-frame {
    width: 100%;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-radius: 8px;
    background: #F5F0E8;
}
.ys-card-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transform-origin: center center;
    animation: ys-kenburns 12s ease-in-out infinite alternate;
}
.ys-card-photo--ph {
    background: linear-gradient(135deg, #F5F0E8, #E8D9C0);
}
@keyframes ys-kenburns {
    0%   { transform: scale(1.00) translate(0%, 0%); }
    100% { transform: scale(1.08) translate(2%, -1%); }
}

.ys-card-body { display: flex; flex-direction: column; gap: 6px; padding: 0 4px 8px; }
.ys-card-year {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: #C9A961;
    letter-spacing: 0.15em;
    margin: 0;
}
.ys-card-title {
    font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-weight: 600;
    color: #1A2E4A;
    font-size: 24px;
    line-height: 1.25;
    margin: 0;
}
.ys-card-desc {
    font-family: 'EB Garamond', Georgia, serif;
    color: #2A4063;
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

.ys-card--empty {
    align-items: center; justify-content: center;
    min-height: 160px;
}
.ys-card-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #A39E94;
    font-size: 18px;
    margin: 0;
}

@media (min-width: 768px) {
    .ys-card { flex-direction: row; max-width: 720px; padding: 20px; }
    .ys-card-photo-frame { flex: 0 0 280px; aspect-ratio: 4 / 3; }
    .ys-card-body { flex: 1; justify-content: center; }
    .ys-card-title { font-size: 28px; }
}

@media (prefers-reduced-motion: reduce) {
    .ys-card-photo { animation: none; transform: scale(1.04); }
}
</style>
