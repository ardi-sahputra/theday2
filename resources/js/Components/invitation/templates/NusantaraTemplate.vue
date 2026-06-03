<script setup>
import { onMounted } from 'vue';
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate';
import BatikKawung    from '@/Components/invitation/ornaments/BatikKawung.vue';
import SulurDivider   from '@/Components/invitation/ornaments/SulurDivider.vue';
import MandalaBg      from '@/Components/invitation/ornaments/MandalaBg.vue';
import JavaneseGate   from '@/Components/invitation/ornaments/JavaneseGate.vue';
import WayangBorder   from '@/Components/invitation/ornaments/WayangBorder.vue';
import GallerySection from '@/Components/invitation/sections/GallerySection.vue';

const SECTION_BG_DEFAULTS = {
    cover:   { type: 'color', value: '#2C1810' },
    opening: { type: 'color', value: '#1a1008' },
    events:  { type: 'color', value: '#f5efe8' },
    gallery: { type: 'color', value: '#f5efe8' },
    closing: { type: 'color', value: '#2C1810' },
}

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
});

const {
    primary, primaryLight, darkBg, bgColor, accent,
    fontTitle, fontHeading, fontBody,
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl, coverTextColor,
    details, events, galleries, galleryLayout,
    sectionEnabled, sectionData,
    openingText, closingText,
    firstEvent, firstEventDate,
    countdown, targetDate, pad,
    gateOpen, contentOpen, gateAnimating, triggerGate,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    videoEmbedUrl, vReveal,
    sectionBg, bgStyle,
} = useInvitationTemplate(props, {
    galleryLayout:     'grid',
    openingStyle:      'gate',
    revealClass:       'n-visible',
    sectionBgDefaults: SECTION_BG_DEFAULTS,
});

// Nusantara-specific: inject Google Fonts
onMounted(() => {
    const fontParam = [
        'Cinzel+Decorative:wght@400;700',
        'Cormorant+Garamond:ital,wght@0,400;0,600;1,400',
        'Crimson+Text:ital,wght@0,400;0,600;1,400',
    ].join('&family=');
    const link = Object.assign(document.createElement('link'), {
        rel:  'stylesheet',
        href: `https://fonts.googleapis.com/css2?family=${fontParam}&display=swap`,
    });
    document.head.appendChild(link);
});
</script>

