<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/islamic-geometric-design.md before editing -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import IsgOpening      from './islamic-geometric/IsgOpening.vue'
import IsgCover        from './islamic-geometric/IsgCover.vue'
import IsgHero         from './islamic-geometric/IsgHero.vue'
import IsgCartouche    from './islamic-geometric/IsgCartouche.vue'
import IsgKhatam       from './islamic-geometric/IsgKhatam.vue'
import IsgArabesqueBg  from './islamic-geometric/IsgArabesqueBg.vue'
import IsgKhattName    from './islamic-geometric/IsgKhattName.vue'
import TheDayLogo      from './netflix/TheDayLogo.vue'

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
    fontTitle, fontBody,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'isg-visible',
})

const cfg              = computed(() => props.invitation.config ?? {})
const coupleArabicRaw  = computed(() => cfg.value.isg_couple_arabic?.trim() || '')
const arabicParts      = computed(() => {
    if (!coupleArabicRaw.value) return null
    return coupleArabicRaw.value.split(/\s*[&و]\s*|\s+dan\s+/i)
        .map(s => s.trim()).filter(s => s.length > 0)
})
const hasArabic        = computed(() => arabicParts.value && arabicParts.value.length === 2)
const patternDensity   = computed(() => cfg.value.isg_pattern_density ?? 'medium')
const quoteDefault     = computed(() => cfg.value.isg_quote_default ?? 'ar-rum-21')
const giftInfaq        = computed(() => cfg.value.isg_gift_infaq ?? false)
const showMusic        = computed(() => cfg.value.isg_show_music ?? false)
const closingDoa       = computed(() => cfg.value.isg_closing_doa ?? 'default')
const dominantEvent    = computed(() => cfg.value.isg_dominant_event ?? 'akad')

// Quote constants (exact Unicode per spec Appendix)
const QUOTE_DEFAULTS = {
    'ar-rum-21': {
        arabic: 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ',
        translation: 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antara kamu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir.',
        source: 'QS. AR-RUM (30): 21',
    },
    'adh-dhariyat-49': {
        arabic: 'وَمِن كُلِّ شَيْءٍ خَلَقْنَا زَوْجَيْنِ لَعَلَّكُمْ تَذَكَّرُونَ',
        translation: 'Dan segala sesuatu Kami ciptakan berpasang-pasangan, agar kamu mengingat (kebesaran Allah).',
        source: 'QS. ADH-DHARIYAT (51): 49',
    },
    'an-nisa-1': {
        arabic: 'يَا أَيُّهَا النَّاسُ اتَّقُوا رَبَّكُمُ الَّذِي خَلَقَكُم مِّن نَّفْسٍ وَاحِدَةٍ وَخَلَقَ مِنْهَا زَوْجَهَا وَبَثَّ مِنْهُمَا رِجَالًا كَثِيرًا وَنِسَاءً',
        translation: 'Wahai manusia! Bertakwalah kepada Tuhanmu yang telah menciptakan kamu dari diri yang satu (Adam), dan (Allah) menciptakan pasangannya (Hawa) dari (diri)-nya; dan dari keduanya Allah memperkembangbiakkan laki-laki dan perempuan yang banyak.',
        source: 'QS. AN-NISA (4): 1',
    },
    'custom': { arabic: '', translation: '', source: '' },
}
const quoteArabic      = computed(() => sectionData('quote').arabic || QUOTE_DEFAULTS[quoteDefault.value]?.arabic || QUOTE_DEFAULTS['ar-rum-21'].arabic)
const quoteTranslation = computed(() => sectionData('quote').text   || QUOTE_DEFAULTS[quoteDefault.value]?.translation || QUOTE_DEFAULTS['ar-rum-21'].translation)
const quoteSource      = computed(() => sectionData('quote').source || QUOTE_DEFAULTS[quoteDefault.value]?.source      || QUOTE_DEFAULTS['ar-rum-21'].source)

// Closing doa constants
const DOA_DEFAULTS = {
    default: {
        arabic: 'بَارَكَ اللَّهُ لَكُمَا وَبَارَكَ عَلَيْكُمَا وَجَمَعَ بَيْنَكُمَا فِي خَيْر',
        translation: 'Semoga Allah memberkahi kalian berdua, dan memberkahi atas kalian, dan mempertemukan kalian dalam kebaikan.',
    },
    simple: {
        arabic: 'وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ',
        translation: 'Dan keselamatan, rahmat Allah, serta keberkahan-Nya semoga tercurah kepada kalian.',
    },
}
const closingDoaArabic = computed(() => DOA_DEFAULTS[closingDoa.value]?.arabic      || DOA_DEFAULTS.default.arabic)
const closingDoaTrans  = computed(() => DOA_DEFAULTS[closingDoa.value]?.translation || DOA_DEFAULTS.default.translation)

