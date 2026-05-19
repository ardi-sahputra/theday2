<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/ayat-hadits-design.md before editing -->
<!-- This template is NO-PHOTO + TEXT-FIRST by design. groom_photo_url/bride_photo_url and galleries[] are intentionally NOT rendered. -->
<!-- DIFFERENTIATION: must visually diverge from Islamic Geometric — NO geometric pattern, NO mandala, NO khatam star, NO 8-fold rosette. -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AhScroll       from './ayat-hadits/AhScroll.vue'
import AhCover        from './ayat-hadits/AhCover.vue'
import AhHero         from './ayat-hadits/AhHero.vue'
import AhCartouche    from './ayat-hadits/AhCartouche.vue'
import AhParchmentBg  from './ayat-hadits/AhParchmentBg.vue'
import AhCalligraphy  from './ayat-hadits/AhCalligraphy.vue'
import AhHaditsCard   from './ayat-hadits/AhHaditsCard.vue'
import TheDayLogo     from './netflix/TheDayLogo.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
    details, events, galleries,
    openingText, closingText,
    firstEvent, firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'vertical',
    openingStyle:  'fade',
    revealClass:   'ah-visible',
})

const cfg = computed(() => props.invitation.config ?? {})
const showArabicNames    = computed(() => cfg.value.ah_show_arabic_names   ?? false)
const arabicGroom        = computed(() => cfg.value.ah_couple_arabic_groom ?? '')
const arabicBride        = computed(() => cfg.value.ah_couple_arabic_bride ?? '')
const heroAyatKey        = computed(() => cfg.value.ah_hero_ayat_key       ?? 'ar-rum-21')
const defaultHaditsKey   = computed(() => cfg.value.ah_default_hadits_key  ?? 'bukhari-marriage')
const agingIntensity     = computed(() => cfg.value.ah_aging_intensity     ?? 'medium')
const cartoucheStyle     = computed(() => cfg.value.ah_cartouche_style     ?? 'ottoman')
const includeDoaPenutup  = computed(() => cfg.value.ah_include_doa_penutup ?? true)
const giftInfaqEnabled   = computed(() => cfg.value.ah_gift_infaq_enabled  ?? false)
const giftInfaqText      = computed(() => cfg.value.ah_gift_infaq_text     ?? '')
const openingLabel       = computed(() => cfg.value.ah_opening_label       ?? 'PEMBUKAAN')

// Ayat catalog (v1: Ar-Rum 21 only — exact Unicode from spec, verified bit-by-bit against quran.com/30/21)
const ayatCatalog = {
    'ar-rum-21': {
        arabic: 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ',
        transliteration: "Wa min āyātihī an khalaqa lakum min anfusikum azwājan litaskunū ilaihā wa ja'ala bainakum mawaddatan wa raḥmah. Inna fī żālika la-āyātil liqaumin yatafakkarūn.",
        translation_id: 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya. Dan Dia menjadikan di antaramu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir.',
        source: 'QS. Ar-Rum: 21',
    },
}
const heroAyat = computed(() => ayatCatalog[heroAyatKey.value] ?? ayatCatalog['ar-rum-21'])

// Hadits catalog (v1: Bukhari 5063 marriage — exact Unicode from spec, verified against sunnah.com)
const haditsCatalog = {
    'bukhari-marriage': {
        source:          'Shahih al-Bukhari, no. 5063',
        sanad:           "Imam al-Bukhari meriwayatkan dari Anas bin Mālik radhiyallāhu 'anhu.",
        matn_arabic:     'عَنْ أَنَسِ بْنِ مَالِكٍ رَضِيَ اللَّهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: «النِّكَاحُ سُنَّتِي، فَمَنْ رَغِبَ عَنْ سُنَّتِي فَلَيْسَ مِنِّي»',
        transliteration: "'An Anas bin Mālik raḍiyallāhu 'anhu qāla: qāla Rasūlullāhi ṣallallāhu 'alaihi wa sallam: \"An-nikāḥu sunnatī, faman raghiba 'an sunnatī falaisa minnī.\"",
        translation_id:  "Dari Anas bin Mālik radhiyallāhu 'anhu, ia berkata: Rasulullah \u{FD3F}saw\u{FD3E} bersabda: \"Nikah adalah sunnahku, barangsiapa enggan dari sunnahku, maka ia bukan dari golonganku.\"",
        attribution:     'HR. al-Bukhari',
    },
}
const defaultHadits = computed(() => haditsCatalog[defaultHaditsKey.value] ?? haditsCatalog['bukhari-marriage'])