<template>
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- NUSANTARA TEMPLATE                                                    -->
    <!-- Javanese Royal Wedding — Premium Tier                                 -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div class="n-root">

        <!-- Background audio -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            class="sr-only"
        />

        <!-- ══ Opening Gate Screen ════════════════════════════════════════ -->
        <Transition name="n-overlay">
            <div
                v-if="!gateOpen"
                class="n-opening"
                :style="{ background: darkBg }"
                @click="triggerGate"
            >
                <!-- Subtle kawung texture on dark bg -->
                <BatikKawung :color="primaryLight" :opacity="0.04"/>

                <!-- Left gate panel (decorative column) -->
                <div class="n-gate n-gate-left" :class="{ 'n-gate-opening': gateAnimating }">
                    <div class="n-gate-inner">
                        <!-- Column body with gold edge decoration -->
                        <div class="n-gate-column">
                            <!-- Top capital ornament -->
                            <svg viewBox="0 0 80 120" class="n-gate-capital" aria-hidden="true">
                                <defs>
                                    <linearGradient id="gc-l" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" :stop-color="primaryLight"/>
                                        <stop offset="100%" :stop-color="primary"/>
                                    </linearGradient>
                                </defs>
                                <!-- Tiered pyramid top -->
                                <polygon points="40,0 0,40 80,40" fill="url(#gc-l)"/>
                                <rect x="10" y="36" width="60" height="12" fill="url(#gc-l)" rx="1"/>
                                <rect x="20" y="44" width="40" height="10" fill="url(#gc-l)" rx="1"/>
                                <rect x="0"  y="50" width="80" height="14" fill="url(#gc-l)" rx="1"/>
                                <!-- Tier detail lines -->
                                <line x1="0" y1="50" x2="80" y2="50" :stroke="primaryLight" stroke-width="1.5" opacity="0.7"/>
                                <!-- Lotus at center -->
                                <ellipse cx="40" cy="42" rx="10" ry="5" :fill="primaryLight" opacity="0.5"/>
                                <!-- Column shaft -->
                                <rect x="28" y="64" width="24" height="56" fill="url(#gc-l)" rx="1"/>
                                <rect x="32" y="68" width="8"  height="48" :fill="darkBg" opacity="0.3" rx="1"/>
                                <!-- Molding bands on shaft -->
                                <rect x="28" y="80" width="24" height="3" :fill="primaryLight" opacity="0.8"/>
                                <rect x="28" y="95" width="24" height="3" :fill="primaryLight" opacity="0.8"/>
                                <rect x="28" y="108" width="24" height="3" :fill="primaryLight" opacity="0.8"/>
                            </svg>
                        </div>
                        <!-- Vertical gold edge line (inner edge, faces center) -->
                        <div class="n-gate-edge"/>
                        <!-- Diamond ornaments along the edge -->
                        <div class="n-gate-diamonds">
                            <div v-for="i in 5" :key="i" class="n-gate-diamond"/>
                        </div>
                    </div>
                </div>

                <!-- Right gate panel (mirror) -->
                <div class="n-gate n-gate-right" :class="{ 'n-gate-opening': gateAnimating }">
                    <div class="n-gate-inner n-gate-inner-right">
                        <div class="n-gate-edge n-gate-edge-right"/>
                        <div class="n-gate-diamonds n-gate-diamonds-right">
                            <div v-for="i in 5" :key="i" class="n-gate-diamond"/>
                        </div>
                        <div class="n-gate-column n-gate-column-right">
                            <svg viewBox="0 0 80 120" class="n-gate-capital" style="transform: scaleX(-1)" aria-hidden="true">
                                <defs>
                                    <linearGradient id="gc-r" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" :stop-color="primaryLight"/>
                                        <stop offset="100%" :stop-color="primary"/>
                                    </linearGradient>
                                </defs>
                                <polygon points="40,0 0,40 80,40" fill="url(#gc-r)"/>
                                <rect x="10" y="36" width="60" height="12" fill="url(#gc-r)" rx="1"/>
                                <rect x="20" y="44" width="40" height="10" fill="url(#gc-r)" rx="1"/>
                                <rect x="0"  y="50" width="80" height="14" fill="url(#gc-r)" rx="1"/>
                                <line x1="0" y1="50" x2="80" y2="50" :stroke="primaryLight" stroke-width="1.5" opacity="0.7"/>
                                <ellipse cx="40" cy="42" rx="10" ry="5" :fill="primaryLight" opacity="0.5"/>
                                <rect x="28" y="64" width="24" height="56" fill="url(#gc-r)" rx="1"/>
                                <rect x="32" y="68" width="8"  height="48" :fill="darkBg" opacity="0.3" rx="1"/>
                                <rect x="28" y="80" width="24" height="3" :fill="primaryLight" opacity="0.8"/>
                                <rect x="28" y="95" width="24" height="3" :fill="primaryLight" opacity="0.8"/>
                                <rect x="28" y="108" width="24" height="3" :fill="primaryLight" opacity="0.8"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Center content (always visible, between the two panels) -->
                <div class="n-opening-center" :class="{ 'n-opening-center-reveal': gateAnimating }">
                    <p class="n-arabic">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</p>
                    <p
                        class="n-subtitle-label"
                        :style="{ fontFamily: fontHeading, color: primaryLight }"
                    >
                        Undangan Pernikahan
                    </p>

                    <SulurDivider :color="primaryLight"/>

                    <h1 class="n-name-display shimmer-gold"
                        :style="{ fontFamily: fontTitle }">
                        {{ groomNick }}
                    </h1>
                    <p class="n-ampersand" :style="{ color: primaryLight, fontFamily: fontHeading }">
                        &amp;
                    </p>
                    <h1 class="n-name-display shimmer-gold"
                        :style="{ fontFamily: fontTitle }">
                        {{ brideNick }}
                    </h1>

                    <SulurDivider :color="primaryLight"/>

                    <p v-if="firstEventDate"
                       class="n-opening-date"
                       :style="{ fontFamily: fontHeading, color: primaryLight }">
                        {{ firstEventDate }}
                    </p>

                    <!-- Guest name -->
                    <div v-if="guest?.name"
                         class="n-guest-label"
                         :style="{ fontFamily: fontHeading, color: primaryLight + 'cc' }">
                        <span class="n-guest-kepada">Kepada Yth.</span>
                        <span class="n-guest-name">{{ guest.name }}</span>
                    </div>

                    <button
                        class="n-open-btn"
                        :style="{ borderColor: primaryLight, color: primaryLight, fontFamily: fontHeading }"
                        @click.stop="triggerGate"
                    >
                        {{ sectionData('cover').button_text || 'Buka Undangan' }}
                        <span class="n-open-arrow">↓</span>
                    </button>

                    <p class="n-opening-hint" :style="{ color: primaryLight + '70' }">
                        Ketuk di mana saja untuk membuka
                    </p>
                </div>
            </div>
        </Transition>

        <!-- ══ Floating music button ══════════════════════════════════════ -->
        <Transition name="n-fade">
            <button
                v-if="gateOpen && invitation.music?.file_url && sectionEnabled('music')"
                @click="toggleMusic"
                class="n-music-btn"
                :class="{ 'is-playing': musicPlaying }"
                :style="{ background: primary }"
                aria-label="Toggle musik"
            >
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 19V6l12-3v13"/>
                    <circle cx="6"  cy="18" r="3"/>
                    <circle cx="18" cy="15" r="3"/>
                </svg>
            </button>
        </Transition>

        <!-- ══ Main Scrolling Content ════════════════════════════════════ -->
        <div v-if="gateOpen" class="n-main">

            <!-- ── Section 1: Cover ─────────────────────────────────── -->
            <section
                class="n-cover"
                :class="{ 'n-cover--photo': coverPhotoUrl }"
                :style="coverPhotoUrl
                    ? { backgroundImage: `url(${coverPhotoUrl})`, backgroundSize: 'cover', backgroundPosition: 'center top' }
                    : { background: bgColor }"
            >
                <!-- Section background overlay (color or image from editor / defaults) -->
                <div
                    v-if="sectionBg('cover')"
                    class="absolute inset-0 pointer-events-none"
                    :style="bgStyle(sectionBg('cover'))"
                />

                <!-- Dark overlay when photo present for readability -->
                <div v-if="coverPhotoUrl" class="n-cover-photo-overlay" :style="{ background: darkBg }"/>

                <BatikKawung :color="primaryLight" :opacity="coverPhotoUrl ? 0.06 : 0.035"/>

                <!-- Ambient gradient overlay -->
                <div class="n-cover-gradient" :style="{ '--n-primary': primaryLight }"/>

                <!-- JavaneseGate frame with names -->
                <JavaneseGate :primary-color="coverTextColor" :light-color="primaryLight" class="n-gate-frame">
                    <div class="n-cover-names">
                        <p
                            class="n-cover-eyebrow"
                            :style="{ fontFamily: fontHeading, color: coverTextColor }"
                        >
                            The Wedding of
                        </p>

                        <h1 class="n-cover-name shimmer-gold"
                            :style="{ fontFamily: fontTitle }">
                            {{ groomNick }}
                        </h1>
                        <p class="n-cover-amp" :style="{ color: coverTextColor, fontFamily: fontHeading }">&amp;</p>
                        <h1 class="n-cover-name shimmer-gold"
                            :style="{ fontFamily: fontTitle }">
                            {{ brideNick }}
                        </h1>

                        <p v-if="firstEventDate"
                           class="n-cover-date"
                           :style="{ fontFamily: fontBody, color: coverTextColor + 'cc' }">
                            {{ firstEventDate }}
                        </p>
                    </div>
                </JavaneseGate>

                <!-- Photos (optional) -->
                <div
                    v-if="details.groom_photo_url || details.bride_photo_url"
                    class="n-cover-photos"
                    :use-directive="vReveal"
                >
                    <div v-if="details.groom_photo_url"
                         class="n-photo-frame"
                         :style="{ borderColor: primaryLight }">
                        <img :src="details.groom_photo_url" alt="" class="n-photo"/>
                    </div>
                    <div class="n-photo-sep">
                        <div class="n-photo-line" :style="{ background: primaryLight }"/>
                        <span :style="{ color: primaryLight, fontFamily: fontHeading }">&amp;</span>
                        <div class="n-photo-line" :style="{ background: primaryLight }"/>
                    </div>
                    <div v-if="details.bride_photo_url"
                         class="n-photo-frame"
                         :style="{ borderColor: primaryLight }">
                        <img :src="details.bride_photo_url" alt="" class="n-photo"/>
                    </div>
                </div>

                <!-- Scroll hint -->
                <div class="n-scroll-hint">
                    <span :style="{ color: primary + '80', fontFamily: fontBody, letterSpacing: '0.2em', fontSize: '10px', textTransform: 'uppercase' }">SCROLL</span>
                    <svg class="w-4 h-4 animate-bounce mt-1" :style="{ color: primary + '80' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </section>

            <!-- ══ Sections 2–9: revealed after "Buka Undangan" ═════════ -->
            <Transition name="n-sections">
            <div v-if="contentOpen">

            <!-- ── Section 2: Opening / Sambutan ───────────────────── -->
            <section class="n-section n-section-light" :style="{ background: bgColor }">
                <!-- Section background overlay from editor -->
                <div
                    v-if="sectionBg('opening')"
                    class="absolute inset-0 pointer-events-none"
                    :style="bgStyle(sectionBg('opening'))"
                />
                <BatikKawung :color="primary" :opacity="0.03"/>

                <!-- Corner sulur ornaments -->
                <div class="n-corner-ornament n-corner-tl" :style="{ color: primary + '60' }">
                    <svg viewBox="0 0 80 80" width="80" height="80" aria-hidden="true" fill="none">
                        <path d="M0,80 Q0,0 80,0" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        <path d="M20,80 Q20,20 80,20" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.6"/>
                        <circle cx="8" cy="8" r="5" fill="currentColor"/>
                        <circle cx="8" cy="8" r="2.5" fill="currentColor" opacity="0.5" style="fill: var(--n-bg, #F5F0E8)"/>
                    </svg>
                </div>
                <div class="n-corner-ornament n-corner-br" :style="{ color: primary + '60' }">
                    <svg viewBox="0 0 80 80" width="80" height="80" aria-hidden="true" fill="none">
                        <path d="M80,0 Q80,80 0,80" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        <path d="M60,0 Q60,60 0,60" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.6"/>
                        <circle cx="72" cy="72" r="5" fill="currentColor"/>
                        <circle cx="72" cy="72" r="2.5" fill="currentColor" opacity="0.5" style="fill: var(--n-bg, #F5F0E8)"/>
                    </svg>
                </div>

                <div class="n-section-inner">
                    <!-- Sulur divider top -->
                    <SulurDivider :color="primary"/>

                    <!-- Arabic greeting -->
                    <div :ref="el => vReveal(el)" class="n-reveal n-opening-block">
                        <p class="n-assalamu"
                           :style="{ fontFamily: fontHeading, color: primary }">
                            Assalamu'alaikum Warahmatullahi Wabarakatuh
                        </p>

                        <p class="n-opening-text"
                           :style="{ fontFamily: fontBody, color: darkBg }">
                            {{ openingText }}
                        </p>
                    </div>

                    <!-- Couple profiles -->
                    <div
                        :ref="el => vReveal(el)"
                        class="n-reveal n-couple-grid"
                    >
                        <!-- Groom card -->
                        <div class="n-couple-card">
                            <div v-if="details.groom_photo_url"
                                 class="n-couple-photo-wrap"
                                 :style="{ borderColor: primaryLight }">
                                <img :src="details.groom_photo_url" alt="" class="n-couple-photo"/>
                            </div>
                            <div v-else class="n-couple-avatar" :style="{ background: primary + '20', borderColor: primaryLight }">
                                <span :style="{ color: primary, fontFamily: fontTitle, fontSize: '32px' }">
                                    {{ groomName.charAt(0) }}
                                </span>
                            </div>
                            <h3 :style="{ fontFamily: fontTitle, color: darkBg }" class="n-couple-name">
                                {{ groomName }}
                            </h3>
                            <p class="n-couple-label" :style="{ fontFamily: fontBody, color: primary }">Putra dari</p>
                            <p class="n-couple-parents" :style="{ fontFamily: fontBody, color: darkBg + 'aa' }">
                                {{ details.groom_parent_names ?? '—' }}
                            </p>
                        </div>

                        <!-- Center "&" divider -->
                        <div class="n-couple-sep">
                            <div class="n-couple-sep-line" :style="{ background: `linear-gradient(to bottom, transparent, ${primaryLight}, transparent)` }"/>
                            <span :style="{ fontFamily: fontHeading, color: primaryLight }" class="n-couple-amp">&amp;</span>
                            <div class="n-couple-sep-line" :style="{ background: `linear-gradient(to bottom, transparent, ${primaryLight}, transparent)` }"/>
                        </div>

                        <!-- Bride card -->
                        <div class="n-couple-card">
                            <div v-if="details.bride_photo_url"
                                 class="n-couple-photo-wrap"
                                 :style="{ borderColor: primaryLight }">
                                <img :src="details.bride_photo_url" alt="" class="n-couple-photo"/>
                            </div>
                            <div v-else class="n-couple-avatar" :style="{ background: primary + '20', borderColor: primaryLight }">
                                <span :style="{ color: primary, fontFamily: fontTitle, fontSize: '32px' }">
                                    {{ brideName.charAt(0) }}
                                </span>
                            </div>
                            <h3 :style="{ fontFamily: fontTitle, color: darkBg }" class="n-couple-name">
                                {{ brideName }}
                            </h3>
                            <p class="n-couple-label" :style="{ fontFamily: fontBody, color: primary }">Putri dari</p>
                            <p class="n-couple-parents" :style="{ fontFamily: fontBody, color: darkBg + 'aa' }">
                                {{ details.bride_parent_names ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <SulurDivider :color="primary"/>
                </div>
            </section>

            <!-- ── Quote ──────────────────────────────────────────────── -->
            <section
                v-if="sectionEnabled('quote') && sectionData('quote').text"
                class="n-section n-section-light"
                :style="{ background: bgColor }"
            >
                <BatikKawung :color="primary" :opacity="0.025"/>
                <div class="n-section-inner">
                    <div :ref="el => vReveal(el)" class="n-reveal n-quote-block">
                        <div class="n-quote-mark" :style="{ color: primary }">&#10077;</div>
                        <p class="n-quote-text" :style="{ fontFamily: fontHeading, color: darkBg }">
                            {{ sectionData('quote').text }}
                        </p>
                        <div class="n-quote-line" :style="{ background: primary + '40' }"/>
                        <p v-if="sectionData('quote').source"
                           class="n-quote-source"
                           :style="{ fontFamily: fontBody, color: primary }">
                            — {{ sectionData('quote').source }}
                        </p>
                    </div>
                    <SulurDivider :color="primary"/>
                </div>
            </section>

            <!-- ── Section 3: Events (dark) ─────────────────────────── -->
            <section
                class="n-section n-section-dark"
                :style="{ background: darkBg }"
            >
                <!-- Section background overlay from editor -->
                <div
                    v-if="sectionBg('events')"
                    class="absolute inset-0 pointer-events-none"
                    :style="bgStyle(sectionBg('events'))"
                />
                <!-- Mandala background -->
                <div class="n-mandala-bg">
                    <MandalaBg :color="primaryLight" :opacity="0.05" size="600px"/>
                </div>

                <div class="n-section-inner">
                    <!-- Heading -->
                    <div :ref="el => vReveal(el)" class="n-reveal n-section-heading-block">
                        <SulurDivider :color="primaryLight"/>
                        <h2 class="n-section-heading"
                            :style="{ fontFamily: fontHeading, color: primaryLight }">
                            Waktu &amp; Tempat
                        </h2>
                        <p class="n-section-subheading"
                           :style="{ fontFamily: fontBody, color: primaryLight + '80' }">
                            Rangkaian Acara
                        </p>
                    </div>

                    <!-- Event cards -->
                    <div class="n-events-list">
                        <div
                            v-for="(event, i) in events"
                            :key="event.id"
                            :ref="el => vReveal(el)"
                            class="n-reveal n-event-card"
                            :style="{
                                borderColor: primaryLight + '40',
                                transitionDelay: `${i * 120}ms`,
                            }"
                        >
                            <!-- Gold top accent -->
                            <div class="n-event-accent" :style="{ background: `linear-gradient(90deg, transparent, ${primaryLight}, transparent)` }"/>

                            <!-- Event name -->
                            <h3 class="n-event-name"
                                :style="{ fontFamily: fontTitle, color: primaryLight }">
                                {{ event.event_name }}
                            </h3>

                            <!-- Date -->
                            <div class="n-event-row">
                                <div class="n-event-icon" :style="{ background: primaryLight + '20', color: primaryLight }">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p :style="{ fontFamily: fontBody, color: bgColor }">{{ event.event_date_formatted }}</p>
                                    <p v-if="event.start_time"
                                       :style="{ fontFamily: fontBody, color: bgColor + '80', fontSize: '13px' }">
                                        {{ event.start_time }}{{ event.end_time ? ` – ${event.end_time}` : '' }} WIB
                                    </p>
                                </div>
                            </div>

                            <!-- Divider line -->
                            <div class="n-event-divider" :style="{ background: primaryLight + '25' }"/>

                            <!-- Venue -->
                            <div class="n-event-row">
                                <div class="n-event-icon" :style="{ background: primaryLight + '20', color: primaryLight }">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p :style="{ fontFamily: fontBody, color: bgColor, fontWeight: 600 }">{{ event.venue_name }}</p>
                                    <p v-if="event.venue_address"
                                       :style="{ fontFamily: fontBody, color: bgColor + '70', fontSize: '12px', lineHeight: '1.5' }">
                                        {{ event.venue_address }}
                                    </p>
                                </div>
                            </div>

                            <!-- Maps button -->
                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="n-maps-btn"
                                :style="{ borderColor: primaryLight + '60', color: primaryLight, fontFamily: fontBody }"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                Lihat Peta
                            </a>
                        </div>
                    </div>

                    <!-- Sulur between events and countdown -->
                    <SulurDivider :color="primaryLight"/>

                    <!-- Countdown -->
                    <div v-if="targetDate && sectionEnabled('countdown')" :ref="el => vReveal(el)" class="n-reveal n-countdown-block">
                        <p class="n-countdown-label"
                           :style="{ fontFamily: fontHeading, color: primaryLight + 'cc', fontStyle: 'italic' }">
                            Menghitung Hari
                        </p>
                        <div class="n-countdown-grid">
                            <div
                                v-for="({ val, label }) in [
                                    { val: countdown.days,    label: 'Hari'  },
                                    { val: countdown.hours,   label: 'Jam'   },
                                    { val: countdown.minutes, label: 'Menit' },
                                    { val: countdown.seconds, label: 'Detik' },
                                ]"
                                :key="label"
                                class="n-countdown-cell"
                                :style="{ borderColor: primaryLight + '40', background: primaryLight + '10' }"
                            >
                                <!-- Decorative corner marks -->
                                <span class="n-cd-corner n-cd-tl" :style="{ borderColor: primaryLight }"/>
                                <span class="n-cd-corner n-cd-tr" :style="{ borderColor: primaryLight }"/>
                                <span class="n-cd-corner n-cd-bl" :style="{ borderColor: primaryLight }"/>
                                <span class="n-cd-corner n-cd-br" :style="{ borderColor: primaryLight }"/>

                                <p class="n-cd-number" :style="{ fontFamily: fontTitle, color: primaryLight }">
                                    {{ pad(val) }}
                                </p>
                                <p class="n-cd-unit"   :style="{ fontFamily: fontBody, color: primaryLight + 'aa' }">
                                    {{ label }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Live Streaming ─────────────────────────────── -->
                    <template v-if="sectionEnabled('live_streaming') && sectionData('live_streaming').url">
                        <SulurDivider :color="primaryLight"/>
                        <div :ref="el => vReveal(el)" class="n-reveal n-livestream-block">
                            <div class="n-livestream-icon" :style="{ color: primaryLight }">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="n-livestream-heading"
                                :style="{ fontFamily: fontHeading, color: primaryLight }">
                                Live Streaming
                            </h3>
                            <p v-if="sectionData('live_streaming').platform"
                               class="n-livestream-platform"
                               :style="{ fontFamily: fontBody, color: primaryLight + '80' }">
                                via {{ sectionData('live_streaming').platform }}
                            </p>
                            <a
                                :href="sectionData('live_streaming').url"
                                target="_blank" rel="noopener noreferrer"
                                class="n-livestream-btn"
                                :style="{ borderColor: primaryLight + '60', color: primaryLight, fontFamily: fontHeading }"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Saksikan Live
                            </a>
                        </div>
                    </template>

                    <!-- ── Additional Info ────────────────────────────── -->
                    <template v-if="sectionEnabled('additional_info') && sectionData('additional_info').text">
                        <SulurDivider :color="primaryLight"/>
                        <div :ref="el => vReveal(el)" class="n-reveal n-additional-block">
                            <p class="n-additional-text"
                               :style="{ fontFamily: fontBody, color: bgColor + 'cc' }">
                                {{ sectionData('additional_info').text }}
                            </p>
                        </div>
                    </template>

                </div>
            </section>

            <!-- ── Section 4: Gallery ────────────────────────────────── -->
            <section
                v-if="galleries.length && sectionEnabled('gallery')"
                class="n-section n-section-light n-gallery-section"
                :style="{ background: bgColor }"
            >
                <!-- Section background overlay from editor -->
                <div
                    v-if="sectionBg('gallery')"
                    class="absolute inset-0 pointer-events-none"
                    :style="bgStyle(sectionBg('gallery'))"
                />
                <!-- WayangBorder frame around the content -->
                <div class="n-wayang-frame">
                    <WayangBorder :primary-color="primary" :light-color="primaryLight"/>
                </div>

                <div class="n-section-inner">
                    <div :ref="el => vReveal(el)" class="n-reveal n-section-heading-block">
                        <SulurDivider :color="primary"/>
                        <h2 class="n-section-heading"
                            :style="{ fontFamily: fontHeading, color: darkBg }">
                            Galeri Kami
                        </h2>
                    </div>

                    <GallerySection
                        :galleries="galleries"
                        :layout="galleryLayout"
                        :primary-color="'#C9A84C'"
                    />

                    <SulurDivider :color="primary"/>
                </div>
            </section>

            <!-- ── Love Story ─────────────────────────────────────────── -->
            <section
                v-if="sectionEnabled('love_story') && sectionData('love_story').stories?.length"
                class="n-section n-section-light"
                :style="{ background: bgColor }"
            >
                <BatikKawung :color="primary" :opacity="0.025"/>
                <div class="n-section-inner">
                    <div :ref="el => vReveal(el)" class="n-reveal n-section-heading-block">
                        <SulurDivider :color="primary"/>
                        <h2 class="n-section-heading"
                            :style="{ fontFamily: fontHeading, color: darkBg }">
                            Kisah Cinta Kami
                        </h2>
                        <p class="n-section-subheading"
                           :style="{ fontFamily: fontBody, color: primary + 'aa' }">
                            Perjalanan menuju hari istimewa
                        </p>
                    </div>

                    <div class="n-timeline">
                        <div
                            v-for="(story, i) in sectionData('love_story').stories"
                            :key="i"
                            :ref="el => vReveal(el)"
                            class="n-reveal n-timeline-item"
                            :style="{ transitionDelay: `${i * 120}ms` }"
                        >
                            <div class="n-timeline-dot" :style="{ background: primary, boxShadow: `0 0 0 4px ${bgColor}, 0 0 0 5px ${primary}40` }"/>
                            <div class="n-timeline-body">
                                <span class="n-timeline-year" :style="{ fontFamily: fontTitle, color: primary }">
                                    {{ story.year }}
                                </span>
                                <h3 class="n-timeline-title" :style="{ fontFamily: fontHeading, color: darkBg }">
                                    {{ story.title }}
                                </h3>
                                <p class="n-timeline-desc" :style="{ fontFamily: fontBody, color: darkBg + 'aa' }">
                                    {{ story.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <SulurDivider :color="primary"/>
                </div>
            </section>

            <!-- ── Video ──────────────────────────────────────────────── -->
            <section
                v-if="sectionEnabled('video') && sectionData('video').url"
                class="n-section n-section-light"
                :style="{ background: bgColor }"
            >
                <BatikKawung :color="primary" :opacity="0.025"/>
                <div class="n-section-inner">
                    <div :ref="el => vReveal(el)" class="n-reveal n-section-heading-block">
                        <SulurDivider :color="primary"/>
                        <h2 class="n-section-heading"
                            :style="{ fontFamily: fontHeading, color: darkBg }">
                            Video Kami
                        </h2>
                    </div>

                    <!-- YouTube / Vimeo embed -->
                    <div v-if="videoEmbedUrl(sectionData('video').url)"
                         :ref="el => vReveal(el)"
                         class="n-reveal n-video-wrap">
                        <div class="n-video-frame" :style="{ borderColor: primary + '40' }">
                            <iframe
                                :src="videoEmbedUrl(sectionData('video').url)"
                                class="n-video-iframe"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            />
                        </div>
                    </div>
                    <!-- Fallback link for other URLs -->
                    <div v-else :ref="el => vReveal(el)" class="n-reveal n-video-fallback">
                        <a
                            :href="sectionData('video').url"
                            target="_blank" rel="noopener noreferrer"
                            class="n-video-link"
                            :style="{ borderColor: primary + '60', color: primary, fontFamily: fontHeading }"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Tonton Video
                        </a>
                    </div>

                    <p v-if="sectionData('video').caption"
                       class="n-video-caption"
                       :style="{ fontFamily: fontBody, color: darkBg + '70' }">
                        {{ sectionData('video').caption }}
                    </p>

                    <SulurDivider :color="primary"/>
                </div>
            </section>

            <!-- ── Section 5: RSVP (dark) ────────────────────────────── -->
            <section
                v-if="sectionEnabled('rsvp')"
                class="n-section n-section-dark"
                :style="{ background: darkBg }"
            >
                <!-- Kawung texture -->
                <BatikKawung :color="primaryLight" :opacity="0.04"/>

                <div class="n-section-inner">
                    <div :ref="el => vReveal(el)" class="n-reveal n-section-heading-block">
                        <SulurDivider :color="primaryLight"/>
                        <h2 class="n-section-heading"
                            :style="{ fontFamily: fontHeading, color: primaryLight }">
                            Konfirmasi Kehadiran
                        </h2>
                        <p class="n-section-subheading"
                           :style="{ fontFamily: fontBody, color: primaryLight + '80' }">
                            Kehadiran Anda adalah kehormatan bagi kami
                        </p>
                    </div>

                    <!-- RSVP Form -->
                    <div :ref="el => vReveal(el)" class="n-reveal n-rsvp-form-wrap">

                        <!-- Demo notice -->
                        <div v-if="isDemo" class="n-demo-notice"
                             :style="{ borderColor: primaryLight + '40', color: primaryLight + 'aa', fontFamily: fontBody }">
                            Form RSVP tidak aktif di halaman demo
                        </div>

                        <!-- Success state -->
                        <div v-else-if="rsvpSuccess" class="n-success-msg"
                             :style="{ borderColor: primaryLight, color: primaryLight, fontFamily: fontHeading }">
                            <svg class="w-8 h-8 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terima kasih! RSVP Anda telah diterima.
                        </div>

                        <!-- Form fields -->
                        <form v-else class="n-rsvp-form" @submit.prevent="submitRsvp">
                            <div class="n-form-field">
                                <label :style="{ color: primaryLight + 'aa', fontFamily: fontBody }">Nama Lengkap</label>
                                <input
                                    v-model="rsvpForm.guest_name"
                                    type="text"
                                    placeholder="Nama Anda"
                                    class="n-input"
                                    :style="{
                                        borderColor: primaryLight + '50',
                                        color: bgColor,
                                        fontFamily: fontBody,
                                        caretColor: primaryLight,
                                    }"
                                />
                            </div>

                            <div class="n-form-field">
                                <label :style="{ color: primaryLight + 'aa', fontFamily: fontBody }">Kehadiran</label>
                                <div class="n-radio-group">
                                    <label
                                        v-for="opt in [
                                            { val: 'hadir',       label: 'Hadir'        },
                                            { val: 'tidak_hadir', label: 'Tidak Hadir'  },
                                            { val: 'ragu',        label: 'Belum Pasti'  },
                                        ]"
                                        :key="opt.val"
                                        class="n-radio-option"
                                        :class="{ 'is-selected': rsvpForm.attendance === opt.val }"
                                        :style="{
                                            borderColor: rsvpForm.attendance === opt.val ? primaryLight : primaryLight + '30',
                                            background:  rsvpForm.attendance === opt.val ? primaryLight + '15' : 'transparent',
                                            color: rsvpForm.attendance === opt.val ? primaryLight : primaryLight + '70',
                                            fontFamily: fontBody,
                                        }"
                                    >
                                        <input type="radio" :value="opt.val" v-model="rsvpForm.attendance" class="sr-only"/>
                                        {{ opt.label }}
                                    </label>
                                </div>
                            </div>

                            <div class="n-form-field" v-if="rsvpForm.attendance === 'hadir'">
                                <label :style="{ color: primaryLight + 'aa', fontFamily: fontBody }">Jumlah Tamu</label>
                                <input
                                    v-model.number="rsvpForm.guest_count"
                                    type="number"
                                    min="1" max="20"
                                    class="n-input"
                                    :style="{ borderColor: primaryLight + '50', color: bgColor, fontFamily: fontBody }"
                                />
                            </div>

                            <div class="n-form-field">
                                <label :style="{ color: primaryLight + 'aa', fontFamily: fontBody }">Ucapan (opsional)</label>
                                <textarea
                                    v-model="rsvpForm.notes"
                                    placeholder="Doa & ucapan untuk mempelai..."
                                    rows="3"
                                    class="n-input n-textarea"
                                    :style="{ borderColor: primaryLight + '50', color: bgColor, fontFamily: fontBody }"
                                />
                            </div>

                            <p v-if="rsvpError" class="n-form-error" :style="{ fontFamily: fontBody }">
                                {{ rsvpError }}
                            </p>

                            <button
                                type="submit"
                                class="n-submit-btn"
                                :disabled="rsvpSubmitting"
                                :style="{
                                    background: `linear-gradient(135deg, ${primary}, ${primaryLight})`,
                                    color: darkBg,
                                    fontFamily: fontHeading,
                                }"
                            >
                                {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi' }}
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- ── Gift / Amplop Digital ──────────────────────────────── -->
            <section
                v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                class="n-section n-section-dark"
                :style="{ background: darkBg }"
            >
                <BatikKawung :color="primaryLight" :opacity="0.04"/>
                <div class="n-section-inner">
                    <div :ref="el => vReveal(el)" class="n-reveal n-section-heading-block">
                        <SulurDivider :color="primaryLight"/>
                        <h2 class="n-section-heading"
                            :style="{ fontFamily: fontHeading, color: primaryLight }">
                            Hadiah &amp; Amplop Digital
                        </h2>
                        <p class="n-section-subheading"
                           :style="{ fontFamily: fontBody, color: primaryLight + '80' }">
                            Doa tulus lebih berarti dari apapun
                        </p>
                    </div>

                    <div class="n-gift-list">
                        <div
                            v-for="(account, i) in sectionData('gift').accounts"
                            :key="i"
                            :ref="el => vReveal(el)"
                            class="n-reveal n-gift-card"
                            :style="{
                                borderColor: primaryLight + '40',
                                transitionDelay: `${i * 120}ms`,
                            }"
                        >
                            <div class="n-gift-accent"
                                 :style="{ background: `linear-gradient(90deg, transparent, ${primaryLight}, transparent)` }"/>
                            <p class="n-gift-bank"
                               :style="{ fontFamily: fontTitle, color: primaryLight }">
                                {{ account.bank }}
                            </p>
                            <p class="n-gift-owner"
                               :style="{ fontFamily: fontBody, color: bgColor + '80' }">
                                {{ account.name }}
                            </p>
                            <div class="n-gift-number-row">
                                <p class="n-gift-number"
                                   :style="{ fontFamily: fontHeading, color: bgColor }">
                                    {{ account.number }}
                                </p>
                                <button
                                    @click="copyToClipboard(account.number)"
                                    class="n-gift-copy"
                                    :class="{ 'is-copied': copiedAccount === account.number }"
                                    :style="{
                                        borderColor: primaryLight + '50',
                                        color: copiedAccount === account.number ? primaryLight : primaryLight + '60',
                                        fontFamily: fontBody,
                                    }"
                                >
                                    {{ copiedAccount === account.number ? '✓ Disalin' : 'Salin' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <SulurDivider :color="primaryLight"/>
                </div>
            </section>

            <!-- ── Section 6: Messages ───────────────────────────────── -->
            <section
                v-if="sectionEnabled('wishes')"
                class="n-section n-section-light"
                :style="{ background: bgColor }"
            >
                <BatikKawung :color="primary" :opacity="0.025"/>
                <div class="n-section-inner">
                    <div :ref="el => vReveal(el)" class="n-reveal n-section-heading-block">
                        <SulurDivider :color="primary"/>
                        <h2 class="n-section-heading"
                            :style="{ fontFamily: fontHeading, color: darkBg }">
                            Doa &amp; Ucapan
                        </h2>
                    </div>

                    <!-- Message input -->
                    <div v-if="!isDemo" :ref="el => vReveal(el)" class="n-reveal n-msg-input-block">
                        <form class="n-msg-form" @submit.prevent="submitMessage">
                            <input
                                v-model="msgForm.name"
                                type="text"
                                placeholder="Nama Anda"
                                class="n-input-light"
                                :style="{ borderColor: primary + '50', color: darkBg, fontFamily: fontBody }"
                            />
                            <textarea
                                v-model="msgForm.message"
                                placeholder="Tulis ucapan dan doa terbaik..."
                                rows="3"
                                class="n-input-light n-textarea"
                                :style="{ borderColor: primary + '50', color: darkBg, fontFamily: fontBody }"
                            />
                            <p v-if="msgError" class="n-form-error-light" :style="{ fontFamily: fontBody }">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="n-form-success-light"
                               :style="{ fontFamily: fontBody, color: primary }">
                                Ucapan berhasil dikirim ✓
                            </p>
                            <button
                                type="submit"
                                class="n-submit-btn-light"
                                :disabled="msgSubmitting"
                                :style="{
                                    background: `linear-gradient(135deg, ${primary}, ${primaryLight})`,
                                    color: bgColor,
                                    fontFamily: fontHeading,
                                }"
                            >
                                {{ msgSubmitting ? 'Mengirim...' : 'Kirim Ucapan' }}
                            </button>
                        </form>
                    </div>

                    <!-- Messages list -->
                    <div class="n-messages-list" :style="{ '--n-primary': primary, '--n-heading': fontHeading, '--n-body': fontBody }">
                        <TransitionGroup name="n-msg">
                            <div
                                v-for="msg in localMessages"
                                :key="msg.id"
                                class="n-message-card"
                                :style="{ borderLeftColor: primaryLight }"
                            >
                                <p class="n-msg-name" :style="{ fontFamily: fontHeading, color: darkBg }">
                                    {{ msg.name }}
                                </p>
                                <p class="n-msg-text" :style="{ fontFamily: fontBody, color: darkBg + 'cc' }">
                                    "{{ msg.message }}"
                                </p>
                                <p class="n-msg-time" :style="{ color: primary + '80' }">{{ msg.created_at }}</p>
                            </div>
                        </TransitionGroup>
                        <p v-if="!localMessages.length"
                           class="n-msg-empty"
                           :style="{ fontFamily: fontBody, color: darkBg + '50' }">
                            Belum ada ucapan. Jadilah yang pertama! 
                        </p>
                    </div>

                    <SulurDivider :color="primary"/>
                </div>
            </section>

            <!-- ── Section 7: Closing (dark) ────────────────────────── -->
            <section
                class="n-section n-section-dark n-closing"
                :style="{ background: darkBg }"
            >
                <!-- Section background overlay from editor -->
                <div
                    v-if="sectionBg('closing')"
                    class="absolute inset-0 pointer-events-none"
                    :style="bgStyle(sectionBg('closing'))"
                />
                <!-- Full mandala behind content -->
                <div class="n-closing-mandala">
                    <MandalaBg :color="primaryLight" :opacity="0.06" size="500px"/>
                </div>

                <div class="n-section-inner">
                    <div :ref="el => vReveal(el)" class="n-reveal n-closing-content">
                        <SulurDivider :color="primaryLight"/>

                        <p class="n-closing-text"
                           :style="{ fontFamily: fontBody, color: bgColor + 'cc' }">
                            {{ closingText }}
                        </p>

                        <div class="n-closing-signoff">
                            <p class="n-closing-label"
                               :style="{ fontFamily: fontBody, color: primaryLight + '80', letterSpacing: '0.2em', fontSize: '11px' }">
                                DENGAN CINTA,
                            </p>
                            <h2 class="n-closing-names shimmer-gold"
                                :style="{ fontFamily: fontTitle }">
                                {{ groomNick }} &amp; {{ brideNick }}
                            </h2>
                        </div>

                        <p class="n-wassalam"
                           :style="{ fontFamily: fontHeading, color: primaryLight + 'cc', fontStyle: 'italic' }">
                            Wassalamu'alaikum Warahmatullahi Wabarakatuh
                        </p>

                        <SulurDivider :color="primaryLight"/>

                        <!-- Footer -->
                        <p class="n-footer" :style="{ color: primaryLight + '40', fontFamily: fontBody }">
                            Undangan ini dibuat dengan penuh cinta menggunakan
                            <span :style="{ color: primaryLight + '60' }">Theday</span>
                        </p>
                    </div>
                </div>
            </section>

            </div><!-- /contentOpen wrapper -->
            </Transition>

        </div><!-- /n-main -->

    <!-- ══ Toast ══════════════════════════════════════════════════════════════ -->
    <Transition name="n-toast">
        <div v-if="toastVisible" class="n-toast" :style="{ background: darkBg, borderColor: primaryLight, color: primaryLight, fontFamily: fontBody }">
            {{ toastMsg }}
        </div>
    </Transition>

    </div><!-- /n-root -->
</template>

<style scoped>
/* ─── CSS variable bridge (Vue v-bind → CSS custom props) ──────────────────── */
.n-root {
    --n-primary:      v-bind(primary);
    --n-gold:         v-bind(primaryLight);
    --n-dark:         v-bind(darkBg);
    --n-bg:           v-bind(bgColor);
    --n-accent:       v-bind(accent);
    --n-title:        v-bind(fontTitle);
    --n-heading:      v-bind(fontHeading);
    --n-body:         v-bind(fontBody);
    font-family: var(--n-body), Georgia, serif;
}

/* ─── Overlay transitions ──────────────────────────────────────────────────── */
.n-overlay-leave-active { transition: opacity 0.8s ease; }
.n-overlay-leave-to     { opacity: 0; }
.n-fade-enter-active    { transition: opacity 0.6s ease 1s; }
.n-fade-enter-from      { opacity: 0; }

/* ─── Opening screen ───────────────────────────────────────────────────────── */
.n-opening {
    position: fixed;
    inset: 0;
    z-index: 50;
    overflow: hidden;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Gate panels */
.n-gate {
    position: absolute;
    top: 0;
    width: clamp(80px, 22vw, 200px);
    height: 100%;
    transition: transform 1.5s cubic-bezier(0.76, 0, 0.24, 1);
    will-change: transform;
    z-index: 2;
}
.n-gate-left  { left: 0; }
.n-gate-right { right: 0; }
.n-gate-left.n-gate-opening  { transform: translateX(-110%); }
.n-gate-right.n-gate-opening { transform: translateX(110%); }

.n-gate-inner {
    position: relative;
    width: 100%;
    height: 100%;
    background: var(--n-dark);
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
.n-gate-inner-right {
    align-items: flex-start;
}

/* Decorative column within gate panel */
.n-gate-column {
    position: absolute;
    top: 32px;
    right: 8px;
    width: 80px;
    height: 120px;
}
.n-gate-column-right {
    right: auto;
    left: 8px;
}
.n-gate-capital {
    width: 100%;
    height: auto;
}

/* Gold inner edge line */
.n-gate-edge {
    position: absolute;
    top: 0;
    right: 0;
    width: 2px;
    height: 100%;
    background: linear-gradient(to bottom, transparent, var(--n-gold), var(--n-gold), transparent);
    box-shadow: 0 0 12px var(--n-gold);
}
.n-gate-edge-right {
    right: auto;
    left: 0;
}

/* Diamond ornaments along the edge */
.n-gate-diamonds {
    position: absolute;
    top: 0;
    right: -5px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    align-items: center;
    padding: 60px 0;
}
.n-gate-diamonds-right {
    right: auto;
    left: -5px;
}
.n-gate-diamond {
    width: 10px;
    height: 10px;
    background: var(--n-gold);
    transform: rotate(45deg);
    box-shadow: 0 0 6px var(--n-gold);
}

/* Opening center content */
.n-opening-center {
    position: relative;
    z-index: 3;
    text-align: center;
    padding: 32px 24px;
    max-width: 320px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    transition: opacity 0.8s ease, transform 0.8s ease;
}
.n-opening-center-reveal {
    opacity: 0.6;
    transform: scale(1.04);
}

.n-arabic {
    color: var(--n-gold);
    font-size: 18px;
    margin-bottom: 4px;
    letter-spacing: 0.05em;
    direction: rtl;
}
.n-subtitle-label {
    font-size: 11px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.n-name-display {
    font-size: clamp(22px, 6vw, 32px);
    line-height: 1.15;
    font-weight: 400;
    margin: 4px 0;
    letter-spacing: 0.04em;
}
.n-ampersand {
    font-size: 28px;
    font-weight: 300;
    margin: 2px 0;
}
.n-bday-age {
    font-size: 18px;
    font-weight: 300;
    margin-top: 4px;
}
.n-opening-date {
    font-size: 13px;
    letter-spacing: 0.1em;
    margin-top: 4px;
}
.n-open-btn {
    margin-top: 20px;
    padding: 12px 28px;
    border: 1px solid;
    background: transparent;
    font-size: 13px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.3s, color 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.n-open-btn:hover {
    background: var(--n-gold);
    color: var(--n-dark) !important;
}
.n-open-arrow {
    animation: bounce 1.5s ease infinite;
}
.n-opening-hint {
    font-size: 11px;
    margin-top: 12px;
    letter-spacing: 0.1em;
}

.n-guest-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    margin-bottom: 16px;
    opacity: 0.9;
}
.n-guest-kepada {
    font-size: 11px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    opacity: 0.7;
}
.n-guest-name {
    font-size: 17px;
    font-style: italic;
    letter-spacing: 0.04em;
}

/* ─── Music button ─────────────────────────────────────────────────────────── */
.n-music-btn {
    position: fixed;
    bottom: 24px;
    right: 16px;
    z-index: 40;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    cursor: pointer;
    transition: transform 0.3s;
}
.n-music-btn.is-playing {
    animation: spin-slow 4s linear infinite;
}
@keyframes spin-slow { to { transform: rotate(360deg); } }

/* ─── Main content ─────────────────────────────────────────────────────────── */
.n-main { overflow-x: hidden; }

/* ─── Sections ─────────────────────────────────────────────────────────────── */
.n-section { position: relative; overflow: hidden; }
.n-section-inner {
    position: relative;
    z-index: 1;
    max-width: 680px;
    margin: 0 auto;
    padding: 72px 24px;
}

/* ─── Reveal animation ─────────────────────────────────────────────────────── */
.n-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.8s ease, transform 0.8s ease;
}
.n-visible { opacity: 1; transform: translateY(0); }

/* ─── Gold shimmer text ────────────────────────────────────────────────────── */
.shimmer-gold {
    background: linear-gradient(90deg,
        var(--n-primary)   0%,
        var(--n-gold)     30%,
        #FFD470            50%,
        var(--n-gold)     70%,
        var(--n-primary)  100%
    );
    background-size: 200% auto;
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 4s linear infinite;
}
@keyframes shimmer {
    0%   { background-position: 0%   center; }
    100% { background-position: 200% center; }
}

/* ─── Cover section ────────────────────────────────────────────────────────── */
.n-cover {
    min-height: 100svh;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 64px 24px 32px;
    position: relative;
}
.n-cover-photo-overlay {
    position: absolute;
    inset: 0;
    opacity: 0.52;
    pointer-events: none;
    z-index: 0;
}
.n-cover-gradient {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse at 30% 20%, var(--n-primary, #8B6914) 0%, transparent 60%),
        radial-gradient(ellipse at 70% 80%, var(--n-primary, #8B6914) 0%, transparent 60%);
    opacity: 0.08;
    pointer-events: none;
}
.n-gate-frame {
    margin: 0 auto;
    max-width: 380px;
    width: 100%;
}
.n-cover-names {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 16px 8px;
}
.n-cover-eyebrow {
    font-size: 10px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    font-style: italic;
    margin-bottom: 8px;
}
.n-cover-name {
    font-size: clamp(24px, 7vw, 36px);
    line-height: 1.15;
    font-weight: 400;
    letter-spacing: 0.04em;
}
.n-cover-amp {
    font-size: 32px;
    font-weight: 300;
    margin: 4px 0;
}
.n-cover-age {
    font-size: 18px;
    margin-top: 4px;
}
.n-cover-date {
    font-size: 12px;
    letter-spacing: 0.15em;
    margin-top: 12px;
    text-transform: uppercase;
}
.n-cover-photos {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 32px;
}
.n-photo-frame {
    width: 108px;
    height: 136px;
    border-radius: 12px;
    border-width: 3px;
    border-style: solid;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}
.n-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top;
}
.n-photo-sep {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    font-size: 24px;
    font-weight: 300;
}
.n-photo-line { width: 1px; height: 40px; }
.n-scroll-hint {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding-bottom: 24px;
}

/* ─── Opening section ──────────────────────────────────────────────────────── */
.n-opening-block {
    text-align: center;
    max-width: 480px;
    margin: 0 auto;
}
.n-assalamu {
    font-size: 15px;
    font-style: italic;
    margin-bottom: 20px;
}
.n-opening-text {
    font-size: 15px;
    line-height: 1.9;
    font-style: italic;
    color: var(--n-dark);
}
.n-couple-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 24px;
    align-items: center;
    margin: 32px 0;
}
@media (max-width: 480px) {
    .n-couple-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .n-couple-sep { flex-direction: row; padding: 0; }
    .n-couple-sep-line { height: 1px; width: 40px; }
}
.n-couple-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
}
.n-couple-photo-wrap {
    width: 120px;
    height: 150px;
    border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
    border-width: 3px;
    border-style: solid;
    overflow: hidden;
}
.n-couple-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top;
}
.n-couple-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border-width: 2px;
    border-style: solid;
    display: flex;
    align-items: center;
    justify-content: center;
}
.n-couple-sep {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 0;
}
.n-couple-sep-line { width: 1px; height: 60px; }
.n-couple-amp { font-size: 36px; font-weight: 300; }
.n-couple-name { font-size: 20px; font-weight: 400; margin: 0; }
.n-couple-label { font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase; }
.n-couple-parents { font-size: 13px; line-height: 1.6; }

/* ─── Corner ornaments ─────────────────────────────────────────────────────── */
.n-corner-ornament {
    position: absolute;
    pointer-events: none;
}
.n-corner-tl { top: 0; left: 0; }
.n-corner-br { bottom: 0; right: 0; transform: rotate(180deg); }

/* ─── Events section ───────────────────────────────────────────────────────── */
.n-mandala-bg {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
}
.n-section-heading-block { text-align: center; margin-bottom: 8px; }
.n-section-heading {
    font-size: clamp(24px, 5vw, 34px);
    font-weight: 600;
    margin: 12px 0 4px;
    letter-spacing: 0.02em;
}
.n-section-subheading {
    font-size: 13px;
    font-style: italic;
    margin-top: 4px;
}
.n-events-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin: 32px 0;
}
.n-event-card {
    border: 1px solid;
    border-radius: 4px;
    padding: 0;
    overflow: hidden;
    position: relative;
    background: rgba(255,255,255,0.03);
}
.n-event-accent {
    height: 2px;
    width: 100%;
}
.n-event-name {
    font-size: 20px;
    font-weight: 400;
    letter-spacing: 0.06em;
    padding: 16px 20px 8px;
    margin: 0;
}
.n-event-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 6px 20px;
}
.n-event-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.n-event-divider {
    height: 1px;
    margin: 8px 20px;
}
.n-maps-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin: 12px 20px 16px;
    padding: 10px;
    border: 1px solid;
    border-radius: 4px;
    font-size: 13px;
    letter-spacing: 0.08em;
    text-decoration: none;
    transition: background 0.3s;
}
.n-maps-btn:hover {
    background: rgba(201, 168, 76, 0.1);
}