// Phase
const phase = ref(props.autoOpen ? 'content' : 'opening')
function onOpeningDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (showMusic.value && props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Couple data
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

const fullDate  = computed(() => firstEventDate.value ?? '')
const venueName = computed(() => firstEvent.value?.venue_name ?? firstEvent.value?.location ?? '')

// Sort events - akad first if dominantEvent === 'akad'
const sortedEvents = computed(() => {
    if (dominantEvent.value !== 'akad') return events.value
    return [...events.value].sort((a, b) => {
        const aIsAkad = /akad/i.test(a.event_name ?? '')
        const bIsAkad = /akad/i.test(b.event_name ?? '')
        if (aIsAkad && !bIsAkad) return -1
        if (!aIsAkad && bIsAkad) return 1
        return 0
    })
})

const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

// Load Google Fonts once
onMounted(() => {
    if (typeof document === 'undefined') return
    if (document.querySelector('link[data-isg-fonts="1"]')) return

    const pre1 = document.createElement('link')
    pre1.rel = 'preconnect'; pre1.href = 'https://fonts.googleapis.com'
    document.head.appendChild(pre1)

    const pre2 = document.createElement('link')
    pre2.rel = 'preconnect'; pre2.href = 'https://fonts.gstatic.com'; pre2.crossOrigin = 'anonymous'
    document.head.appendChild(pre2)

    const link = document.createElement('link')
    link.rel  = 'stylesheet'
    link.href = 'https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade+New:wght@400;700&family=Reem+Kufi:wght@400;500;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Inter:wght@300;400;500&display=swap'
    link.setAttribute('data-isg-fonts', '1')
    document.head.appendChild(link)
})
</script>

<template>
    <div class="isg-root">
        <audio
            v-if="showMusic && invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="isg-phase" mode="out-in">
            <IsgOpening
                v-if="phase === 'opening'"
                key="opening"
                @proceed="onOpeningDone"
            />
            <IsgCover
                v-else-if="phase === 'cover'"
                key="cover"
                :groom-name="groomName"
                :bride-name="brideName"
                :has-arabic="hasArabic"
                :arabic-parts="arabicParts"
                :full-date="fullDate"
                :venue-name="venueName"
                @open="onCoverOpen"
            />
            <div v-else key="content" class="isg-content">
                <IsgHero
                    v-if="sectionEnabled('opening')"
                    class="isg-reveal"
                    :ref="el => vReveal(el)"
                    :opening-text="openingText"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="isg-section isg-couple isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-ar" dir="rtl" lang="ar">&#x0627;&#x0644;&#x0639;&#x064E;&#x0631;&#x064F;&#x0648;&#x0633; &#x0648;&#x064E;&#x0627;&#x0644;&#x0639;&#x064E;&#x0631;&#x0650;&#x064A;&#x0633;</h2>
                    <p class="isg-section-label">MEMPELAI</p>

                    <div class="isg-couple-block">
                        <p class="isg-person-eyebrow">MEMPELAI PRIA</p>
                        <h3 class="isg-person-name">{{ groomName }}</h3>
                        <IsgKhattName v-if="hasArabic && arabicParts" :text="arabicParts[0]" :size="22" />
                        <p v-if="groomParents" class="isg-person-parents">{{ groomParents }}</p>
                    </div>

                    <IsgKhatam :size="48" class="isg-couple-divider" />

                    <div class="isg-couple-block">
                        <p class="isg-person-eyebrow">MEMPELAI WANITA</p>
                        <h3 class="isg-person-name">{{ brideName }}</h3>
                        <IsgKhattName v-if="hasArabic && arabicParts" :text="arabicParts[1]" :size="22" />
                        <p v-if="brideParents" class="isg-person-parents">{{ brideParents }}</p>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote')"
                    class="isg-section isg-quote isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <IsgKhatam :size="40" class="isg-section-orn" />
                    <p class="isg-section-label">FIRMAN ALLAH SWT</p>
                    <p class="isg-quote-ar" dir="rtl" lang="ar">{{ quoteArabic }}</p>
                    <span class="isg-divider"></span>
                    <p class="isg-quote-trans">{{ quoteTranslation }}</p>
                    <p v-if="quoteSource" class="isg-quote-source">{{ quoteSource }}</p>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="isg-section isg-love isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <p class="isg-section-label">PERJALANAN KAMI</p>
                    <ol class="isg-timeline">
                        <li
                            v-for="(story, idx) in loveStories"
                            :key="story.date ?? idx"
                            class="isg-timeline-item"
                        >
                            <p v-if="story.date" class="isg-timeline-date">{{ story.date }}</p>
                            <p class="isg-timeline-title">{{ story.title }}</p>
                            <p class="isg-timeline-desc">{{ story.description }}</p>
                        </li>
                    </ol>
                </section>

                <!-- Section `gallery` DROPPED per spec - no render block for halal-wedding no-photo. -->

                <section
                    v-if="sectionEnabled('events') && sortedEvents.length"
                    class="isg-section isg-events isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-ar" dir="rtl" lang="ar">&#x0627;&#x0644;&#x062D;&#x064E;&#x0641;&#x0652;&#x0644;</h2>
                    <p class="isg-section-label">RANGKAIAN ACARA</p>
                    <div
                        v-for="(event, idx) in sortedEvents"
                        :key="event.id ?? event.event_name"
                        class="isg-event-card"
                        :class="{ 'isg-event--akad': idx === 0 && dominantEvent === 'akad' && /akad/i.test(event.event_name ?? '') }"
                    >
                        <IsgKhatam
                            v-if="idx === 0 && dominantEvent === 'akad' && /akad/i.test(event.event_name ?? '')"
                            :size="16"
                            class="isg-event-orn"
                        />
                        <p class="isg-event-name">{{ event.event_name }}</p>
                        <p class="isg-event-date">{{ event.event_date_formatted }}</p>
                        <p class="isg-event-time">
                            <span v-if="event.start_time">{{ event.start_time }}</span>
                            <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                            <span v-if="event.timezone"> &middot; {{ event.timezone }}</span>
                        </p>
                        <p v-if="event.venue_name || event.location" class="isg-event-venue">
                            {{ event.venue_name ?? event.location }}
                        </p>
                        <a
                            v-if="event.maps_url"
                            :href="event.maps_url" target="_blank" rel="noopener"
                            class="isg-btn"
                        >LIHAT GOOGLE MAPS</a>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="isg-section isg-countdown isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <p class="isg-section-label">MENUJU HARI BARAKAH</p>
                    <div class="isg-cd-grid">
                        <div class="isg-cd-unit">
                            <Transition name="isg-flip" mode="out-in">
                                <span :key="countdown.days" class="isg-cd-num">{{ pad(countdown.days) }}</span>
                            </Transition>
                            <span class="isg-cd-label">HARI</span>
                        </div>
                        <div class="isg-cd-unit">
                            <Transition name="isg-flip" mode="out-in">
                                <span :key="countdown.hours" class="isg-cd-num">{{ pad(countdown.hours) }}</span>
                            </Transition>
                            <span class="isg-cd-label">JAM</span>
                        </div>
                        <div class="isg-cd-unit">
                            <Transition name="isg-flip" mode="out-in">
                                <span :key="countdown.minutes" class="isg-cd-num">{{ pad(countdown.minutes) }}</span>
                            </Transition>
                            <span class="isg-cd-label">MENIT</span>
                        </div>
                        <div class="isg-cd-unit">
                            <Transition name="isg-flip" mode="out-in">
                                <span :key="countdown.seconds" class="isg-cd-num">{{ pad(countdown.seconds) }}</span>
                            </Transition>
                            <span class="isg-cd-label">DETIK</span>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('rsvp')"
                    class="isg-section isg-rsvp isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-title">KONFIRMASI KEHADIRAN</h2>
                    <form class="isg-form" @submit.prevent="submitRsvp">
                        <input v-model="rsvpForm.guest_name" class="isg-input" placeholder="Nama lengkap" required />
                        <select v-model="rsvpForm.attendance" class="isg-input" required>
                            <option value="">Konfirmasi kehadiran</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                        </select>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="isg-input" placeholder="Jumlah tamu" />
                        <textarea v-model="rsvpForm.notes" class="isg-input isg-textarea" placeholder="Catatan (opsional)"/>
                        <p v-if="rsvpError" class="isg-error">{{ rsvpError }}</p>
                        <div v-if="rsvpSuccess" class="isg-rsvp-success">
                            <p class="isg-rsvp-success-ar" dir="rtl" lang="ar">&#x062C;&#x064E;&#x0632;&#x064E;&#x0627;&#x0643;&#x064E; &#x0627;&#x0644;&#x0644;&#x0651;&#x064E;&#x0647;&#x064F; &#x062E;&#x064E;&#x064A;&#x0652;&#x0631;&#x064B;&#x0627;</p>
                            <p class="isg-rsvp-success-trans">Terima kasih, semoga Allah membalas kebaikan Anda.</p>
                        </div>
                        <button type="submit" class="isg-btn isg-btn--filled" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                        </button>
                    </form>
                </section>

                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="isg-section isg-gift isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-title">HADIAH &amp; AMPLOP DIGITAL</h2>
                    <p class="isg-section-sub">Doa restu Anda adalah hadiah yang paling berharga. Bagi yang berkenan memberi tanda kasih, dapat melalui:</p>
                    <div
                        v-for="acc in giftAccounts"
                        :key="acc.account_number"
                        class="isg-account-card"
                    >
                        <p class="isg-account-bank">{{ acc.bank }}</p>
                        <p class="isg-account-name">{{ acc.account_name }}</p>
                        <p class="isg-account-num">{{ acc.account_number }}</p>
                        <button class="isg-btn" @click="copyToClipboard(acc.account_number)">
                            {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                        </button>
                    </div>
                    <p v-if="giftInfaq" class="isg-infaq-note">
                        Bagi yang berkenan, infaq dapat disalurkan via rekening yang sama dengan keterangan &ldquo;INFAQ&rdquo;.
                    </p>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="isg-section isg-wishes isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-title">DOA &amp; UCAPAN</h2>
                    <form class="isg-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name" class="isg-input" placeholder="Nama" required />
                        <textarea v-model="msgForm.message" class="isg-input isg-textarea" placeholder="Tulis doa dan ucapan..." required />
                        <p v-if="msgError"   class="isg-error">{{ msgError }}</p>
                        <p v-if="msgSuccess" class="isg-success">Doa terkirim.</p>
                        <button type="submit" class="isg-btn isg-btn--filled" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM DOA' }}
                        </button>
                    </form>
                    <p v-if="!localMessages.length" class="isg-empty">Jadilah yang pertama mengirimkan doa restu.</p>
                    <div
                        v-for="msg in localMessages"
                        :key="msg.id ?? msg.name"
                        class="isg-wish-item"
                    >
                        <p class="isg-wish-name">{{ msg.name }}</p>
                        <p class="isg-wish-msg">{{ msg.message }}</p>
                    </div>
                </section>

                <!-- Music: only render floating control if isg_show_music=true AND user uploaded audio. -->
                <button
                    v-if="showMusic && sectionEnabled('music') && invitation.music?.file_url"
                    class="isg-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 18V5l12-2v13" />
                        <circle cx="6" cy="18" r="3" />
                        <circle cx="18" cy="16" r="3" />
                        <path v-if="!musicPlaying" d="M3 3l18 18" />
                    </svg>
                </button>

                <section
                    v-if="sectionEnabled('closing')"
                    class="isg-section isg-closing isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <IsgKhatam :size="96" class="isg-section-orn" />
                    <h2 class="isg-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                    <IsgKhattName
                        v-if="hasArabic && arabicParts"
                        :text="arabicParts[0] + ' و ' + arabicParts[1]"
                        :size="22"
                    />
                    <span class="isg-divider"></span>
                    <p v-if="closingText" class="isg-closing-text">{{ closingText }}</p>
                    <p class="isg-closing-doa-ar" dir="rtl" lang="ar">{{ closingDoaArabic }}</p>
                    <p class="isg-closing-doa-trans">{{ closingDoaTrans }}</p>
                    <TheDayLogo v-if="showWatermark" class="isg-watermark" :height="18" muted />
                </section>

                <Transition name="isg-toast">
                    <div v-if="toastVisible" class="isg-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.isg-root {
    --isg-emerald:        #0e4d3d;
    --isg-emerald-light:  #6b8e7f;
    --isg-emerald-deep:   #0a2820;
    --isg-ivory:          #f5efe3;
    --isg-ivory-warm:     #ede4d2;
    --isg-ink:            #0a0a0a;
    --isg-ink-muted:      #6b6b6b;
    --isg-gold:           #c9a961;
    --isg-gold-warm:      #d4b77a;
    --isg-gold-deep:      #a88940;
    --isg-pattern-stroke: rgba(14,77,61,0.12);
    background: var(--isg-ivory);
    color: var(--isg-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.isg-content { position: relative; }

/* Phase transition */
.isg-phase-enter-active, .isg-phase-leave-active { transition: opacity 0.5s ease; }
.isg-phase-enter-from, .isg-phase-leave-to { opacity: 0; }

/* Section frame */
.isg-section {
    position: relative;
    padding: 64px 24px;
    text-align: center;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 768px) { .isg-section { padding: 96px 56px; } }

.isg-section-orn { color: var(--isg-gold); display: block; margin: 0 auto 16px; }
.isg-section-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-gold);
    margin: 0 0 24px;
}
.isg-section-title {
    font-family: 'Reem Kufi', sans-serif;
    font-size: 28px;
    color: var(--isg-emerald);
    margin: 0 0 16px;
}
.isg-section-ar {
    font-family: 'Reem Kufi', 'Amiri', serif;
    font-size: 28px;
    color: var(--isg-emerald);
    direction: rtl;
    margin: 0 0 4px;
    line-height: 1.4;
}
.isg-section-sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--isg-ink-muted);
    margin: 0 0 32px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}