// Doa Pengantin (closing) — HR. Abu Dawud 2130 / Tirmidzi 1091
const doaPenutup = {
    arabic:          'بَارَكَ اللَّهُ لَكَ وَبَارَكَ عَلَيْكَ وَجَمَعَ بَيْنَكُمَا فِي خَيْرٍ',
    transliteration: "Bārakallāhu laka wa bāraka 'alaika wa jama'a bainakumā fī khair.",
    translation_id:  'Semoga Allah memberkahimu, memberkahi atasmu, dan mempersatukan kalian berdua dalam kebaikan.',
    source:          'HR. Abu Dawud, Tirmidzi',
}

// Phase
const phase = ref(props.autoOpen ? 'content' : 'scroll')
function onScrollOpen() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])

// Akad event detection (per spec: regex /akad/i)
const akadEvent = computed(() =>
    events.value.find(e => /akad/i.test(e.event_name ?? '')) ?? events.value[0] ?? null
)
const otherEvents = computed(() =>
    events.value.filter(e => e !== akadEvent.value)
)

const customQuote = computed(() => sectionData('quote').text ?? '')

const isSubscribed = computed(() => !!props.invitation.user?.activeSubscription)

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>

<template>
    <div class="ah-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="ah-phase" mode="out-in">
            <AhScroll
                v-if="phase === 'scroll'"
                key="scroll"
                :guest-name="guestName"
                :hero-ayat="heroAyat"
                :aging-intensity="agingIntensity"
                @proceed="onScrollOpen"
            />
            <AhCover
                v-else-if="phase === 'cover'"
                key="cover"
                :groom-name="groomName"
                :bride-name="brideName"
                :arabic-groom="arabicGroom"
                :arabic-bride="arabicBride"
                :show-arabic-names="showArabicNames"
                :first-event="firstEvent"
                :first-event-date="firstEventDate"
                :cartouche-style="cartoucheStyle"
                :music-enabled="sectionEnabled('music') && !!invitation.music?.file_url"
                :music-playing="musicPlaying"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="ah-content">
                <AhHero
                    v-if="sectionEnabled('opening')"
                    :opening-text="openingText"
                    :opening-label="openingLabel"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="ah-section ah-couple ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <h2 class="ah-section-title">MEMPELAI</h2>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <AhCartouche :cartouche-style="cartoucheStyle" :width="320" :height="440">
                            <div class="ah-couple__block">
                                <p class="ah-couple__name">{{ groomName }}</p>
                                <p v-if="showArabicNames && arabicGroom" class="ah-couple__name-ar" dir="rtl">{{ arabicGroom }}</p>
                                <p class="ah-couple__rel">PUTRA DARI</p>
                                <p class="ah-couple__parents">{{ groomParents }}</p>
                            </div>
                            <span class="ah-rule ah-rule--center" aria-hidden="true"/>
                            <div class="ah-couple__block">
                                <p class="ah-couple__name">{{ brideName }}</p>
                                <p v-if="showArabicNames && arabicBride" class="ah-couple__name-ar" dir="rtl">{{ arabicBride }}</p>
                                <p class="ah-couple__rel">PUTRI DARI</p>
                                <p class="ah-couple__parents">{{ brideParents }}</p>
                            </div>
                        </AhCartouche>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="ah-section ah-events ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <h2 class="ah-section-title">WAKTU &amp; TEMPAT</h2>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>

                        <article v-if="akadEvent" class="ah-event-card ah-event-card--akad">
                            <p class="ah-event__bismillah" dir="rtl">&#1576;&#1616;&#1587;&#1618;&#1605;&#1616; &#1575;&#1604;&#1604;&#1607;&#1616; &#1575;&#1604;&#1585;&#1617;&#1614;&#1581;&#1618;&#1605;&#1614;&#1600;&#1606;&#1616; &#1575;&#1604;&#1585;&#1617;&#1614;&#1581;&#1616;&#1610;&#1618;&#1605;&#1616;</p>
                            <p class="ah-event__name ah-event__name--akad">{{ akadEvent.event_name }}</p>
                            <p class="ah-event__date">{{ akadEvent.event_date_formatted }}</p>
                            <p class="ah-event__time">
                                <span v-if="akadEvent.start_time">pukul {{ akadEvent.start_time }}</span>
                                <span v-if="akadEvent.end_time"> &ndash; {{ akadEvent.end_time }}</span>
                                <span v-if="akadEvent.timezone"> {{ akadEvent.timezone }}</span>
                            </p>
                            <span class="ah-rule ah-rule--center" aria-hidden="true"/>
                            <p v-if="akadEvent.venue_name" class="ah-event__venue">{{ akadEvent.venue_name }}</p>
                            <p v-if="akadEvent.venue_address || akadEvent.location" class="ah-event__address">
                                {{ akadEvent.venue_address ?? akadEvent.location }}
                            </p>
                            <a v-if="akadEvent.maps_url" :href="akadEvent.maps_url" target="_blank" rel="noopener" class="ah-btn ah-event__maps">BUKA DI MAPS</a>
                        </article>

                        <article
                            v-for="event in otherEvents"
                            :key="event.id ?? event.event_name"
                            class="ah-event-card"
                        >
                            <p class="ah-event__name">{{ event.event_name }}</p>
                            <p class="ah-event__date">{{ event.event_date_formatted }}</p>
                            <p class="ah-event__time">
                                <span v-if="event.start_time">pukul {{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                            </p>
                            <p v-if="event.venue_name" class="ah-event__venue">{{ event.venue_name }}</p>
                            <p v-if="event.venue_address || event.location" class="ah-event__address">
                                {{ event.venue_address ?? event.location }}
                            </p>
                            <a v-if="event.maps_url" :href="event.maps_url" target="_blank" rel="noopener" class="ah-btn ah-event__maps">BUKA DI MAPS</a>
                        </article>

                        <button
                            v-if="sectionEnabled('rsvp')"
                            class="ah-btn ah-btn--filled ah-events__cta"
                            @click="scrollToRsvp"
                        >KONFIRMASI KEHADIRAN</button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="ah-section ah-countdown ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <h2 class="ah-section-title">HITUNG MUNDUR</h2>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <div class="ah-cd-grid">
                            <div class="ah-cd-unit">
                                <Transition name="ah-fade" mode="out-in">
                                    <span :key="countdown.days" class="ah-cd-num">{{ pad(countdown.days) }}</span>
                                </Transition>
                                <span class="ah-cd-label">HARI</span>
                            </div>
                            <div class="ah-cd-unit">
                                <Transition name="ah-fade" mode="out-in">
                                    <span :key="countdown.hours" class="ah-cd-num">{{ pad(countdown.hours) }}</span>
                                </Transition>
                                <span class="ah-cd-label">JAM</span>
                            </div>
                            <div class="ah-cd-unit">
                                <Transition name="ah-fade" mode="out-in">
                                    <span :key="countdown.minutes" class="ah-cd-num">{{ pad(countdown.minutes) }}</span>
                                </Transition>
                                <span class="ah-cd-label">MENIT</span>
                            </div>
                            <div class="ah-cd-unit">
                                <Transition name="ah-fade" mode="out-in">
                                    <span :key="countdown.seconds" class="ah-cd-num">{{ pad(countdown.seconds) }}</span>
                                </Transition>
                                <span class="ah-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('love_story')"
                    class="ah-section ah-love ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <h2 class="ah-section-title">KISAH KAMI</h2>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>

                        <!-- Hadits scaffold ALWAYS renders (template identity per spec) -->
                        <AhHaditsCard :hadits="defaultHadits"/>

                        <ol v-if="loveStories.length" class="ah-timeline">
                            <li
                                v-for="(story, idx) in loveStories"
                                :key="story.date ?? idx"
                                class="ah-timeline__item"
                            >
                                <span class="ah-timeline__dot" aria-hidden="true"/>
                                <p v-if="story.date" class="ah-timeline__date">{{ story.date }}</p>
                                <p class="ah-timeline__title">{{ story.title }}</p>
                                <p class="ah-timeline__desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <!-- Gallery section: intentionally omitted in Ayat & Hadits template (no-photo religious vibe). User toggle has no visible effect; sectionEnabled check kept for catalog compliance. -->
                <template v-if="sectionEnabled('gallery')">
                    <!-- (No section block rendered — by design, see spec section "Differentiator vs Islamic Geometric") -->
                </template>

                <section
                    v-if="sectionEnabled('rsvp')"
                    id="ah-rsvp"
                    class="ah-section ah-rsvp ah-reveal"
                    :ref="setRsvpRef"
                >
                    <div class="ah-section-inner ah-narrow">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <h2 class="ah-section-title">KONFIRMASI KEHADIRAN</h2>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <form class="ah-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="ah-input" placeholder="Nama lengkap" required/>
                            <select v-model="rsvpForm.attendance" class="ah-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="ah-input" placeholder="Jumlah tamu"/>
                            <textarea v-model="rsvpForm.notes" class="ah-input ah-textarea" placeholder="Catatan (opsional)"/>
                            <p v-if="rsvpError" class="ah-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="ah-success">Jazākumullāhu khairan, kehadiran Anda kami nantikan.</p>
                            <button type="submit" class="ah-btn ah-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="ah-section ah-gift ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <h2 class="ah-section-title">HADIAH PERNIKAHAN</h2>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <p class="ah-gift__sub">Doa restu Anda adalah hadiah terindah bagi kami. Bagi yang berkenan menyalurkan tanda kasih&hellip;</p>

                        <aside v-if="giftInfaqEnabled" class="ah-gift-infaq">
                            <h3 class="ah-gift-infaq__title">Infaq Pernikahan</h3>
                            <p class="ah-gift-infaq__desc">
                                {{ giftInfaqText || 'Bagi yang berkenan menyalurkan infaq pernikahan kami, dapat dikirimkan melalui rekening di bawah ini, agar menjadi sedekah jariyah yang berkah.' }}
                            </p>
                        </aside>

                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="ah-account-card"
                        >
                            <p class="ah-account__bank">{{ acc.bank }}</p>
                            <p class="ah-account__name">{{ acc.account_name }}</p>
                            <p class="ah-account__num">{{ acc.account_number }}</p>
                            <button class="ah-btn" @click="copyToClipboard(acc.account_number, acc.bank)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="ah-section ah-wishes ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner ah-narrow">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <h2 class="ah-section-title">UCAPAN &amp; DOA</h2>
                            <span class="ah-ornament" aria-hidden="true">&#x2042;</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <p class="ah-wishes__sub"><em>Mohon doa restu agar pernikahan kami mendapatkan rahmat dan keberkahan dari Allah &#x2726;</em></p>
                        <form class="ah-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="ah-input" placeholder="Nama" required/>
                            <textarea v-model="msgForm.message" class="ah-input ah-textarea" placeholder="Tulis ucapan dan doa..." required/>
                            <p v-if="msgError" class="ah-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="ah-success">Doa Anda telah kami terima.</p>
                            <button type="submit" class="ah-btn ah-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM DOA' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="ah-empty">Jadilah yang pertama menitipkan doa untuk kami.</p>
                        <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="ah-wish-item">
                            <p class="ah-wish__name">{{ msg.name }}</p>
                            <p class="ah-wish__msg">{{ msg.message }}</p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote')"
                    class="ah-section ah-quote ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <AhCartouche :cartouche-style="cartoucheStyle" :width="480" :height="560">
                            <!-- Default: full Ar-Rum 21 (Arabic + transliteration + translation). Custom override via sectionData('quote').text -->
                            <template v-if="!customQuote">
                                <div class="ah-quote__arabic" dir="rtl">{{ heroAyat.arabic }}</div>
                                <p class="ah-quote__translit"><em>{{ heroAyat.transliteration }}</em></p>
                                <p class="ah-quote__translation">{{ heroAyat.translation_id }}</p>
                                <p class="ah-quote__source">— {{ heroAyat.source }}</p>
                            </template>
                            <template v-else>
                                <p class="ah-quote__translation">{{ customQuote }}</p>
                                <p v-if="sectionData('quote').source" class="ah-quote__source">— {{ sectionData('quote').source }}</p>
                            </template>
                        </AhCartouche>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="ah-section ah-closing ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <AhParchmentBg intensity="strong">
                        <div class="ah-section-inner ah-closing__inner">
                            <div v-if="includeDoaPenutup" class="ah-closing__doa">
                                <span class="ah-closing__ornament" aria-hidden="true">&#x2042;</span>
                                <div class="ah-closing__doa-arabic" dir="rtl">{{ doaPenutup.arabic }}</div>
                                <p class="ah-closing__doa-translit"><em>{{ doaPenutup.transliteration }}</em></p>
                                <p class="ah-closing__doa-translation">{{ doaPenutup.translation_id }}</p>
                                <p class="ah-closing__doa-source">— {{ doaPenutup.source }}</p>
                                <span class="ah-rule ah-rule--center" aria-hidden="true"/>
                            </div>
                            <h2 class="ah-closing__names">{{ groomName }} &amp; {{ brideName }}</h2>
                            <p v-if="showArabicNames && (arabicGroom || arabicBride)" class="ah-closing__names-ar" dir="rtl">
                                {{ arabicGroom }} &amp; {{ arabicBride }}
                            </p>
                            <p class="ah-closing__date">{{ firstEventDate }}</p>
                            <p v-if="closingText" class="ah-closing__text">{{ closingText }}</p>
                            <TheDayLogo v-if="!isSubscribed" class="ah-watermark" :height="20" :muted="true"/>
                        </div>
                    </AhParchmentBg>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="ah-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '&#9834;' : '&#9835;' }}</button>

                <Transition name="ah-toast">
                    <div v-if="toastVisible" class="ah-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.ah-root {
    --ah-parchment: #f4e8d0;
    --ah-parchment-light: #fbf5e3;
    --ah-parchment-shadow: #d4c4a4;
    --ah-parchment-deep: #ede0c4;
    --ah-ink: #3d2817;
    --ah-ink-soft: #6b4423;
    --ah-ink-decorative: #8b3a3a;
    --ah-gold: #c9a961;
    --ah-gold-deep: #a8893f;
    --ah-divider: rgba(107, 68, 35, 0.25);
    background: var(--ah-parchment);
    color: var(--ah-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.ah-content { position: relative; }
.ah-phase-enter-active, .ah-phase-leave-active { transition: opacity 0.6s ease; }
.ah-phase-enter-from, .ah-phase-leave-to { opacity: 0; }

.ah-section { position: relative; padding: 64px 24px; }
.ah-section-inner { max-width: 720px; margin: 0 auto; text-align: center; }
.ah-narrow { max-width: 480px; }
@media (min-width: 768px) { .ah-section { padding: 112px 56px; } }

.ah-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; margin: 0 auto 40px;
}
.ah-rule { display: block; flex: 0 0 32px; height: 1px; background: var(--ah-gold); opacity: 0.7; }
.ah-rule--center { width: 40px; margin: 16px auto; opacity: 1; flex: none; }
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

.ah-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.ah-reveal.ah-visible { opacity: 1; transform: none; }

.ah-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--ah-ink);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--ah-ink);
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 0.2s ease, color 0.2s ease;
}
.ah-btn:hover { background: var(--ah-ink); color: var(--ah-parchment); }
.ah-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.ah-btn--filled { background: var(--ah-ink); color: var(--ah-parchment); }
.ah-btn--filled:hover { background: var(--ah-ink-decorative); }

