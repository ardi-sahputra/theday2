<script setup>
import { ref, onMounted, computed } from 'vue'
import TarotCard    from './TarotCard.vue'
import MysticalAura from './MysticalAura.vue'
import GallerySection from '@/Components/invitation/sections/GallerySection.vue'

const props = defineProps({
    card:          { type: Object, required: true },   // { key, roman, name, sectionKey, illustrationKey, legendary, foilTier }
    sectionData:   { type: Object, default: () => ({}) },
    holoIntensity: { type: String, default: 'medium' },
    monogramText:  { type: String, default: 'G & B' },
    // Section-specific data passed from orchestrator
    groomName:     { type: String, default: '' },
    brideName:     { type: String, default: '' },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
    coverPhotoUrl: { type: String, default: null },
    openingText:   { type: String, default: '' },
    closingText:   { type: String, default: '' },
    events:        { type: Array,  default: () => [] },
    galleries:     { type: Array,  default: () => [] },
    layout:        { type: String, default: 'grid' },
    countdown:     { type: Object, default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:    { type: String, default: null },
    loveStories:   { type: Array,  default: () => [] },
    giftAccounts:  { type: Array,  default: () => [] },
    quoteText:     { type: String, default: '' },
    localMessages: { type: Array,  default: () => [] },
    msgForm:       { type: Object, default: () => ({ name: '', message: '' }) },
    msgSubmitting: { type: Boolean, default: false },
    msgSuccess:    { type: Boolean, default: false },
    rsvpForm:      { type: Object, default: () => ({ name: '', attending: 'yes', guests: 1 }) },
    rsvpSubmitting:{ type: Boolean, default: false },
    rsvpSuccess:   { type: Boolean, default: false },
    copiedAccount:  { type: String, default: null },
    details:        { type: Object, default: () => ({}) },
})

defineEmits(['back', 'submit-rsvp', 'submit-message', 'copy-account'])

const showContent = ref(false)
onMounted(() => {
    // Show section content after card flip animation (1s flip + 0.3s buffer)
    setTimeout(() => { showContent.value = true }, 1300)
})

const auraCount = computed(() => props.card.legendary ? 8 : 4)
const effectiveHolo = computed(() => props.card.legendary ? 'legendary' : props.holoIntensity)
</script>

<template>
    <section class="tr-reveal">
        <MysticalAura :count="auraCount" :enabled="true"/>

        <!-- Back button -->
        <button
            type="button"
            class="tr-reveal__back"
            @click="$emit('back')"
            aria-label="Kembali ke semua kartu"
        >
            &#8592; Kembali
        </button>

        <!-- Large focused card -->
        <div class="tr-reveal__card-wrap">
            <TarotCard
                :roman="card.roman"
                :name="card.name"
                :revealed="true"
                :index="0"
                :monogram-text="monogramText"
                :holo-intensity="effectiveHolo"
                :illustration-key="card.illustrationKey"
                :legendary="card.legendary"
            />
        </div>

        <!-- Section content panel -->
        <Transition name="tr-content-fade">
            <div v-if="showContent" class="tr-reveal__content">

                <!-- I — opening -->
                <template v-if="card.sectionKey === 'opening'">
                    <div class="tr-reveal__section tr-reveal__section--opening">
                        <p class="tr-reveal__opening-text">{{ openingText }}</p>
                    </div>
                </template>

                <!-- II — couple -->
                <template v-else-if="card.sectionKey === 'couple'">
                    <div class="tr-reveal__section tr-reveal__section--couple">
                        <div class="tr-couple-pair">
                            <div v-if="coverPhotoUrl" class="tr-couple__photo-wrap">
                                <img :src="coverPhotoUrl" class="tr-couple__photo" :alt="groomNick + ' & ' + brideNick" draggable="false"/>
                            </div>
                            <div class="tr-couple__names">
                                <span class="tr-couple__name">{{ groomName }}</span>
                                <span class="tr-couple__amp">&amp;</span>
                                <span class="tr-couple__name">{{ brideName }}</span>
                            </div>
                            <div v-if="details.groom_parent_names || details.bride_parent_names" class="tr-couple__parents">
                                <p v-if="details.groom_parent_names">{{ details.groom_parent_names }}</p>
                                <p v-if="details.bride_parent_names">{{ details.bride_parent_names }}</p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- III — countdown -->
                <template v-else-if="card.sectionKey === 'countdown'">
                    <div class="tr-reveal__section tr-reveal__section--countdown">
                        <div v-if="targetDate" class="tr-countdown">
                            <div class="tr-countdown__unit">
                                <span class="tr-countdown__num">{{ countdown.days }}</span>
                                <span class="tr-countdown__label">hari</span>
                            </div>
                            <div class="tr-countdown__unit">
                                <span class="tr-countdown__num">{{ countdown.hours }}</span>
                                <span class="tr-countdown__label">jam</span>
                            </div>
                            <div class="tr-countdown__unit">
                                <span class="tr-countdown__num">{{ countdown.minutes }}</span>
                                <span class="tr-countdown__label">menit</span>
                            </div>
                            <div class="tr-countdown__unit">
                                <span class="tr-countdown__num">{{ countdown.seconds }}</span>
                                <span class="tr-countdown__label">detik</span>
                            </div>
                        </div>
                        <p v-else class="tr-reveal__empty">Tanggal belum ditentukan.</p>
                    </div>
                </template>

                <!-- IV — events -->
                <template v-else-if="card.sectionKey === 'events'">
                    <div class="tr-reveal__section tr-reveal__section--events">
                        <div v-for="ev in events" :key="ev.id ?? ev.event_name" class="tr-event-card">
                            <h3 class="tr-event__name">{{ ev.event_name }}</h3>
                            <p class="tr-event__date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                            <p v-if="ev.start_time" class="tr-event__time">{{ ev.start_time }}{{ ev.end_time ? ' – ' + ev.end_time : '' }}</p>
                            <p v-if="ev.venue_name" class="tr-event__venue">{{ ev.venue_name }}</p>
                            <p v-if="ev.venue_address" class="tr-event__address">{{ ev.venue_address }}</p>
                            <a v-if="ev.maps_url" :href="ev.maps_url" target="_blank" rel="noopener noreferrer" class="tr-event__maps">
                                Lihat di Maps &#8599;
                            </a>
                        </div>
                        <p v-if="!events.length" class="tr-reveal__empty">Belum ada acara.</p>
                    </div>
                </template>

                <!-- V — love_story (journey) -->
                <template v-else-if="card.sectionKey === 'love_story'">
                    <div class="tr-reveal__section tr-reveal__section--love-story">
                        <div
                            v-for="(story, idx) in loveStories"
                            :key="idx"
                            class="tr-story-item"
                        >
                            <span class="tr-story__date">{{ story.date }}</span>
                            <h3 class="tr-story__title">{{ story.title }}</h3>
                            <p class="tr-story__desc">{{ story.description }}</p>
                        </div>
                        <p v-if="!loveStories.length" class="tr-reveal__empty">Belum ada cerita.</p>
                    </div>
                </template>

                <!-- VI — gallery (legendary) -->
                <template v-else-if="card.sectionKey === 'gallery'">
                    <div class="tr-reveal__section tr-reveal__section--gallery">
                        <GallerySection
                            v-if="galleries.length"
                            :galleries="galleries"
                            :layout="layout"
                            :primary-color="'#D4AF37'"
                        />
                        <p v-else class="tr-reveal__empty">Belum ada foto.</p>
                    </div>
                </template>

                <!-- VII — RSVP (promise) -->
                <template v-else-if="card.sectionKey === 'rsvp'">
                    <div class="tr-reveal__section tr-reveal__section--rsvp">
                        <div v-if="rsvpSuccess" class="tr-rsvp__success">
                            <p>Terima kasih sudah konfirmasi! &#10022;</p>
                        </div>
                        <form v-else class="tr-rsvp__form" @submit.prevent="$emit('submit-rsvp')">
                            <div class="tr-form-field">
                                <label class="tr-form-label">Nama</label>
                                <input v-model="rsvpForm.name" type="text" class="tr-form-input" placeholder="Nama Anda" required/>
                            </div>
                            <div class="tr-form-field">
                                <label class="tr-form-label">Kehadiran</label>
                                <select v-model="rsvpForm.attending" class="tr-form-input">
                                    <option value="yes">Hadir</option>
                                    <option value="no">Tidak Hadir</option>
                                </select>
                            </div>
                            <button type="submit" class="tr-btn" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'Mengirim...' : 'KONFIRMASI' }}
                            </button>
                        </form>
                    </div>
                </template>

                <!-- VIII — gift (accounts) -->
                <template v-else-if="card.sectionKey === 'gift'">
                    <div class="tr-reveal__section tr-reveal__section--gift">
                        <div
                            v-for="(acc, idx) in giftAccounts"
                            :key="idx"
                            class="tr-gift-card"
                        >
                            <span class="tr-gift__bank">{{ acc.bank }}</span>
                            <span class="tr-gift__number">{{ acc.account_number }}</span>
                            <span class="tr-gift__name">a.n. {{ acc.account_name }}</span>
                            <button
                                type="button"
                                class="tr-gift__copy"
                                @click="$emit('copy-account', acc.account_number)"
                            >
                                {{ copiedAccount === acc.account_number ? 'Tersalin ✓' : 'Salin Nomor' }}
                            </button>
                        </div>
                        <p v-if="!giftAccounts.length" class="tr-reveal__empty">Belum ada rekening.</p>
                    </div>
                </template>

                <!-- IX — quote (future) -->
                <template v-else-if="card.sectionKey === 'quote'">
                    <div class="tr-reveal__section tr-reveal__section--quote">
                        <blockquote class="tr-quote">
                            <p class="tr-quote__text">{{ quoteText || sectionData.text }}</p>
                        </blockquote>
                    </div>
                </template>

                <!-- X — wishes (blessings) -->
                <template v-else-if="card.sectionKey === 'wishes'">
                    <div class="tr-reveal__section tr-reveal__section--wishes">
                        <!-- Wish list -->
                        <div class="tr-wishes__list">
                            <div v-for="(msg, idx) in localMessages.slice(0, 5)" :key="idx" class="tr-wish-item">
                                <span class="tr-wish__author">{{ msg.name ?? msg.guest_name }}</span>
                                <p class="tr-wish__text">{{ msg.message }}</p>
                            </div>
                            <p v-if="!localMessages.length" class="tr-reveal__empty">Jadilah yang pertama memberi ucapan.</p>
                        </div>
                        <!-- Submit form -->
                        <div v-if="!msgSuccess" class="tr-wishes__form-wrap">
                            <form class="tr-wishes__form" @submit.prevent="$emit('submit-message')">
                                <input v-model="msgForm.name" type="text" class="tr-form-input" placeholder="Nama Anda" required/>
                                <textarea v-model="msgForm.message" class="tr-form-input tr-form-textarea" placeholder="Ucapan Anda" rows="3" required></textarea>
                                <button type="submit" class="tr-btn" :disabled="msgSubmitting">
                                    {{ msgSubmitting ? 'Mengirim...' : 'KIRIM UCAPAN' }}
                                </button>
                            </form>
                        </div>
                        <div v-else class="tr-wishes__success">
                            <p>Ucapan terkirim! Terima kasih. &#10022;</p>
                        </div>
                    </div>
                </template>

                <!-- XI — closing -->
                <template v-else-if="card.sectionKey === 'closing'">
                    <div class="tr-reveal__section tr-reveal__section--closing">
                        <p class="tr-reveal__closing-text">{{ closingText }}</p>
                        <div class="tr-closing__names">
                            <span>{{ groomNick }} &amp; {{ brideNick }}</span>
                        </div>
                    </div>
                </template>

                <!-- XII — eternal bond (opening quote / legend) -->
                <template v-else-if="card.sectionKey === 'opening_quote'">
                    <div class="tr-reveal__section tr-reveal__section--eternal">
                        <div class="tr-eternal__content">
                            <p class="tr-eternal__text">{{ openingText || 'Bersama dalam cinta, selamanya.' }}</p>
                            <div class="tr-eternal__names">
                                <span>{{ groomNick }} &amp; {{ brideNick }}</span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Fallback -->
                <template v-else>
                    <div class="tr-reveal__section tr-reveal__section--default">
                        <p class="tr-reveal__empty">{{ card.name }}</p>
                    </div>
                </template>

            </div>
        </Transition>
    </section>