.isg-divider {
    display: inline-block;
    width: 60px;
    height: 1px;
    background: var(--isg-gold);
    margin: 16px 0;
}

/* Reveal */
.isg-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.isg-reveal.isg-visible { opacity: 1; transform: none; }

/* Couple */
.isg-couple-block {
    margin: 24px 0;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.isg-couple-divider {
    color: var(--isg-gold);
    display: block;
    margin: 24px auto;
}
.isg-person-eyebrow {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-ink-muted);
    margin: 0;
}
.isg-person-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: var(--isg-ink);
    margin: 0;
}
.isg-person-parents {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted);
    max-width: 360px;
    margin: 0;
}

/* Quote */
.isg-quote { padding-top: 96px; padding-bottom: 96px; max-width: 640px; }
.isg-quote-ar {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: clamp(20px, 4vw, 24px);
    color: var(--isg-emerald);
    direction: rtl;
    line-height: 2;
    margin: 0 0 16px;
}
.isg-quote-trans {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--isg-ink);
    line-height: 1.6;
    margin: 8px 0;
}
.isg-quote-source {
    font-family: 'Cormorant Garamond', serif;
    font-size: 13px;
    color: var(--isg-gold);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 8px 0 0;
}

/* Love story timeline */
.isg-timeline { list-style: none; padding: 0; margin: 0; border-left: 1px solid var(--isg-emerald-light); text-align: left; max-width: 560px; margin-left: auto; margin-right: auto; }
.isg-timeline-item { padding: 0 0 32px 24px; position: relative; }
.isg-timeline-date {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-gold);
    margin: 0 0 4px;
}
.isg-timeline-title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 22px;
    color: var(--isg-emerald);
    margin: 0 0 8px;
}
.isg-timeline-desc {
    font-family: 'Cormorant Garamond', serif;
    font-size: 15px;
    color: var(--isg-ink-muted);
    line-height: 1.7;
    margin: 0;
}