/* Couple */
.ah-couple__block { padding: 16px 0; text-align: center; }
.ah-couple__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 26px;
    margin: 0;
}
.ah-couple__name-ar {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 20px;
    margin: 6px 0 0;
    direction: rtl;
    letter-spacing: 0;
}
.ah-couple__rel {
    font-family: 'EB Garamond', serif;
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--ah-ink-soft);
    margin: 8px 0 4px;
}
.ah-couple__parents {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 14px;
    margin: 0;
    line-height: 1.6;
}

/* Events */
.ah-event-card {
    background: var(--ah-parchment-deep);
    border: 1px solid var(--ah-divider);
    padding: 28px 24px;
    margin-bottom: 16px;
    border-radius: 2px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.ah-event-card--akad {
    border-top: 3px solid var(--ah-gold);
    padding: 40px 32px;
}
.ah-event__bismillah {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 18px;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 12px;
}
.ah-event__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 22px;
    margin: 0;
}
.ah-event__name--akad { font-size: 28px; }
.ah-event__date {
    font-family: 'Cormorant Garamond', serif;
    color: var(--ah-ink);
    font-size: 28px;
    margin: 0;
}
.ah-event-card--akad .ah-event__date { font-size: 32px; }
.ah-event__time {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 16px;
    margin: 0;
}
.ah-event__venue {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 17px;
    margin: 0;
}
.ah-event__address {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink-soft);
    font-size: 14px;
    margin: 0;
    line-height: 1.5;
}
.ah-event__maps { margin-top: 8px; }
.ah-events__cta { display: block; margin: 24px auto 0; }