/* ─── Countdown ────────────────────────────────────────────────────────────── */
.n-countdown-block { text-align: center; }
.n-countdown-label { font-size: 18px; margin-bottom: 20px; }
.n-countdown-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    max-width: 400px;
    margin: 0 auto;
}
.n-countdown-cell {
    position: relative;
    border: 1px solid;
    border-radius: 4px;
    padding: 16px 4px;
    text-align: center;
}
/* Decorative corner marks on countdown cells */
.n-cd-corner {
    position: absolute;
    width: 8px;
    height: 8px;
    border-style: solid;
    border-color: inherit;
}
.n-cd-tl { top: -1px;  left: -1px;  border-width: 2px 0 0 2px; }
.n-cd-tr { top: -1px;  right: -1px; border-width: 2px 2px 0 0; }
.n-cd-bl { bottom: -1px; left: -1px;  border-width: 0 0 2px 2px; }
.n-cd-br { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }
.n-cd-number { font-size: clamp(24px, 6vw, 36px); font-weight: 400; line-height: 1; margin: 0; }
.n-cd-unit   { font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; margin: 4px 0 0; }

/* ─── Gallery section ──────────────────────────────────────────────────────── */
.n-gallery-section .n-section-inner { padding-top: 60px; padding-bottom: 60px; }
.n-wayang-frame {
    position: absolute;
    inset: 20px;
    pointer-events: none;
}

