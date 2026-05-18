<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
defineProps({
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    groomPhoto:   { type: String, default: null },
    bridePhoto:   { type: String, default: null },
    groomParents: { type: String, default: '' },
    brideParents: { type: String, default: '' },
    groomTags:    { type: String, default: 'Romantic · Dreamer · Coffee Lover' },
    brideTags:    { type: String, default: 'Soulful · Reader · Sunset Chaser' },
})
</script>

<template>
    <section
        class="sw-slide sw-slide-artists"
        data-slide-key="top-artists"
        :style="{
            '--sw-bg-from':       '#E13300',
            '--sw-bg-to':         '#C20BB1',
            '--sw-bg-direction':  '135deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">TOP ARTISTS</span>
                <span class="sw-slide-counter">02 / 10</span>
            </header>
            <h2 class="sw-slide-title">YOUR FAVORITE ARTISTS THIS YEAR</h2>

            <div class="sw-artists-grid">
                <article class="sw-artist-card sw-artist-card--in-right">
                    <div class="sw-artist-photo-wrap">
                        <img v-if="groomPhoto" :src="groomPhoto" :alt="groomName" class="sw-artist-photo"/>
                        <span v-else class="sw-artist-photo sw-artist-photo--ph" aria-hidden="true"/>
                        <span class="sw-badge-rank sw-badge-rank--1">
                            <svg viewBox="0 0 32 32" width="16" height="16" aria-hidden="true">
                                <text x="16" y="22" text-anchor="middle" font-family="Inter" font-weight="900" font-size="18" fill="#FFFFFF">1</text>
                            </svg>
                            <span>MOST PLAYED</span>
                        </span>
                    </div>
                    <h3 class="sw-artist-name">{{ groomName }}</h3>
                    <p class="sw-artist-tags">{{ groomTags }}</p>
                    <p v-if="groomParents" class="sw-artist-parents">{{ groomParents }}</p>
                </article>

                <article class="sw-artist-card sw-artist-card--in-left">
                    <div class="sw-artist-photo-wrap">
                        <img v-if="bridePhoto" :src="bridePhoto" :alt="brideName" class="sw-artist-photo"/>
                        <span v-else class="sw-artist-photo sw-artist-photo--ph" aria-hidden="true"/>
                        <span class="sw-badge-rank sw-badge-rank--2">
                            <svg viewBox="0 0 32 32" width="16" height="16" aria-hidden="true">
                                <text x="16" y="22" text-anchor="middle" font-family="Inter" font-weight="900" font-size="18" fill="#FFFFFF">2</text>
                            </svg>
                            <span>RUNNER UP</span>
                        </span>
                    </div>
                    <h3 class="sw-artist-name">{{ brideName }}</h3>
                    <p class="sw-artist-tags">{{ brideTags }}</p>
                    <p v-if="brideParents" class="sw-artist-parents">{{ brideParents }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-artists-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    margin-top: 32px;
}
@media (min-width: 768px) {
    .sw-artists-grid { grid-template-columns: 1fr 1fr; gap: 40px; }
}
.sw-artist-card {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px;
    opacity: 0;
    transform: translateX(0);
    transition: opacity 0.6s ease-out 0.15s, transform 0.6s ease-out 0.15s;
}
.sw-artist-card--in-right { transform: translateX(40px); }
.sw-artist-card--in-left  { transform: translateX(-40px); }
:global(.sw-visible) .sw-artist-card { opacity: 1; transform: translateX(0); }

.sw-artist-photo-wrap {
    position: relative;
    width: 100%;
    max-width: 240px;
    aspect-ratio: 1 / 1;
}
.sw-artist-photo {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 8px;
    display: block;
}
.sw-artist-photo--ph { background: rgba(255,255,255,0.18); }
.sw-badge-rank {
    position: absolute;
    top: 10px; left: 10px;
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 10px;
    background: rgba(0,0,0,0.7);
    color: #FFFFFF;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 10px;
    letter-spacing: 0.12em;
    transform: scale(0);
    opacity: 0;
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s, opacity 0.3s ease 0.4s;
}
:global(.sw-visible) .sw-badge-rank { transform: scale(1); opacity: 1; }
.sw-artist-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 28px;
    margin: 16px 0 0;
    letter-spacing: -0.01em;
}
.sw-artist-tags {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    margin: 0;
    opacity: 0.85;
}
.sw-artist-parents {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    margin: 4px 0 0;
    opacity: 0.7;
    line-height: 1.5;
}
@media (prefers-reduced-motion: reduce) {
    .sw-artist-card, .sw-artist-card--in-left, .sw-artist-card--in-right {
        opacity: 1; transform: none; transition: none;
    }
    .sw-badge-rank { transform: scale(1); opacity: 1; transition: none; }
}
</style>