</template>

<style scoped>
.tr-reveal {
    position: relative;
    min-height: 100vh;
    background: #0F0B23;
    color: #F5E6D3;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px 16px 80px;
    overflow-x: hidden;
}

.tr-reveal__back {
    align-self: flex-start;
    background: transparent;
    color: #D4AF37;
    border: 1px solid rgba(212,175,55,0.4);
    padding: 8px 18px;
    font-family: 'IM Fell English', serif;
    font-size: 13px;
    letter-spacing: 0.1em;
    cursor: pointer;
    margin-bottom: 32px;
    transition: color 0.2s ease, border-color 0.2s ease;
    z-index: 10;
}
.tr-reveal__back:hover, .tr-reveal__back:focus-visible {
    color: #F5E6D3;
    border-color: rgba(212,175,55,0.8);
}
.tr-reveal__back:focus-visible {
    outline: 2px solid #D4AF37;
    outline-offset: 2px;
}

.tr-reveal__card-wrap {
    width: min(55vw, 320px);
    max-width: 320px;
    position: relative;
    z-index: 2;
    flex-shrink: 0;
}

.tr-reveal__content {
    width: 100%;
    max-width: 640px;
    margin-top: 32px;
    z-index: 2;
}

.tr-content-fade-enter-active { transition: opacity 0.6s ease-out, transform 0.6s ease-out; }
.tr-content-fade-enter-from   { opacity: 0; transform: translateY(16px); }