/* Events */
.isg-event-card {
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-gold);
    padding: 28px;
    margin-bottom: 16px;
    text-align: center;
    display: flex; flex-direction: column; gap: 6px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    border-radius: 2px;
}
.isg-event--akad {
    border: 2px solid var(--isg-emerald);
}
.isg-event-orn {
    position: absolute;
    top: 12px;
    right: 12px;
    color: var(--isg-gold);
}
.isg-event-name {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--isg-gold);
    margin: 0;
}
.isg-event-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: var(--isg-ink);
    margin: 0;
}
.isg-event-time {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--isg-ink);
    margin: 0;
}
.isg-event-venue {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: var(--isg-ink-muted);
    margin: 0;
}

/* Countdown */
.isg-cd-grid { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
.isg-cd-unit {
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-gold);
    width: 72px; height: 88px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
    border-radius: 2px;
}
.isg-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--isg-emerald);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.isg-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--isg-ink-muted);
    font-size: 10px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
}
.isg-flip-enter-active, .isg-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.isg-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.isg-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

/* Forms */
.isg-form { display: flex; flex-direction: column; gap: 16px; max-width: 480px; margin: 0 auto; }
.isg-input {
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-emerald-light);
    color: var(--isg-ink);
    padding: 14px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 0;
}
.isg-input::placeholder { color: var(--isg-ink-muted); }
.isg-input:focus { border-color: var(--isg-emerald); }
.isg-textarea { min-height: 100px; resize: vertical; }
.isg-error   { color: #b3261e; font-size: 14px; margin: 0; }
.isg-success { color: #1e7a30; font-size: 14px; margin: 0; }
.isg-rsvp-success { text-align: center; margin: 8px 0; }
.isg-rsvp-success-ar {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: var(--isg-emerald);
    direction: rtl;
    margin: 0 0 4px;
}
.isg-rsvp-success-trans {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--isg-ink-muted);
    margin: 0;
}

/* Button */
.isg-btn {
    display: inline-block;
    padding: 14px 32px;
    background: transparent;
    color: var(--isg-emerald);
    border: 1px solid var(--isg-gold);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 200ms ease-out, color 200ms ease-out, border-color 200ms ease-out;
}
.isg-btn:hover { background: var(--isg-emerald); color: var(--isg-ivory); border-color: var(--isg-emerald); }
.isg-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.isg-btn:focus-visible { outline: 2px solid var(--isg-gold); outline-offset: 2px; }
.isg-btn--filled { background: var(--isg-emerald); color: var(--isg-ivory); border-color: var(--isg-emerald); }
.isg-btn--filled:hover { background: var(--isg-emerald-deep); }

/* Gift accounts */
.isg-account-card {
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-gold);
    padding: 24px;
    margin-bottom: 16px;
    max-width: 420px;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    display: flex; flex-direction: column; gap: 6px;
}
.isg-account-bank {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-ink-muted);
    margin: 0;
}
.isg-account-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 22px;
    color: var(--isg-emerald);
    margin: 0;
}
.isg-account-num {
    font-family: 'Inter', sans-serif;
    font-size: 18px;
    color: var(--isg-ink);
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.isg-infaq-note {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted);
    margin: 16px auto 0;
    max-width: 480px;
}