/* ─── RSVP form ────────────────────────────────────────────────────────────── */
.n-rsvp-form-wrap {
    max-width: 480px;
    margin: 0 auto;
    width: 100%;
}
.n-demo-notice {
    border: 1px solid;
    border-radius: 4px;
    padding: 16px;
    text-align: center;
    font-size: 13px;
    font-style: italic;
}
.n-success-msg {
    border: 1px solid;
    border-radius: 4px;
    padding: 32px;
    text-align: center;
    font-size: 18px;
    font-style: italic;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.n-rsvp-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.n-form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.n-form-field label {
    font-size: 11px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
}
.n-input {
    background: transparent;
    border: 0;
    border-bottom: 1px solid;
    padding: 10px 4px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s;
    width: 100%;
}
.n-input::placeholder { color: inherit; opacity: 0.4; }
.n-textarea { resize: vertical; min-height: 80px; }
.n-radio-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.n-radio-option {
    padding: 8px 16px;
    border: 1px solid;
    border-radius: 2px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    letter-spacing: 0.05em;
}
.n-form-error {
    font-size: 13px;
    color: #EF4444;
    font-style: italic;
}
.n-submit-btn {
    padding: 14px;
    border: 0;
    border-radius: 2px;
    font-size: 13px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    transition: opacity 0.3s, transform 0.15s;
    font-weight: 600;
}
.n-submit-btn:hover:not(:disabled) { opacity: 0.9; }
.n-submit-btn:active:not(:disabled) { transform: scale(0.99); }
.n-submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ─── Messages ─────────────────────────────────────────────────────────────── */
.n-msg-input-block {
    max-width: 480px;
    margin: 0 auto 32px;
    width: 100%;
}
.n-msg-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.n-input-light {
    background: transparent;
    border: 0;
    border-bottom: 1px solid;
    padding: 10px 4px;
    font-size: 14px;
    outline: none;
    width: 100%;
}
.n-input-light::placeholder { color: inherit; opacity: 0.35; }
.n-form-error-light { font-size: 13px; color: #DC2626; font-style: italic; }
.n-form-success-light { font-size: 13px; font-style: italic; }
.n-submit-btn-light {
    padding: 12px;
    border: 0;
    border-radius: 2px;
    font-size: 12px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    transition: opacity 0.3s;
    font-weight: 600;
}
.n-submit-btn-light:disabled { opacity: 0.5; cursor: not-allowed; }

.n-messages-list {
    max-width: 560px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 480px;
    overflow-y: auto;
    padding-right: 8px;
    scrollbar-width: thin;
    scrollbar-color: var(--n-primary) transparent;
}
.n-message-card {
    border-left: 3px solid;
    padding: 14px 16px;
    background: rgba(139, 105, 20, 0.04);
    border-radius: 0 4px 4px 0;
}
.n-msg-name { font-size: 16px; font-weight: 600; margin: 0 0 6px; }
.n-msg-text { font-size: 14px; font-style: italic; line-height: 1.7; margin: 0 0 8px; }
.n-msg-time { font-size: 11px; letter-spacing: 0.05em; }
.n-msg-empty { text-align: center; font-style: italic; font-size: 14px; padding: 32px 0; }

/* Message appear animation */
.n-msg-enter-active { transition: all 0.5s ease; }
.n-msg-enter-from   { opacity: 0; transform: translateY(-16px); }

/* ─── Closing section ──────────────────────────────────────────────────────── */
.n-closing { text-align: center; }
.n-closing-mandala {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
}
.n-closing-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    max-width: 500px;
    margin: 0 auto;
}
.n-closing-text {
    font-size: 15px;
    font-style: italic;
    line-height: 1.9;
    margin: 16px 0;
    max-width: 420px;
}
.n-closing-signoff {
    margin: 16px 0;
}
.n-closing-label {
    margin-bottom: 12px;
}
.n-closing-names {
    font-size: clamp(20px, 5vw, 28px);
    font-weight: 400;
    letter-spacing: 0.04em;
    margin: 0;
}
.n-wassalam {
    font-size: 14px;
    margin-top: 16px;
}
.n-footer {
    font-size: 11px;
    margin-top: 24px;
    letter-spacing: 0.08em;
}

/* ─── Unused legacy styles (kept for CSS class name stability) ──────────────── */
.n-bday-profile { display: none; }
.n-bday-photo-wrap {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border-width: 3px;
    border-style: solid;
    overflow: hidden;
    margin: 0 auto 16px;
}
.n-bday-photo { width: 100%; height: 100%; object-fit: cover; }
.n-bday-name { font-size: 28px; font-weight: 400; margin: 0 0 8px; }

/* ─── Bounce animation for arrow ──────────────────────────────────────────── */
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(6px); }
}

