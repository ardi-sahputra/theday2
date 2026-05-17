<script setup>
defineProps({
    coverPhotoUrl:  { type: String, default: null },
    coverTextColor: { type: String, default: '#ffffff' },
    groomName:      { type: String, default: '' },
    brideName:      { type: String, default: '' },
    weddingDate:    { type: String, default: '' },
    eiffelVisible:  { type: Boolean, default: true },
})
defineEmits(['open'])
</script>

<template>
    <section class="bp-cover">
        <div
            class="bp-cover-photo"
            :style="coverPhotoUrl
                ? { backgroundImage: `url(${coverPhotoUrl})` }
                : { background: '#3d3d3d' }"
            aria-hidden="true"
        />

        <img
            class="bp-cover-wash"
            src="/images/templates/belle-epoque/wash-blush.webp"
            alt=""
            aria-hidden="true"
            loading="eager"
        />
        <div class="bp-cover-gradient" aria-hidden="true"/>

        <p class="bp-cover-eyebrow" :style="{ color: coverTextColor }">Le Mariage de</p>

        <div class="bp-cover-script">
            <span class="bp-script-name" :style="{ color: coverTextColor }">{{ groomName }}</span>
            <span class="bp-script-amp" :style="{ color: coverTextColor }">&amp;</span>
            <span class="bp-script-name" :style="{ color: coverTextColor }">{{ brideName }}</span>
        </div>

        <span class="bp-cover-divider" :style="{ background: coverTextColor }"/>
        <p class="bp-cover-date" :style="{ color: coverTextColor }">{{ weddingDate }}</p>

        <img
            v-if="eiffelVisible"
            class="bp-cover-eiffel"
            src="/images/templates/belle-epoque/eiffel-front.webp"
            alt=""
            aria-hidden="true"
            loading="lazy"
        />

        <button class="bp-cover-cta" @click="$emit('open')">Ouvrir l'Invitation</button>
    </section>
</template>

<style scoped>
.bp-cover {
    position: fixed; inset: 0; z-index: 40;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 48px 24px;
    overflow: hidden;
    background: #3d3d3d;
    text-align: center;
}
.bp-cover-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.bp-cover-wash {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: cover;
    mix-blend-mode: multiply;
    opacity: 0.55;
    pointer-events: none;
}
.bp-cover-gradient {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(247,233,220,0.15) 0%, rgba(61,61,61,0.45) 100%);
}

.bp-cover-eyebrow {
    position: relative; z-index: 2;
    font-family: 'Italianno', cursive;
    font-size: 28px; line-height: 1;
    margin: 0 0 12px;
    text-shadow: 0 2px 6px rgba(184,134,11,0.4);
}
.bp-cover-script {
    position: relative; z-index: 2;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    margin: 0 0 16px;
}
.bp-script-name {
    font-family: 'Italianno', cursive;
    font-size: clamp(64px, 12vw, 140px);
    line-height: 1; font-weight: 400;
    text-shadow: 0 3px 12px rgba(0,0,0,0.35);
}
.bp-script-amp {
    font-family: 'Italianno', cursive;
    font-size: clamp(56px, 10vw, 110px);
    opacity: 0.85;
}
.bp-cover-divider {
    position: relative; z-index: 2;
    width: 60px; height: 1px;
    margin: 12px 0;
    opacity: 0.7;
    display: block;
}
.bp-cover-date {
    position: relative; z-index: 2;
    font-family: 'Cormorant SC', 'Cormorant Garamond', serif;
    font-size: 14px; letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0 0 24px;
    opacity: 0.92;
}
.bp-cover-eiffel {
    position: absolute; right: 16px; bottom: 96px;
    width: 90px; height: auto;
    opacity: 0.7;
    z-index: 1;
    pointer-events: none;
}
.bp-cover-cta {
    position: relative; z-index: 2;
    padding: 14px 36px;
    background: #d4a5a5;
    color: #fff;
    border: none; border-radius: 999px;
    font-family: 'Cormorant SC', serif;
    font-size: 13px; letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.2s ease;
    box-shadow: 0 6px 20px rgba(184,134,11,0.25);
}
.bp-cover-cta:hover  { background: #c08a8a; transform: translateY(-1px); }
.bp-cover-cta:active { transform: translateY(0); }

/* fade-in entry for script names (fallback per spec — no SVG draw in v1) */
.bp-script-name, .bp-script-amp, .bp-cover-eyebrow {
    opacity: 0;
    animation: bp-cover-rise 0.9s ease-out forwards;
}
.bp-cover-eyebrow     { animation-delay: 0.1s; }
.bp-script-name:nth-of-type(1) { animation-delay: 0.25s; }
.bp-script-amp        { animation-delay: 0.45s; }
.bp-script-name:nth-of-type(2) { animation-delay: 0.65s; }
@keyframes bp-cover-rise {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .bp-script-name, .bp-script-amp, .bp-cover-eyebrow {
        animation: none; opacity: 1; transform: none;
    }
    .bp-cover-cta { transition: none; }
}
</style>