/* Wishes */
.isg-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--isg-ink-muted);
    text-align: center;
    margin: 24px 0 0;
}
.isg-wish-item {
    padding: 16px 0;
    border-top: 1px solid var(--isg-gold);
    text-align: left;
    max-width: 560px;
    margin: 0 auto;
}
.isg-wish-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--isg-emerald);
    margin: 0 0 4px;
}
.isg-wish-msg {
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    color: var(--isg-ink);
    line-height: 1.7;
    margin: 0;
}

/* Floating music */
.isg-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 36px; height: 36px;
    background: var(--isg-ivory);
    border: 1px solid var(--isg-gold);
    border-radius: 50%;
    color: var(--isg-emerald);
    cursor: pointer;
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
}

/* Closing */
.isg-closing { text-align: center; padding: 96px 24px; max-width: 480px; }
.isg-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 24px;
    color: var(--isg-emerald);
    margin: 16px 0 0;
}
.isg-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--isg-ink);
    font-size: 16px;
    line-height: 1.7;
    margin: 16px auto 16px;
}
.isg-closing-doa-ar {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: var(--isg-emerald);
    direction: rtl;
    line-height: 1.8;
    margin: 16px auto 8px;
}
.isg-closing-doa-trans {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted);
    margin: 0;
}
.isg-watermark {
    color: var(--isg-gold);
    opacity: 0.6;
    margin: 48px auto 0;
    display: block;
}

/* Toast */
.isg-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-gold);
    color: var(--isg-ink);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.isg-toast-enter-active, .isg-toast-leave-active { transition: opacity 0.3s; }
.isg-toast-enter-from, .isg-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .isg-reveal { opacity: 1; transform: none; transition: none; }
    .isg-phase-enter-active, .isg-phase-leave-active { transition: none; }
    .isg-flip-enter-active, .isg-flip-leave-active { transition: none; }
    .isg-flip-enter-from, .isg-flip-leave-to { transform: none; opacity: 1; }
    .isg-btn { transition: none; }
}
</style>