/* ─── Cover CTA (Buka Undangan) ────────────────────────────────────────────── */
.n-cover-cta {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
    padding-bottom: 40px;
}
.n-buka-btn {
    padding: 14px 36px;
    border: 0;
    border-radius: 2px;
    font-size: 14px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    font-weight: 600;
    box-shadow: 0 4px 24px rgba(139, 105, 20, 0.35);
    transition: opacity 0.3s, transform 0.15s, box-shadow 0.3s;
}
.n-buka-btn:hover {
    opacity: 0.9;
    box-shadow: 0 8px 32px rgba(139, 105, 20, 0.5);
    transform: translateY(-1px);
}
.n-buka-btn:active { transform: scale(0.98); }
.n-buka-hint {
    font-size: 11px;
    letter-spacing: 0.1em;
    font-style: italic;
}

/* CTA swap transition */
.n-cta-swap-enter-active { transition: opacity 0.5s ease, transform 0.5s ease; }
.n-cta-swap-leave-active { transition: opacity 0.3s ease; }
.n-cta-swap-enter-from   { opacity: 0; transform: translateY(12px); }
.n-cta-swap-leave-to     { opacity: 0; }

/* Sections reveal transition */
.n-sections-enter-active { transition: opacity 0.9s ease; }
.n-sections-enter-from   { opacity: 0; }