/* Section wrappers */
.tr-reveal__section {
    padding: 24px;
    background: rgba(45,27,78,0.7);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 12px;
    backdrop-filter: blur(8px);
}
.tr-reveal__empty {
    text-align: center;
    color: #9D8FB0;
    font-style: italic;
    font-size: 14px;
}

/* Opening */
.tr-reveal__opening-text {
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    font-size: clamp(15px, 2.5vw, 18px);
    line-height: 1.8;
    color: #F5E6D3;
    text-align: center;
    white-space: pre-line;
}

/* Couple */
.tr-couple-pair { display: flex; flex-direction: column; align-items: center; gap: 16px; }
.tr-couple__photo-wrap { width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 2px solid #D4AF37; }
.tr-couple__photo { width: 100%; height: 100%; object-fit: cover; }
.tr-couple__names { display: flex; align-items: center; gap: 12px; font-family: 'Cormorant Garamond', serif; font-size: clamp(20px, 4vw, 28px); color: #D4AF37; }
.tr-couple__amp { color: #9D8FB0; }
.tr-couple__parents { text-align: center; font-family: 'EB Garamond', serif; font-size: 13px; color: #9D8FB0; line-height: 1.6; }
.tr-couple__parents p { margin: 0; }

/* Countdown */
.tr-countdown { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
.tr-countdown__unit { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.tr-countdown__num { font-family: 'Cinzel Decorative', serif; font-size: clamp(28px, 6vw, 48px); color: #D4AF37; line-height: 1; }
.tr-countdown__label { font-family: 'IM Fell English', serif; font-size: 11px; color: #9D8FB0; text-transform: uppercase; letter-spacing: 0.1em; }

/* Events */
.tr-event-card { padding: 16px 0; border-bottom: 1px solid rgba(212,175,55,0.15); }
.tr-event-card:last-child { border-bottom: none; }
.tr-event__name { margin: 0 0 6px; font-family: 'Cinzel Decorative', serif; font-size: 14px; color: #D4AF37; }
.tr-event__date, .tr-event__time, .tr-event__venue, .tr-event__address { margin: 0 0 2px; font-family: 'EB Garamond', serif; font-size: 14px; color: #F5E6D3; }
.tr-event__maps { display: inline-block; margin-top: 8px; font-size: 12px; color: #D4AF37; text-decoration: none; }
.tr-event__maps:hover { text-decoration: underline; }

/* Love story */
.tr-story-item { padding: 12px 0; border-bottom: 1px solid rgba(212,175,55,0.1); }
.tr-story-item:last-child { border-bottom: none; }
.tr-story__date { font-family: 'IM Fell English', serif; font-size: 11px; color: #9D8FB0; text-transform: uppercase; letter-spacing: 0.1em; }
.tr-story__title { margin: 4px 0; font-family: 'Cormorant Garamond', serif; font-size: 16px; color: #D4AF37; }
.tr-story__desc { margin: 0; font-family: 'EB Garamond', serif; font-size: 14px; color: #F5E6D3; line-height: 1.6; }

/* Gallery */
.tr-gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.tr-gallery__img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 4px; display: block; }

/* RSVP */
.tr-rsvp__form, .tr-wishes__form { display: flex; flex-direction: column; gap: 12px; }
.tr-form-field { display: flex; flex-direction: column; gap: 4px; }
.tr-form-label { font-family: 'IM Fell English', serif; font-size: 11px; color: #9D8FB0; text-transform: uppercase; letter-spacing: 0.1em; }
.tr-form-input {
    background: rgba(15,11,35,0.8);
    border: 1px solid rgba(212,175,55,0.3);
    color: #F5E6D3;
    padding: 10px 14px;
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    border-radius: 4px;
    outline: none;
}
.tr-form-input:focus { border-color: rgba(212,175,55,0.7); }
.tr-form-textarea { resize: vertical; min-height: 80px; }
.tr-rsvp__success, .tr-wishes__success {
    text-align: center;
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: #D4AF37;
    font-size: 16px;
}

/* Gift */
.tr-gift-card {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 16px;
    background: rgba(15,11,35,0.6);
    border: 1px solid rgba(212,175,55,0.25);
    border-radius: 8px;
    margin-bottom: 12px;
}
.tr-gift__bank { font-family: 'Cinzel Decorative', serif; font-size: 14px; color: #D4AF37; }
.tr-gift__number { font-family: 'EB Garamond', serif; font-size: 22px; color: #F5E6D3; letter-spacing: 0.1em; }
.tr-gift__name { font-family: 'EB Garamond', serif; font-size: 13px; color: #9D8FB0; }
.tr-gift__copy {
    align-self: flex-start;
    background: transparent;
    color: #D4AF37;
    border: 1px solid rgba(212,175,55,0.4);
    padding: 6px 14px;
    font-family: 'IM Fell English', serif;
    font-size: 12px;
    cursor: pointer;
    margin-top: 6px;
    border-radius: 3px;
    transition: background 0.2s ease, color 0.2s ease;
}
.tr-gift__copy:hover { background: #D4AF37; color: #0F0B23; }

/* Quote */
.tr-quote { margin: 0; padding: 16px 24px; border-left: 3px solid #D4AF37; }
.tr-quote__text { font-family: 'EB Garamond', serif; font-style: italic; font-size: clamp(15px, 2.5vw, 18px); color: #F5E6D3; line-height: 1.8; margin: 0; }

/* Wishes */
.tr-wishes__list { margin-bottom: 24px; }
.tr-wish-item { padding: 10px 0; border-bottom: 1px solid rgba(212,175,55,0.1); }
.tr-wish-item:last-child { border-bottom: none; }
.tr-wish__author { font-family: 'Cinzel Decorative', serif; font-size: 12px; color: #D4AF37; }
.tr-wish__text { margin: 4px 0 0; font-family: 'EB Garamond', serif; font-size: 14px; color: #F5E6D3; line-height: 1.6; }
.tr-wishes__form-wrap { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(212,175,55,0.15); }

/* Closing */
.tr-reveal__closing-text { font-family: 'EB Garamond', serif; font-style: italic; font-size: clamp(15px, 2.5vw, 18px); line-height: 1.8; color: #F5E6D3; text-align: center; white-space: pre-line; }
.tr-closing__names { margin-top: 16px; text-align: center; font-family: 'Cormorant Garamond', serif; font-size: clamp(20px, 4vw, 28px); color: #D4AF37; }

/* Eternal */
.tr-eternal__content { text-align: center; }
.tr-eternal__text { font-family: 'EB Garamond', serif; font-style: italic; font-size: clamp(16px, 3vw, 20px); color: #F5E6D3; line-height: 1.8; white-space: pre-line; }
.tr-eternal__names { margin-top: 16px; font-family: 'Cormorant Garamond', serif; font-size: clamp(22px, 4vw, 32px); color: #D4AF37; }

/* Shared button */
.tr-btn {
    padding: 12px 28px;
    background: transparent;
    color: #D4AF37;
    font-family: 'Cinzel Decorative', serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.28em;
    border: 1px solid #D4AF37;
    cursor: pointer;
    transition: color 0.2s ease, background 0.2s ease;
    align-self: flex-start;
}
.tr-btn:hover, .tr-btn:focus-visible { background: #D4AF37; color: #0F0B23; }
.tr-btn:disabled { opacity: 0.6; cursor: not-allowed; }

@media (prefers-reduced-motion: reduce) {
    .tr-content-fade-enter-active { transition: none; }
    .tr-content-fade-enter-from   { opacity: 1; transform: none; }
}
@media (max-width: 600px) {
    .tr-reveal { padding: 16px 12px 64px; }
    .tr-reveal__card-wrap { width: min(72vw, 280px); }
    .tr-gallery-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
