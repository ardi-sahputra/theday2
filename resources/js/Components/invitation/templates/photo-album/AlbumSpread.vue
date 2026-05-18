<script setup>
import { computed } from 'vue'
import AlbumPage          from './AlbumPage.vue'
import PhotoCorner        from './PhotoCorner.vue'
import WashiTape          from './WashiTape.vue'
import HandwrittenCaption from './HandwrittenCaption.vue'
import PressedFlower      from './PressedFlower.vue'
import TheEndStamp        from './TheEndStamp.vue'

const props = defineProps({
    spreadKey:      { type: String, required: true },     // e.g. 'opening-couple', 'events', 'closing'
    pageNumbers:    { type: Array,  default: () => [2, 3] },
    isMobile:       { type: Boolean, default: false },
    washiPattern:   { type: String, default: 'mixed' },
    pressedFlower:  { type: Boolean, default: true },

    // Data (passed-through from orchestrator)
    invitation:     { type: Object, required: true },
    openingText:    { type: String, default: '' },
    closingText:    { type: String, default: '' },
    groomName:      { type: String, default: '' },
    brideName:      { type: String, default: '' },
    groomNick:      { type: String, default: '' },
    brideNick:      { type: String, default: '' },
    groomPhoto:     { type: String, default: null },
    bridePhoto:     { type: String, default: null },
    groomParents:   { type: String, default: '' },
    brideParents:   { type: String, default: '' },
    events:         { type: Array,  default: () => [] },
    galleries:      { type: Array,  default: () => [] },
    loveStories:    { type: Array,  default: () => [] },
    giftAccounts:   { type: Array,  default: () => [] },
    localMessages:  { type: Array,  default: () => [] },
    countdown:      { type: Object, default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:     { type: [Date, Object, String], default: null },
    pad:            { type: Function, default: (n) => String(n).padStart(2, '0') },
    firstEventDate: { type: String, default: '' },
    coverPhotoUrl:  { type: String, default: null },
    quoteText:      { type: String, default: '' },

    rsvpForm:       { type: Object, default: () => ({}) },
    submitRsvp:     { type: Function, default: () => {} },
    rsvpSubmitting: { type: Boolean, default: false },
    rsvpSuccess:    { type: Boolean, default: false },
    rsvpError:      { type: String, default: '' },

    msgForm:        { type: Object, default: () => ({}) },
    submitMessage:  { type: Function, default: () => {} },
    msgSubmitting:  { type: Boolean, default: false },
    msgSuccess:     { type: Boolean, default: false },
    msgError:       { type: String, default: '' },

    copiedAccount:  { type: String, default: '' },
    copyToClipboard:{ type: Function, default: () => {} },

    onLightboxOpen: { type: Function, default: () => {} },
})

const emit = defineEmits(['rsvp-submit', 'message-submit'])

const galleryPreview = computed(() => props.galleries.slice(0, 4))
const galleryRest    = computed(() => props.galleries.length - galleryPreview.value.length)

function imgUrl(g) { return g?.image_url ?? g?.file_url ?? '' }
function imgCaption(g) { return g?.caption ?? '' }
</script>

<template>
    <div class="pa-spread" :class="{ 'pa-spread--mobile': isMobile }">

        <!-- ───── Spread A: opening + couple ───── -->
        <template v-if="spreadKey === 'opening-couple'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <blockquote v-if="quoteText" class="pa-epigraph pa-reveal">
                    <HandwrittenCaption :rotate="-1" size="md">{{ quoteText }}</HandwrittenCaption>
                </blockquote>
                <header class="pa-section-header pa-reveal">
                    <span class="pa-rule"/><h2>Sebuah Kisah</h2><span class="pa-rule"/>
                </header>
                <p class="pa-body pa-reveal">
                    <span class="pa-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
                </p>
            </AlbumPage>

            <AlbumPage v-if="!isMobile" :side="'right'" :page-number="pageNumbers[1]">
                <header class="pa-section-header pa-reveal"><h2>Mempelai</h2></header>

                <div class="pa-couple-grid">
                    <figure v-if="groomPhoto" class="pa-photo-wrap pa-photo pa-reveal" style="--rot: -2deg; --idx: 0;">
                        <img :src="groomPhoto" :alt="groomName" class="pa-photo-img"/>
                        <PhotoCorner />
                        <WashiTape :pattern="washiPattern" position="top-left" :seed="1"/>
                        <figcaption class="pa-photo-cap">
                            <HandwrittenCaption :rotate="0.5" size="md">{{ groomName }}</HandwrittenCaption>
                            <p class="pa-parent">{{ groomParents }}</p>
                        </figcaption>
                    </figure>

                    <figure v-if="bridePhoto" class="pa-photo-wrap pa-photo pa-reveal" style="--rot: 2deg; --idx: 1;">
                        <img :src="bridePhoto" :alt="brideName" class="pa-photo-img"/>
                        <PhotoCorner />
                        <WashiTape :pattern="washiPattern" position="top-right" :seed="2"/>
                        <figcaption class="pa-photo-cap">
                            <HandwrittenCaption :rotate="-0.5" size="md">{{ brideName }}</HandwrittenCaption>
                            <p class="pa-parent">{{ brideParents }}</p>
                        </figcaption>
                    </figure>
                </div>

                <PressedFlower v-if="pressedFlower" variant="rose" position="bottom-right" :seed="11"/>
            </AlbumPage>

            <!-- Mobile: couple stacked below -->
            <AlbumPage v-if="isMobile" side="single" :page-number="pageNumbers[1]">
                <header class="pa-section-header pa-reveal"><h2>Mempelai</h2></header>
                <div class="pa-couple-grid">
                    <figure v-if="groomPhoto" class="pa-photo-wrap pa-photo pa-reveal" style="--rot: -2deg; --idx: 0;">
                        <img :src="groomPhoto" :alt="groomName" class="pa-photo-img"/>
                        <PhotoCorner />
                        <figcaption class="pa-photo-cap">
                            <HandwrittenCaption :rotate="0.5" size="md">{{ groomName }}</HandwrittenCaption>
                            <p class="pa-parent">{{ groomParents }}</p>
                        </figcaption>
                    </figure>
                    <figure v-if="bridePhoto" class="pa-photo-wrap pa-photo pa-reveal" style="--rot: 2deg; --idx: 1;">
                        <img :src="bridePhoto" :alt="brideName" class="pa-photo-img"/>
                        <PhotoCorner />
                        <figcaption class="pa-photo-cap">
                            <HandwrittenCaption :rotate="-0.5" size="md">{{ brideName }}</HandwrittenCaption>
                            <p class="pa-parent">{{ brideParents }}</p>
                        </figcaption>
                    </figure>
                </div>
            </AlbumPage>
        </template>

        <!-- ───── Spread B: events ───── -->
        <template v-else-if="spreadKey === 'events'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal">
                    <h2>Itinerary</h2>
                    <span class="pa-gold-rule"/>
                </header>
                <ul class="pa-event-list pa-lined">
                    <li v-for="(ev, idx) in events.slice(0, 2)" :key="`evL-${idx}`" class="pa-event-item pa-reveal" :style="{ '--idx': idx }">
                        <span class="pa-event-chip">{{ ev.event_name }}</span>
                        <p class="pa-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                        <p class="pa-event-time">
                            {{ ev.start_time }}<template v-if="ev.end_time"> - {{ ev.end_time }}</template>
                        </p>
                        <p class="pa-event-venue">{{ ev.venue_name }}</p>
                        <p class="pa-event-addr">{{ ev.venue_address ?? ev.location ?? '' }}</p>
                        <a v-if="ev.maps_url" :href="ev.maps_url" class="pa-maps-link" target="_blank" rel="noopener">Buka Maps »</a>
                        <WashiTape v-if="idx < 1" :pattern="washiPattern" position="horizontal-bottom" :length="180" :seed="idx + 3"/>
                    </li>
                </ul>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <ul class="pa-event-list pa-lined">
                    <li v-for="(ev, idx) in events.slice(2, 4)" :key="`evR-${idx}`" class="pa-event-item pa-reveal" :style="{ '--idx': idx }">
                        <span class="pa-event-chip">{{ ev.event_name }}</span>
                        <p class="pa-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                        <p class="pa-event-time">
                            {{ ev.start_time }}<template v-if="ev.end_time"> - {{ ev.end_time }}</template>
                        </p>
                        <p class="pa-event-venue">{{ ev.venue_name }}</p>
                        <p class="pa-event-addr">{{ ev.venue_address ?? ev.location ?? '' }}</p>
                        <a v-if="ev.maps_url" :href="ev.maps_url" class="pa-maps-link" target="_blank" rel="noopener">Buka Maps »</a>
                    </li>
                </ul>
                <HandwrittenCaption class="pa-corner-note pa-reveal" :rotate="1" size="md">Save the dates ♥</HandwrittenCaption>
            </AlbumPage>
        </template>

        <!-- ───── Spread C: countdown ───── -->
        <template v-else-if="spreadKey === 'countdown'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal"><h2>Menuju Hari Bahagia</h2></header>
                <div class="pa-cd-grid">
                    <div class="pa-cd-card pa-reveal" v-for="(unit, idx) in [
                        { label: 'HARI',  value: countdown.days,    sub: 'Days'    },
                        { label: 'JAM',   value: countdown.hours,   sub: 'Hours'   },
                        { label: 'MENIT', value: countdown.minutes, sub: 'Minutes' },
                        { label: 'DETIK', value: countdown.seconds, sub: 'Seconds' },
                    ]" :key="unit.label" :style="{ '--idx': idx }">
                        <span class="pa-cd-strip">
                            <HandwrittenCaption :rotate="0" size="sm">{{ unit.sub }}</HandwrittenCaption>
                        </span>
                        <Transition name="pa-cd-flip" mode="out-in">
                            <span :key="unit.value" class="pa-cd-digit">{{ pad(unit.value) }}</span>
                        </Transition>
                        <span class="pa-cd-label">{{ unit.label }}</span>
                    </div>
                </div>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <figure class="pa-photo-wrap pa-photo pa-reveal" style="--rot: -2deg; --idx: 0;">
                    <img
                        :src="imgUrl(galleries[0]) || coverPhotoUrl || '/image/demo-image/cover-demo.webp'"
                        :alt="imgCaption(galleries[0]) || 'First moment'"
                        class="pa-photo-img pa-photo-img--lg"/>
                    <PhotoCorner />
                    <WashiTape :pattern="washiPattern" position="top-center" :length="160" :seed="5"/>
                </figure>
                <img class="pa-arrow pa-reveal" src="/images/templates/photo-album/hand-drawn-arrow.svg" alt="" aria-hidden="true"/>
                <HandwrittenCaption class="pa-first-moment-cap pa-reveal" :rotate="-1" size="md">"{{ firstEventDate }}, akhirnya tiba"</HandwrittenCaption>
            </AlbumPage>
        </template>

        <!-- ───── Spread D: love_story ───── -->
        <template v-else-if="spreadKey === 'love_story'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal">
                    <h2>Our Story</h2>
                    <span class="pa-gold-rule"/>
                </header>
                <ol class="pa-story-list">
                    <li
                        v-for="(s, idx) in loveStories.slice(0, Math.ceil(loveStories.length / 2))"
                        :key="`storyL-${idx}`"
                        class="pa-story-item pa-reveal"
                        :style="{ '--idx': idx, '--rot': `${idx % 2 ? 1.5 : -1.5}deg` }">
                        <figure v-if="s.photo_url" class="pa-photo-wrap pa-photo" :style="{ '--rot': `${idx % 2 ? 1.5 : -1.5}deg`, '--idx': idx }">
                            <img :src="s.photo_url" :alt="s.title || ''" class="pa-photo-img pa-photo-img--sm"/>
                            <PhotoCorner />
                            <WashiTape :pattern="washiPattern" :position="idx % 2 ? 'top-right' : 'top-left'" :seed="idx + 10"/>
                        </figure>
                        <h3 class="pa-story-title">{{ s.title }}</h3>
                        <time class="pa-story-date">{{ s.date }}</time>
                        <p class="pa-story-desc">{{ s.description }}</p>
                    </li>
                </ol>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <ol class="pa-story-list">
                    <li
                        v-for="(s, idx) in loveStories.slice(Math.ceil(loveStories.length / 2))"
                        :key="`storyR-${idx}`"
                        class="pa-story-item pa-reveal"
                        :style="{ '--idx': idx + 10 }">
                        <figure v-if="s.photo_url" class="pa-photo-wrap pa-photo" :style="{ '--rot': `${idx % 2 ? -1.5 : 1.5}deg`, '--idx': idx + 10 }">
                            <img :src="s.photo_url" :alt="s.title || ''" class="pa-photo-img pa-photo-img--sm"/>
                            <PhotoCorner />
                        </figure>
                        <h3 class="pa-story-title">{{ s.title }}</h3>
                        <time class="pa-story-date">{{ s.date }}</time>
                        <p class="pa-story-desc">{{ s.description }}</p>
                        <HandwrittenCaption v-if="s.description && s.description.length < 60" :rotate="2" size="md">"{{ s.title }}!"</HandwrittenCaption>
                    </li>
                </ol>
                <PressedFlower v-if="pressedFlower" variant="leaf" position="bottom-left" :seed="21"/>
            </AlbumPage>
        </template>

        <!-- ───── Spread E: gallery ───── -->
        <template v-else-if="spreadKey === 'gallery'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal"><h2>Moments</h2></header>
                <div class="pa-gallery-grid">
                    <figure
                        v-for="(g, idx) in galleryPreview.slice(0, 2)"
                        :key="`glL-${idx}`"
                        class="pa-photo-wrap pa-photo pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? -2 : 2}deg`, '--idx': idx }"
                        @click="onLightboxOpen(imgUrl(g))">
                        <img :src="imgUrl(g)" :alt="imgCaption(g)" class="pa-photo-img"/>
                        <PhotoCorner />
                        <WashiTape :pattern="washiPattern" :position="idx % 2 ? 'bottom-right' : 'top-left'" :seed="idx + 30"/>
                        <figcaption v-if="imgCaption(g)" class="pa-photo-cap">
                            <HandwrittenCaption :rotate="idx % 2 ? -1 : 1" size="sm">{{ imgCaption(g) }}</HandwrittenCaption>
                        </figcaption>
                    </figure>
                </div>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <div class="pa-gallery-grid">
                    <figure
                        v-for="(g, idx) in galleryPreview.slice(2, 4)"
                        :key="`glR-${idx}`"
                        class="pa-photo-wrap pa-photo pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? 2 : -2}deg`, '--idx': idx + 2 }"
                        @click="onLightboxOpen(imgUrl(g))">
                        <img :src="imgUrl(g)" :alt="imgCaption(g)" class="pa-photo-img"/>
                        <PhotoCorner />
                        <WashiTape :pattern="washiPattern" :position="idx % 2 ? 'top-right' : 'bottom-left'" :seed="idx + 40"/>
                    </figure>
                </div>
                <button v-if="galleryRest > 0" class="pa-see-all" @click="onLightboxOpen(imgUrl(galleries[0]))">
                    Lihat semua ({{ galleries.length }})
                </button>
            </AlbumPage>
        </template>

        <!-- ───── Spread F: rsvp ───── -->
        <template v-else-if="spreadKey === 'rsvp'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal">
                    <h2>Reply Slip</h2>
                    <span class="pa-stamp-chip">RSVP by {{ firstEventDate }}</span>
                </header>

                <form class="pa-rsvp pa-lined" @submit.prevent="emit('rsvp-submit')">
                    <label class="pa-field">
                        <span class="pa-field-label">NAMA TAMU</span>
                        <input v-model="rsvpForm.guest_name" type="text" class="pa-input-hand" placeholder="Nama lengkap" required/>
                    </label>

                    <fieldset class="pa-field">
                        <legend class="pa-field-label">KEHADIRAN</legend>
                        <label class="pa-check-row">
                            <input type="radio" v-model="rsvpForm.attendance" value="yes"/>
                            <span class="pa-check-box"/> Hadir
                        </label>
                        <label class="pa-check-row">
                            <input type="radio" v-model="rsvpForm.attendance" value="no"/>
                            <span class="pa-check-box"/> Tidak Hadir
                        </label>
                    </fieldset>
                </form>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <form class="pa-rsvp pa-lined" @submit.prevent="emit('rsvp-submit')">
                    <label class="pa-field">
                        <span class="pa-field-label">JUMLAH TAMU</span>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="pa-input-hand"/>
                    </label>

                    <label class="pa-field">
                        <span class="pa-field-label">CATATAN</span>
                        <textarea v-model="rsvpForm.notes" rows="3" class="pa-input-hand pa-input-hand--multi" placeholder="Tulis pesan singkat..."/>
                    </label>

                    <button type="submit" class="pa-submit-stamp" :disabled="rsvpSubmitting">{{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM' }}</button>
                    <p v-if="rsvpError" class="pa-form-error">{{ rsvpError }}</p>
                </form>
                <TheEndStamp v-if="rsvpSuccess" text="TERKIRIM" class="pa-rsvp-success"/>
            </AlbumPage>
        </template>

        <!-- ───── Spread G: gift ───── -->
        <template v-else-if="spreadKey === 'gift'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal"><h2>Hadiah Pernikahan</h2></header>
                <p class="pa-gift-sub pa-reveal"><em>Doa restu Anda adalah hadiah terindah. Namun jika berkenan…</em></p>

                <ul class="pa-gift-list">
                    <li
                        v-for="(acc, idx) in giftAccounts.slice(0, Math.ceil(giftAccounts.length / 2))"
                        :key="`giftL-${idx}`"
                        class="pa-gift-card pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? 1 : -1}deg`, '--idx': idx }">
                        <span class="pa-gift-bank">{{ acc.bank_name }}</span>
                        <strong class="pa-gift-holder">{{ acc.account_holder }}</strong>
                        <span class="pa-gift-number">{{ acc.account_number }}</span>
                        <button class="pa-wax-seal" @click="copyToClipboard(acc.account_number)">
                            {{ copiedAccount === acc.account_number ? 'TERSALIN ✓' : 'SALIN' }}
                        </button>
                    </li>
                </ul>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <ul class="pa-gift-list">
                    <li
                        v-for="(acc, idx) in giftAccounts.slice(Math.ceil(giftAccounts.length / 2))"
                        :key="`giftR-${idx}`"
                        class="pa-gift-card pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? -1 : 1}deg`, '--idx': idx + 10 }">
                        <span class="pa-gift-bank">{{ acc.bank_name }}</span>
                        <strong class="pa-gift-holder">{{ acc.account_holder }}</strong>
                        <span class="pa-gift-number">{{ acc.account_number }}</span>
                        <button class="pa-wax-seal" @click="copyToClipboard(acc.account_number)">
                            {{ copiedAccount === acc.account_number ? 'TERSALIN ✓' : 'SALIN' }}
                        </button>
                    </li>
                </ul>
                <PressedFlower v-if="pressedFlower" variant="leaf" position="bottom-right" :seed="33"/>
            </AlbumPage>
        </template>

        <!-- ───── Spread H: wishes ───── -->
        <template v-else-if="spreadKey === 'wishes'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal"><h2>Memory Book</h2></header>

                <form class="pa-wish-form pa-lined" @submit.prevent="emit('message-submit')">
                    <label class="pa-field">
                        <span class="pa-field-label">NAMA</span>
                        <input v-model="msgForm.name" type="text" class="pa-input-hand" placeholder="Nama Anda" required/>
                    </label>
                    <label class="pa-field">
                        <span class="pa-field-label">UCAPAN</span>
                        <textarea v-model="msgForm.message" rows="3" class="pa-input-hand pa-input-hand--multi" placeholder="Tulis ucapan & doa..." required/>
                    </label>
                    <button type="submit" class="pa-submit-stamp" :disabled="msgSubmitting">{{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}</button>
                    <p v-if="msgError" class="pa-form-error">{{ msgError }}</p>
                    <p v-if="msgSuccess" class="pa-form-success">Terima kasih atas ucapannya.</p>
                </form>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <ul class="pa-wish-list">
                    <li
                        v-for="(m, idx) in localMessages.slice(0, 10)"
                        :key="`wish-${idx}`"
                        class="pa-wish-card pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? 1 : -1}deg`, '--idx': idx }">
                        <p class="pa-wish-msg">{{ m.message }}</p>
                        <span class="pa-wish-sig">— {{ m.name }}</span>
                    </li>
                </ul>
                <PressedFlower v-if="pressedFlower" variant="petal" position="bottom-right" :seed="44"/>
            </AlbumPage>
        </template>

        <!-- ───── Back cover: closing ───── -->
        <template v-else-if="spreadKey === 'closing'">
            <AlbumPage side="single" :page-number="pageNumbers[0]">
                <div class="pa-back-cover">
                    <TheEndStamp text="The End" class="pa-reveal"/>
                    <p class="pa-closing-text pa-reveal">{{ closingText }}</p>
                    <HandwrittenCaption :rotate="-1" size="lg" class="pa-back-signoff pa-reveal">{{ groomNick }} &amp; {{ brideNick }}</HandwrittenCaption>
                    <p class="pa-back-date pa-reveal">{{ firstEventDate }}</p>
                    <PressedFlower v-if="pressedFlower" variant="full-bouquet" position="bottom-right" :seed="99"/>
                    <slot name="watermark"/>
                </div>
            </AlbumPage>
        </template>
    </div>
</template>

<style scoped>
.pa-spread {
    position: relative;
    width: 100%;
    height: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: linear-gradient(90deg, transparent 48%, rgba(0,0,0,0.6) 50%, transparent 52%);
}
.pa-spread--mobile { grid-template-columns: 1fr; background: none; }

/* ─── Headers / typography ─── */
.pa-section-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    text-align: center;
    margin-bottom: 18px;
}
.pa-section-header h2 {
    font-family: 'Cormorant SC', serif;
    font-size: 22px;
    font-weight: 600;
    color: #f4ead5;
    letter-spacing: 4px;
    margin: 0;
}
.pa-rule {
    width: 40px; height: 1px;
    background: #d4a574;
    display: inline-block;
}
.pa-gold-rule {
    display: block;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4a574 50%, transparent);
    margin: 6px auto 14px;
    width: 60%;
}
.pa-epigraph {
    border-left: 2px solid #8b6f47;
    padding-left: 12px;
    margin: 0 0 18px;
}
.pa-body {
    font-family: 'Crimson Text', Georgia, serif;
    font-size: 16px;
    line-height: 1.85;
    color: #f4ead5;
    text-align: justify;
}
.pa-dropcap {
    font-family: 'Cormorant SC', serif;
    font-size: 64px;
    color: #d4a574;
    float: left;
    line-height: 0.9;
    padding: 4px 8px 0 0;
}

/* ─── Couple ─── */
.pa-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-top: 12px;
}
.pa-photo-wrap {
    position: relative;
    margin: 8px auto;
    max-width: 220px;
    padding: 8px;
    background: #f4ead5;
    border: 1px solid #5a3818;
    transform: rotate(var(--rot, 0deg));
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}
.pa-photo-img {
    display: block;
    width: 100%;
    aspect-ratio: 3 / 4;
    object-fit: cover;
    filter: sepia(0.18) saturate(0.92) brightness(0.96);
}
.pa-photo-img--lg { aspect-ratio: 4 / 3; max-height: 280px; }
.pa-photo-img--sm { aspect-ratio: 4 / 3; max-height: 140px; }
.pa-photo-cap {
    margin-top: 10px;
    text-align: center;
}
.pa-parent {
    font-family: 'Cormorant SC', serif;
    font-style: italic;
    font-size: 13px;
    color: #c9bfa8;
    margin: 4px 0 0;
}