/* ─── Quote section ────────────────────────────────────────────────────────── */
.n-quote-block {
    text-align: center;
    max-width: 480px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}
.n-quote-mark {
    font-size: 64px;
    line-height: 0.6;
    opacity: 0.5;
    font-family: Georgia, serif;
    margin-bottom: 8px;
}
.n-quote-text {
    font-size: clamp(17px, 4vw, 22px);
    font-style: italic;
    line-height: 1.7;
    font-weight: 400;
    letter-spacing: 0.02em;
    margin: 0;
}
.n-quote-line {
    width: 40px;
    height: 1px;
}
.n-quote-source {
    font-size: 13px;
    letter-spacing: 0.12em;
    font-style: normal;
    margin: 0;
}

/* ─── Live streaming block ──────────────────────────────────────────────────── */
.n-livestream-block {
    text-align: center;
    padding: 24px 0;
}
.n-livestream-icon {
    display: flex;
    justify-content: center;
    margin-bottom: 12px;
    opacity: 0.8;
}
.n-livestream-heading {
    font-size: 22px;
    font-weight: 400;
    letter-spacing: 0.05em;
    margin: 0 0 6px;
}
.n-livestream-platform {
    font-size: 13px;
    font-style: italic;
    margin: 0 0 20px;
}
.n-livestream-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    border: 1px solid;
    border-radius: 2px;
    font-size: 13px;
    letter-spacing: 0.12em;
    text-decoration: none;
    text-transform: uppercase;
    transition: background 0.3s;
}
.n-livestream-btn:hover { background: rgba(201, 168, 76, 0.12); }