/* Countdown */
.ah-cd-grid { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
.ah-cd-unit {
    background: transparent;
    border: 1px solid var(--ah-divider);
    padding: 16px 12px;
    border-radius: 2px;
    width: 72px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.ah-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--ah-ink);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.ah-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.ah-fade-enter-active, .ah-fade-leave-active { transition: opacity 0.3s ease; }
.ah-fade-enter-from, .ah-fade-leave-to { opacity: 0; }

/* Love story (hadits + timeline) */
.ah-timeline { list-style: none; padding: 0; margin: 32px 0 0; text-align: left; border-left: 1px solid var(--ah-gold-deep); }
.ah-timeline__item { position: relative; padding: 0 0 24px 24px; }
.ah-timeline__dot {
    position: absolute; left: -5px; top: 6px;
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--ah-gold);
}
.ah-timeline__date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-gold-deep);
    font-size: 13px;
    margin: 0 0 4px;
}
.ah-timeline__title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 22px;
    margin: 0 0 8px;
}
.ah-timeline__desc {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Forms */
.ah-form { display: flex; flex-direction: column; gap: 14px; }
.ah-input {
    background: transparent;
    border: 1px solid var(--ah-divider);
    color: var(--ah-ink);
    padding: 12px 16px;
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 2px;
    transition: border-color 0.2s ease;
}
.ah-input::placeholder { color: var(--ah-ink-soft); }
.ah-input:focus { border-color: var(--ah-ink); }
.ah-textarea { min-height: 100px; resize: vertical; }
.ah-error { color: #b54a4a; font-size: 14px; margin: 0; }
.ah-success { color: var(--ah-ink); font-size: 14px; margin: 0; font-family: 'EB Garamond', serif; font-style: italic; }

/* Gift */
.ah-gift__sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    text-align: center;
    margin: 0 0 24px;
    font-size: 16px;
}
.ah-gift-infaq {
    background: var(--ah-parchment-light);
    border: 1px dashed var(--ah-gold);
    padding: 24px;
    margin-bottom: 24px;
    border-radius: 2px;
    text-align: center;
}
.ah-gift-infaq__title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 22px;
    margin: 0 0 8px;
}
.ah-gift-infaq__desc {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}
.ah-account-card {
    background: var(--ah-parchment-deep);
    border-top: 2px solid var(--ah-gold);
    padding: 24px;
    margin-bottom: 16px;
    border-radius: 2px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.ah-account__bank {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.ah-account__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 20px;
    margin: 0;
}
.ah-account__num {
    font-family: 'Inter', sans-serif;
    color: var(--ah-gold-deep);
    font-size: 18px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}

/* Wishes */
.ah-wishes__sub {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    text-align: center;
    margin: 0 0 24px;
    font-size: 15px;
    line-height: 1.7;
}
.ah-empty {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    text-align: center;
    margin: 24px 0 0;
    font-size: 16px;
}
.ah-wish-item { padding: 16px 0; border-top: 1px solid var(--ah-divider); text-align: left; }
.ah-wish__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 18px;
    margin: 0 0 4px;
}
.ah-wish__msg {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 15px;
    line-height: 1.8;
    margin: 0;
}