/* ─── Photo stick-on animation ─── */
@keyframes pa-photo-stick {
    0%   { transform: translateY(-10px) rotate(var(--rot, 0deg)); opacity: 0; }
    100% { transform: translateY(0)     rotate(var(--rot, 0deg)); opacity: 1; }
}
.pa-photo.pa-visible {
    animation: pa-photo-stick 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    animation-delay: calc(var(--idx, 0) * 80ms);
}

/* ─── Events ─── */
.pa-event-list { list-style: none; padding: 0; margin: 0; }
.pa-event-item {
    position: relative;
    padding: 16px 8px 28px;
    border-bottom: 1px dashed rgba(244, 234, 213, 0.18);
}
.pa-event-chip {
    display: inline-block;
    padding: 4px 10px;
    background: #d4a574;
    color: #1a1410;
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
}
.pa-event-date {
    font-family: 'Cormorant SC', serif;
    font-size: 18px;
    color: #f4ead5;
    margin: 8px 0 2px;
}
.pa-event-time, .pa-event-venue, .pa-event-addr {
    font-family: 'Crimson Text', serif;
    font-size: 14px;
    color: #c9bfa8;
    margin: 2px 0;
}
.pa-event-venue { color: #f4ead5; font-weight: 600; }
.pa-maps-link {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    color: #d4a574;
    text-decoration: underline;
    font-size: 14px;
}
.pa-corner-note {
    display: block;
    text-align: right;
    margin-top: 24px;
    color: #d4a574;
}

/* ─── Countdown ─── */
.pa-cd-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 20px;
}
.pa-cd-card {
    position: relative;
    padding: 24px 8px 12px;
    background-image: url('/images/templates/photo-album/calendar-tear-off.svg');
    background-size: 100% 100%;
    background-repeat: no-repeat;
    text-align: center;
    min-height: 160px;
}
.pa-cd-strip {
    position: absolute;
    top: 6px; left: 50%;
    transform: translateX(-50%);
    color: #5a3818;
}
.pa-cd-digit {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 56px;
    font-weight: 600;
    color: #1a1410;
    text-shadow: 1px 1px 0 rgba(122, 56, 56, 0.25);
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.pa-cd-label {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    color: #5a3818;
    letter-spacing: 3px;
    margin-top: 6px;
}
.pa-cd-flip-enter-active, .pa-cd-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.pa-cd-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.pa-cd-flip-leave-to   { transform: rotateX(90deg);  opacity: 0; }

.pa-arrow {
    display: block;
    width: 140px;
    height: auto;
    margin: -8px auto -8px;
    transform: rotate(-8deg);
}
.pa-first-moment-cap {
    display: block;
    text-align: center;
    margin: 10px auto 0;
    color: #d4a574;
}

/* ─── Love story ─── */
.pa-story-list { list-style: none; padding: 0; margin: 0; }
.pa-story-item {
    margin-bottom: 22px;
    position: relative;
}
.pa-story-title {
    font-family: 'Cormorant SC', serif;
    font-size: 14px;
    color: #d4a574;
    letter-spacing: 3px;
    margin: 6px 0 2px;
}
.pa-story-date {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 12px;
    color: #c9bfa8;
}
.pa-story-desc {
    font-family: 'Crimson Text', serif;
    font-size: 14px;
    color: #f4ead5;
    line-height: 1.7;
    margin: 6px 0;
}

/* ─── Gallery ─── */
.pa-gallery-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}
.pa-see-all {
    margin-top: 16px;
    padding: 6px 14px;
    background: transparent;
    border: 1px solid #d4a574;
    color: #d4a574;
    font-family: 'Cormorant SC', serif;
    letter-spacing: 3px;
    cursor: pointer;
}
.pa-see-all:hover { background: #d4a574; color: #1a1410; }

/* ─── RSVP & wishes (lined paper inputs) ─── */
.pa-lined {
    background-image: url('/images/templates/photo-album/lined-paper.svg');
    background-size: 100% auto;
    background-repeat: repeat-y;
    padding-top: 8px;
}
.pa-field {
    display: block;
    margin-bottom: 18px;
}
.pa-field-label {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    color: #d4a574;
    margin-bottom: 4px;
}
.pa-input-hand {
    width: 100%;
    background: transparent;
    border: none;
    border-bottom: 1px dashed rgba(244, 234, 213, 0.3);
    font-family: 'Homemade Apple', cursive;
    font-size: 20px;
    color: #8b6f47;
    padding: 4px 0;
    outline: none;
}
.pa-input-hand--multi {
    border-bottom: none;
    font-size: 16px;
    line-height: 28px;
    resize: vertical;
}
.pa-input-hand:focus {
    border-bottom-color: #d4a574;
}
.pa-input-hand::placeholder {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    color: rgba(201, 191, 168, 0.5);
    font-size: 14px;
}
.pa-check-row {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-right: 18px;
    font-family: 'Homemade Apple', cursive;
    color: #f4ead5;
}
.pa-check-row input[type="radio"] { display: none; }
.pa-check-box {
    display: inline-block;
    width: 18px; height: 18px;
    border: 1.5px solid #d4a574;
    position: relative;
}
.pa-check-row input[type="radio"]:checked + .pa-check-box::after {
    content: 'x';
    position: absolute;
    inset: -4px 0 0 2px;
    color: #d4a574;
    font-family: 'Homemade Apple', cursive;
    font-size: 22px;
}
.pa-stamp-chip {
    display: inline-block;
    padding: 4px 10px;
    border: 2px solid #7a3838;
    color: #7a3838;
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    transform: rotate(-3deg);
    margin-left: 8px;
}
.pa-submit-stamp {
    margin-top: 14px;
    padding: 10px 22px;
    background: #d4a574;
    color: #1a1410;
    font-family: 'Cormorant SC', serif;
    letter-spacing: 4px;
    font-size: 14px;
    text-transform: uppercase;
    border: 2px solid #5a3818;
    cursor: pointer;
    transform: rotate(-2deg);
}
.pa-submit-stamp[disabled] { opacity: 0.6; cursor: wait; }
.pa-form-error   { color: #d97b6c; font-family: 'Crimson Text', serif; font-style: italic; margin-top: 8px; }
.pa-form-success { color: #d4a574; font-family: 'Crimson Text', serif; font-style: italic; margin-top: 8px; }
.pa-rsvp-success { display: block; margin: 18px auto 0; max-width: 220px; }

/* ─── Gift ─── */
.pa-gift-sub {
    text-align: center;
    color: #c9bfa8;
    font-family: 'Crimson Text', serif;
    font-size: 14px;
    margin-bottom: 18px;
}
.pa-gift-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 16px; }
.pa-gift-card {
    position: relative;
    padding: 16px;
    background: #f4ead5;
    color: #1a1410;
    border: 1px solid #5a3818;
    text-align: center;
    transform: rotate(var(--rot, 0deg));
}
.pa-gift-bank {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    color: #d4a574;
    letter-spacing: 3px;
}
.pa-gift-holder {
    display: block;
    font-family: 'Crimson Text', serif;
    font-size: 18px;
    margin: 6px 0;
}
.pa-gift-number {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 22px;
    letter-spacing: 4px;
    font-variant-numeric: tabular-nums;
}
.pa-wax-seal {
    margin-top: 10px;
    padding: 6px 16px;
    background: #d4a574;
    color: #1a1410;
    border: 2px solid #5a3818;
    font-family: 'Cormorant SC', serif;
    letter-spacing: 3px;
    font-size: 12px;
    cursor: pointer;
    border-radius: 999px;
}

/* ─── Wishes ─── */
.pa-wish-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 12px; }
.pa-wish-card {
    background: #f4ead5;
    color: #1a1410;
    padding: 10px 14px;
    transform: rotate(var(--rot, 0deg));
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
}
.pa-wish-msg {
    font-family: 'Homemade Apple', cursive;
    color: #8b6f47;
    font-size: 15px;
    line-height: 1.4;
    margin: 0 0 6px;
}
.pa-wish-sig {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 13px;
    color: #5a3818;
}

/* ─── Back cover ─── */
.pa-back-cover {
    position: relative;
    text-align: center;
    padding: 60px 24px;
    max-width: 480px;
    margin: 0 auto;
    color: #f4ead5;
    display: flex;
    flex-direction: column;
    gap: 24px;
    align-items: center;
}
.pa-closing-text {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 16px;
    line-height: 1.7;
}
.pa-back-signoff { color: #d4a574; }
.pa-back-date {
    font-family: 'Cormorant SC', serif;
    font-size: 14px;
    letter-spacing: 3px;
    color: #c9bfa8;
}

/* ─── Reveal base ─── */
.pa-reveal {
    opacity: 0;
    transform: translateY(18px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.pa-reveal.pa-visible {
    opacity: 1;
    transform: translateY(0);
}

/* ─── Desktop spread refinement ─── */
@media (min-width: 1024px) {
    .pa-couple-grid { grid-template-columns: 1fr; }
    .pa-cd-grid     { grid-template-columns: repeat(2, 1fr); }
}

/* ─── Mobile single-page mode ─── */
@media (max-width: 1023px) {
    .pa-spread { grid-template-columns: 1fr; }
}

/* ─── Reduced motion ─── */
@media (prefers-reduced-motion: reduce) {
    .pa-reveal,
    .pa-photo {
        animation: none !important;
        transition: opacity 0.2s ease !important;
        transform: rotate(var(--rot, 0deg)) !important;
        opacity: 1 !important;
    }
    .pa-cd-flip-enter-active, .pa-cd-flip-leave-active {
        transition: opacity 0.15s ease !important;
        transform: none !important;
    }
}
</style>