/* ─── Additional info block ─────────────────────────────────────────────────── */
.n-additional-block {
    max-width: 520px;
    margin: 0 auto;
    padding: 8px 0 24px;
}
.n-additional-text {
    font-size: 15px;
    line-height: 1.9;
    font-style: italic;
    text-align: center;
    margin: 0;
}

/* ─── Love story timeline ───────────────────────────────────────────────────── */
.n-timeline {
    position: relative;
    max-width: 560px;
    margin: 32px auto;
    padding-left: 32px;
}
.n-timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 8px;
    bottom: 8px;
    width: 1px;
    background: linear-gradient(to bottom, transparent, var(--n-primary), var(--n-primary), transparent);
    opacity: 0.4;
}
.n-timeline-item {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 0 0 36px 24px;
}
.n-timeline-item:last-child { padding-bottom: 0; }
.n-timeline-dot {
    position: absolute;
    left: -6px;
    top: 6px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    flex-shrink: 0;
}
.n-timeline-body { display: flex; flex-direction: column; gap: 4px; }
.n-timeline-year {
    font-size: 13px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin: 0;
}
.n-timeline-title {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
    line-height: 1.3;
}
.n-timeline-desc {
    font-size: 14px;
    line-height: 1.8;
    margin: 0;
}

/* ─── Video section ─────────────────────────────────────────────────────────── */
.n-video-wrap {
    max-width: 600px;
    margin: 0 auto 24px;
}
.n-video-frame {
    border: 1px solid;
    border-radius: 4px;
    overflow: hidden;
    position: relative;
    padding-top: 56.25%; /* 16:9 */
}
.n-video-iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
.n-video-fallback { text-align: center; padding: 24px 0; }
.n-video-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 32px;
    border: 1px solid;
    border-radius: 2px;
    font-size: 14px;
    letter-spacing: 0.12em;
    text-decoration: none;
    text-transform: uppercase;
    transition: background 0.3s;
}
.n-video-link:hover { background: rgba(139, 105, 20, 0.08); }
.n-video-caption {
    text-align: center;
    font-size: 13px;
    font-style: italic;
    margin: 0 auto 16px;
    max-width: 480px;
}