/* Quote (full Ar-Rum 21 in cartouche) */
.ah-quote { padding-top: 112px; padding-bottom: 112px; }
.ah-quote__arabic {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink);
    font-size: 28px;
    line-height: 1.95;
    text-align: center;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 24px;
}
@media (max-width: 480px) {
    .ah-quote__arabic { font-size: 22px; }
}
.ah-quote__translit {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 15px;
    line-height: 1.7;
    margin: 0 0 16px;
}
.ah-quote__translation {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 15px;
    line-height: 1.75;
    margin: 0 0 12px;
    text-align: justify;
}
.ah-quote__source {
    font-family: 'Inter', sans-serif;
    color: var(--ah-gold-deep);
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}

/* Closing */
.ah-closing { padding: 0; }
.ah-closing__inner { padding: 112px 24px; text-align: center; max-width: 640px; }
.ah-closing__doa { margin-bottom: 24px; }
.ah-closing__ornament { color: var(--ah-gold); font-size: 18px; display: block; margin-bottom: 16px; }
.ah-closing__doa-arabic {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 24px;
    line-height: 1.9;
    text-align: center;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 16px;
}
.ah-closing__doa-translit {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 14px;
    margin: 0 0 12px;
}
.ah-closing__doa-translation {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 16px;
    line-height: 1.7;
    margin: 0 0 8px;
}
.ah-closing__doa-source {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}
.ah-closing__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 32px;
    margin: 16px 0 0;
}
.ah-closing__names-ar {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 20px;
    margin: 6px 0 0;
    direction: rtl;
    letter-spacing: 0;
}
.ah-closing__date {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 8px 0 0;
}
.ah-closing__text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 16px;
    line-height: 1.7;
    margin: 16px auto 0;
    max-width: 480px;
}
.ah-watermark {
    color: var(--ah-ink-soft);
    opacity: 0.6;
    margin: 48px auto 0;
    display: block;
}

/* Floating music */
.ah-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 36px; height: 36px;
    background: var(--ah-parchment);
    border: 1px solid var(--ah-gold);
    border-radius: 50%;
    color: var(--ah-ink);
    cursor: pointer;
    z-index: 50;
    font-size: 14px;
    display: flex; align-items: center; justify-content: center;
}

/* Toast */
.ah-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--ah-parchment-deep);
    border: 1px solid var(--ah-divider);
    color: var(--ah-ink);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    border-radius: 2px;
    white-space: nowrap;
}
.ah-toast-enter-active, .ah-toast-leave-active { transition: opacity 0.3s; }
.ah-toast-enter-from, .ah-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .ah-reveal { opacity: 1; transform: none; transition: none; }
    .ah-phase-enter-active, .ah-phase-leave-active { transition: none; }
    .ah-fade-enter-active, .ah-fade-leave-active { transition: none; }
    .ah-btn { transition: none; }
}

/* Print friendly */
@media print {
    .ah-root { background: #fff; color: #000; }
    .ah-float-music, .ah-watermark, .ah-cover__music { display: none; }
}
</style>