/* ─── Gift / amplop digital ─────────────────────────────────────────────────── */
.n-gift-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-width: 480px;
    margin: 32px auto;
}
.n-gift-card {
    border: 1px solid;
    border-radius: 4px;
    overflow: hidden;
    background: rgba(255,255,255,0.03);
    position: relative;
}
.n-gift-accent {
    height: 2px;
    width: 100%;
}
.n-gift-bank {
    font-size: 18px;
    font-weight: 400;
    letter-spacing: 0.08em;
    padding: 16px 20px 4px;
    margin: 0;
}
.n-gift-owner {
    font-size: 12px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 0 20px 12px;
    margin: 0;
}
.n-gift-number-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 20px 16px;
    border-top: 1px solid rgba(255,255,255,0.06);
}
.n-gift-number {
    font-size: 20px;
    font-weight: 600;
    letter-spacing: 0.06em;
    margin: 0;
}
.n-gift-copy {
    padding: 6px 14px;
    border: 1px solid;
    border-radius: 2px;
    font-size: 11px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s;
}
.n-gift-copy.is-copied {
    background: rgba(201, 168, 76, 0.15);
}

/* ─── Toast ────────────────────────────────────────────────────────────────── */
.n-toast {
    position: fixed;
    bottom: 88px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 200;
    padding: 12px 24px;
    border: 1px solid;
    border-radius: 4px;
    font-size: 13px;
    letter-spacing: 0.08em;
    white-space: nowrap;
    pointer-events: none;
    box-shadow: 0 4px 24px rgba(0,0,0,0.4);
}
.n-toast-enter-active,
.n-toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.n-toast-enter-from,
.n-toast-leave-to     { opacity: 0; transform: translateX(-50%) translateY(10px); }

/* ─── Reduced motion ───────────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .shimmer-gold { animation: none; -webkit-text-fill-color: unset; background: none; color: inherit; }
    .n-gate       { transition: none; }
    .n-music-btn.is-playing { animation: none; }
    .n-reveal, .sulur-path  { transition: none; opacity: 1; transform: none; }
}

/* ─── Mobile adjustments ───────────────────────────────────────────────────── */
@media (max-width: 480px) {
    .n-gate { width: clamp(64px, 18vw, 120px); }
    .n-gate-capital { width: 60px; }
    .n-countdown-grid { gap: 8px; }
    .n-cd-number { font-size: 22px; }
    .n-section-inner { padding: 48px 20px; }
    .n-cover { padding: 40px 16px; }
}
</style>
