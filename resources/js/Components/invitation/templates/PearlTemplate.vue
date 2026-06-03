<script setup>
import { computed, onMounted } from 'vue';
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate';
import GallerySection from '@/Components/invitation/sections/GallerySection.vue';

const SECTION_BG_DEFAULTS = {
    cover:   { type: 'image', value: '/image/demo-image/bride-groom.png', opacity: 0.75 },
    opening: { type: 'color', value: '#0f0e0c' },
    events:  { type: 'color', value: '#faf9f7' },
    gallery: { type: 'color', value: '#f5f0e8' },
    closing: { type: 'image', value: '/image/demo-image/bride-groom.png', opacity: 0.6 },
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
    openingStyle:      'fade',
    revealClass:       'p-visible',
    sectionBgDefaults: SECTION_BG_DEFAULTS,
});

const guestDisplayName = computed(() =>
    props.isDemo ? 'Bapak / Ibu Tamu Undangan' : (props.guest?.name ?? null)
);

onMounted(() => {
    const fontParam = [
        'Playfair+Display:ital,wght@0,400;0,600;0,700;1,400',
        'Lato:wght@300;400;700',
        'Cormorant+Garamond:ital,wght@0,400;1,400',
    ].join('&family=');
    const link = Object.assign(document.createElement('link'), {
        rel:  'stylesheet',
        href: `https://fonts.googleapis.com/css2?family=${fontParam}&display=swap`,
    });
    document.head.appendChild(link);
});
</script>

<template>
    <div class="pearl" :style="{ background: bgColor, fontFamily: fontBody }">

        <!-- ── Audio ── -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            class="sr-only"
        />

        <!-- ══════════════════════════════════════════════════════ -->
        <!-- COVER / GATE SCREEN                                    -->
        <!-- ══════════════════════════════════════════════════════ -->
        <div
            v-if="!gateOpen"
            class="pearl-gate"
            :class="{ 'pearl-gate--sliding': gateAnimating }"
            @click="triggerGate"
        >
            <div
                v-if="sectionBg('cover') || coverPhotoUrl"
                class="pearl-gate__bg"
                :style="sectionBg('cover')
                    ? bgStyle(sectionBg('cover'))
                    : { backgroundImage: `url(${coverPhotoUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' }"
            />
            <div class="pearl-gate__overlay" />

            <div class="pearl-gate__content">
                <div class="pearl-gate__initials" :style="{ fontFamily: fontTitle }">
                    <span>{{ brideNick?.[0]?.toUpperCase() }}</span>
                    <span>{{ groomNick?.[0]?.toUpperCase() }}</span>
                </div>
                <div class="pearl-gate__bottom">
                    <template v-if="guestDisplayName">
                        <p class="pearl-gate__dear" :style="{ fontFamily: fontBody }">DEAR</p>
                        <p class="pearl-gate__guest-name" :style="{ fontFamily: fontHeading }">{{ guestDisplayName }}</p>
                    </template>
                    <button
                        class="pearl-gate__btn"
                        :style="{ fontFamily: fontHeading }"
                        :disabled="gateAnimating"
                        @click.stop="triggerGate"
                    >
                        {{ gateAnimating ? '...' : 'OPEN INVITATION' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════ -->
        <!-- MAIN CONTENT                                           -->
        <!-- ══════════════════════════════════════════════════════ -->
        <div :style="contentOpen ? {} : { opacity: 0, pointerEvents: 'none' }">

            <!-- Music float button -->
            <button
                v-if="sectionEnabled('music') && invitation.music?.file_url"
                class="pearl-music-btn"
                :style="{ background: primary }"
                @click="toggleMusic"
                aria-label="Toggle musik"
            >
                <span>{{ musicPlaying ? '❚❚' : '▶' }}</span>
            </button>

            <!-- ── Cover section (nama + foto atas) ── -->
            <section v-if="sectionEnabled('cover')" class="pearl-cover" :style="{ background: darkBg }">
                <div v-if="coverPhotoUrl" class="pearl-cover__img">
                    <img :src="coverPhotoUrl" alt="Cover" />
                </div>
                <div class="pearl-cover__text">
                    <p class="pearl-cover__sub" :style="{ fontFamily: fontBody, color: primaryLight }">THE WEDDING OF</p>
                    <h2 class="pearl-cover__names" :style="{ fontFamily: fontTitle, color: '#fff' }">
                        {{ groomNick }} &amp; {{ brideNick }}
                    </h2>
                    <p v-if="firstEventDate" :style="{ fontFamily: fontHeading, color: '#fff', opacity: 0.7 }">
                        {{ firstEventDate }}
                    </p>
                </div>
            </section>

            <!-- ── Opening / Pasangan ── -->
            <section v-if="sectionEnabled('opening')" class="pearl-opening" style="position:relative" :ref="vReveal">
                <!-- Section background overlay from editor -->
                <div
                    v-if="sectionBg('opening')"
                    class="absolute inset-0 pointer-events-none"
                    :style="bgStyle(sectionBg('opening'))"
                />
                <div class="pearl-divider-label" :style="{ fontFamily: fontBody, color: primary }">
                    <span class="pearl-line" :style="{ background: primary }"></span>
                    Wedding Ceremony
                    <span class="pearl-line" :style="{ background: primary }"></span>
                </div>

                <p class="pearl-opening__text" :style="{ fontFamily: fontHeading }">
                    {{ openingText }}
                </p>

                <div class="pearl-couple">
                    <!-- Groom -->
                    <div class="pearl-couple__person">
                        <h2 class="pearl-couple__nick" :style="{ fontFamily: fontTitle, color: primary }">
                            {{ groomNick }}
                        </h2>
                        <div class="pearl-couple__fullname" :style="{ fontFamily: fontHeading, color: accent }">
                            {{ groomName }}
                        </div>
                        <div v-if="details.groom_son_order" class="pearl-couple__order" :style="{ fontFamily: fontBody }">
                            {{ details.groom_son_order }}
                        </div>
                        <div v-if="details.groom_parent_names" class="pearl-couple__parents" :style="{ fontFamily: fontBody }">
                            <span :style="{ color: primary, fontSize: '0.75rem' }">Putra dari</span><br>
                            {{ details.groom_parent_names }}
                        </div>
                    </div>

                    <div class="pearl-couple__ampersand" :style="{ fontFamily: fontTitle, color: primary }">
                        &amp;
                    </div>

                    <!-- Bride -->
                    <div class="pearl-couple__person">
                        <h2 class="pearl-couple__nick" :style="{ fontFamily: fontTitle, color: primary }">
                            {{ brideNick }}
                        </h2>
                        <div class="pearl-couple__fullname" :style="{ fontFamily: fontHeading, color: accent }">
                            {{ brideName }}
                        </div>
                        <div v-if="details.bride_daughter_order" class="pearl-couple__order" :style="{ fontFamily: fontBody }">
                            {{ details.bride_daughter_order }}
                        </div>
                        <div v-if="details.bride_parent_names" class="pearl-couple__parents" :style="{ fontFamily: fontBody }">
                            <span :style="{ color: primary, fontSize: '0.75rem' }">Putri dari</span><br>
                            {{ details.bride_parent_names }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Quote / Ayat ── -->
            <section
                v-if="sectionEnabled('quote') && sectionData('quote').text"
                class="pearl-quote"
                :style="{ background: darkBg }"
                :ref="vReveal"
            >
                <div class="pearl-quote__mark" :style="{ color: primaryLight, fontFamily: fontTitle }">"</div>
                <blockquote class="pearl-quote__text" :style="{ fontFamily: fontHeading, color: '#fff' }">
                    {{ sectionData('quote').text }}
                </blockquote>
            </section>

            <!-- ── Save The Date / Countdown ── -->
            <section v-if="sectionEnabled('countdown') && targetDate" class="pearl-countdown" :ref="vReveal">
                <div class="pearl-section-title" :style="{ fontFamily: fontTitle, color: primary }">Save The Date</div>
                <p v-if="firstEventDate" :style="{ fontFamily: fontHeading, color: accent, marginBottom: '24px' }">
                    {{ firstEventDate }}
                </p>
                <div class="pearl-countdown__boxes">
                    <div class="pearl-countdown__box" :style="{ borderColor: primary }">
                        <span class="pearl-countdown__num" :style="{ fontFamily: fontTitle, color: primary }">{{ pad(countdown.days) }}</span>
                        <span class="pearl-countdown__lbl" :style="{ color: accent, fontFamily: fontBody }">Hari</span>
                    </div>
                    <div class="pearl-countdown__box" :style="{ borderColor: primary }">
                        <span class="pearl-countdown__num" :style="{ fontFamily: fontTitle, color: primary }">{{ pad(countdown.hours) }}</span>
                        <span class="pearl-countdown__lbl" :style="{ color: accent, fontFamily: fontBody }">Jam</span>
                    </div>
                    <div class="pearl-countdown__box" :style="{ borderColor: primary }">
                        <span class="pearl-countdown__num" :style="{ fontFamily: fontTitle, color: primary }">{{ pad(countdown.minutes) }}</span>
                        <span class="pearl-countdown__lbl" :style="{ color: accent, fontFamily: fontBody }">Menit</span>
                    </div>
                    <div class="pearl-countdown__box" :style="{ borderColor: primary }">
                        <span class="pearl-countdown__num" :style="{ fontFamily: fontTitle, color: primary }">{{ pad(countdown.seconds) }}</span>
                        <span class="pearl-countdown__lbl" :style="{ color: accent, fontFamily: fontBody }">Detik</span>
                    </div>
                </div>
            </section>

            <!-- ── Events ── -->
            <section v-if="sectionEnabled('events') && events.length" class="pearl-events" :style="{ background: darkBg, position: 'relative' }" :ref="vReveal">
                <!-- Section background overlay from editor -->
                <div
                    v-if="sectionBg('events')"
                    class="absolute inset-0 pointer-events-none"
                    :style="bgStyle(sectionBg('events'))"
                />
                <div class="pearl-section-title" :style="{ fontFamily: fontTitle, color: '#fff' }">Tanggal Pernikahan</div>
                <div class="pearl-events__grid">
                    <div
                        v-for="event in events"
                        :key="event.id"
                        class="pearl-event-card"
                        :style="{ borderColor: primaryLight }"
                    >
                        <h3 class="pearl-event-card__name" :style="{ fontFamily: fontTitle, color: primaryLight }">
                            {{ event.event_name }}
                        </h3>
                        <div class="pearl-event-card__date" :style="{ fontFamily: fontHeading, color: '#fff' }">
                            {{ event.event_date_formatted }}
                        </div>
                        <div v-if="event.start_time" class="pearl-event-card__time" :style="{ fontFamily: fontBody, color: '#fff', opacity: 0.7 }">
                            {{ event.start_time }}<span v-if="event.end_time"> – {{ event.end_time }}</span> WIB
                        </div>
                        <div class="pearl-event-card__venue" :style="{ fontFamily: fontBody, color: primaryLight }">
                            {{ event.location }}
                        </div>
                        <a
                            v-if="event.maps_url"
                            :href="event.maps_url"
                            target="_blank"
                            rel="noopener"
                            class="pearl-event-card__maps"
                            :style="{ color: primaryLight, fontFamily: fontBody }"
                        >
                            View Maps →
                        </a>
                    </div>
                </div>
            </section>

            <!-- ── Live streaming ── -->
            <section
                v-if="sectionEnabled('live_streaming') && sectionData('live_streaming').url"
                class="pearl-section-pad"
                :ref="vReveal"
            >
                <div class="pearl-section-title" :style="{ fontFamily: fontTitle, color: primary }">Live Streaming</div>
                <a
                    :href="sectionData('live_streaming').url"
                    target="_blank"
                    rel="noopener"
                    class="pearl-btn-outline"
                    :style="{ borderColor: primary, color: primary, fontFamily: fontHeading }"
                >
                    Tonton Live Streaming
                </a>
            </section>

            <!-- ── Additional info ── -->
            <section
                v-if="sectionEnabled('additional_info') && sectionData('additional_info').text"
                class="pearl-additional"
                :ref="vReveal"
            >
                <p :style="{ fontFamily: fontBody, color: accent }">{{ sectionData('additional_info').text }}</p>
            </section>

            <!-- ── Love Story ── -->
            <section
                v-if="sectionEnabled('love_story') && sectionData('love_story').stories?.length"
                class="pearl-story"
                :ref="vReveal"
            >
                <div class="pearl-section-title" :style="{ fontFamily: fontTitle, color: primary }">
                    Love Story
                </div>
                <div class="pearl-story__list">
                    <div
                        v-for="(story, i) in sectionData('love_story').stories"
                        :key="i"
                        class="pearl-story__item"
                    >
                        <div class="pearl-story__dot" :style="{ background: primary }"></div>
                        <div class="pearl-story__body">
                            <div v-if="story.date" class="pearl-story__date" :style="{ color: primary, fontFamily: fontBody }">
                                {{ story.date }}
                            </div>
                            <h4 class="pearl-story__title" :style="{ fontFamily: fontTitle, color: accent }">
                                {{ story.title }}
                            </h4>
                            <p class="pearl-story__desc" :style="{ fontFamily: fontBody }">
                                {{ story.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Gallery ── -->
            <section v-if="sectionEnabled('gallery') && galleries.length" class="pearl-gallery" style="position:relative" :ref="vReveal">
                <!-- Section background overlay from editor -->
                <div
                    v-if="sectionBg('gallery')"
                    class="absolute inset-0 pointer-events-none"
                    :style="bgStyle(sectionBg('gallery'))"
                />
                <div class="pearl-divider-label" :style="{ fontFamily: fontBody, color: primary }">
                    <span class="pearl-line" :style="{ background: primary }"></span>
                    Gallery
                    <span class="pearl-line" :style="{ background: primary }"></span>
                </div>
                <GallerySection :galleries="galleries" :layout="galleryLayout" :primary-color="'#8B6914'" />
            </section>

            <!-- ── Video embed ── -->
            <section
                v-if="sectionEnabled('video') && videoEmbedUrl(sectionData('video').url)"
                class="pearl-video"
                :ref="vReveal"
            >
                <iframe
                    :src="videoEmbedUrl(sectionData('video').url)"
                    frameborder="0"
                    allowfullscreen
                    allow="autoplay; encrypted-media"
                />
            </section>

            <!-- ── RSVP ── -->
            <section v-if="sectionEnabled('rsvp')" class="pearl-rsvp" :ref="vReveal">
                <div class="pearl-section-title" :style="{ fontFamily: fontTitle, color: primary }">Konfirmasi Kehadiran</div>
                <form class="pearl-form" @submit.prevent="submitRsvp">
                    <input
                        v-model="rsvpForm.guest_name"
                        placeholder="Nama lengkap"
                        required
                        :style="{ fontFamily: fontBody, borderColor: primary }"
                    />
                    <select
                        v-model="rsvpForm.attendance"
                        required
                        :style="{ fontFamily: fontBody, borderColor: primary }"
                    >
                        <option value="">Konfirmasi kehadiran</option>
                        <option value="hadir">Hadir</option>
                        <option value="tidak_hadir">Tidak Hadir</option>
                    </select>
                    <input
                        v-model.number="rsvpForm.guest_count"
                        type="number" min="1" max="10"
                        placeholder="Jumlah tamu"
                        :style="{ fontFamily: fontBody, borderColor: primary }"
                    />
                    <textarea
                        v-model="rsvpForm.notes"
                        placeholder="Catatan (opsional)"
                        :style="{ fontFamily: fontBody, borderColor: primary }"
                    />
                    <p v-if="rsvpError" class="pearl-form__error">{{ rsvpError }}</p>
                    <p v-if="rsvpSuccess" class="pearl-form__success">Terima kasih atas konfirmasinya!</p>
                    <button
                        type="submit"
                        :disabled="rsvpSubmitting"
                        :style="{ background: primary, fontFamily: fontHeading }"
                    >
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim RSVP' }}
                    </button>
                </form>
            </section>

            <!-- ── Gift / Rekening ── -->
            <section
                v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                class="pearl-gift"
                :style="{ background: darkBg }"
                :ref="vReveal"
            >
                <div class="pearl-section-title" :style="{ fontFamily: fontTitle, color: '#fff' }">Wedding Gift</div>
                <p class="pearl-gift__desc" :style="{ fontFamily: fontBody, color: '#fff', opacity: 0.7 }">
                    Kehadiran dan doa restu Anda sudah cukup bagi kami. Namun jika berkenan, kami menyediakan amplop digital.
                </p>
                <div class="pearl-gift__accounts">
                    <div
                        v-for="acc in sectionData('gift').accounts"
                        :key="acc.account_number"
                        class="pearl-gift__card"
                        :style="{ borderColor: primaryLight }"
                    >
                        <div class="pearl-gift__bank" :style="{ fontFamily: fontBody, color: primaryLight }">{{ acc.bank }}</div>
                        <div class="pearl-gift__name" :style="{ fontFamily: fontHeading, color: '#fff' }">{{ acc.account_name }}</div>
                        <div class="pearl-gift__number" :style="{ fontFamily: fontTitle, color: primaryLight }">{{ acc.account_number }}</div>
                        <button
                            class="pearl-gift__copy"
                            :style="{ borderColor: primaryLight, color: primaryLight, fontFamily: fontBody }"
                            @click="copyToClipboard(acc.account_number)"
                        >
                            {{ copiedAccount === acc.account_number ? 'Tersalin ✓' : 'Salin Nomor' }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- ── Wishes / Buku Tamu ── -->
            <section v-if="sectionEnabled('wishes')" class="pearl-wishes" :ref="vReveal">
                <div class="pearl-section-title" :style="{ fontFamily: fontTitle, color: primary }">Wedding Wish</div>
                <p :style="{ fontFamily: fontBody, color: accent, marginBottom: '24px' }">
                    Kirimkan doa &amp; ucapan kepada kedua mempelai
                </p>

                <form class="pearl-form" @submit.prevent="submitMessage">
                    <input
                        v-model="msgForm.name"
                        placeholder="Nama"
                        required
                        :style="{ fontFamily: fontBody, borderColor: primary }"
                    />
                    <textarea
                        v-model="msgForm.message"
                        placeholder="Ucapan &amp; doa"
                        required
                        :style="{ fontFamily: fontBody, borderColor: primary }"
                    />
                    <p v-if="msgError" class="pearl-form__error">{{ msgError }}</p>
                    <p v-if="msgSuccess" class="pearl-form__success">Ucapan terkirim!</p>
                    <button
                        type="submit"
                        :disabled="msgSubmitting"
                        :style="{ background: primary, fontFamily: fontHeading }"
                    >
                        {{ msgSubmitting ? 'Mengirim...' : 'Kirimkan Ucapan' }}
                    </button>
                </form>

                <div class="pearl-wishes__list">
                    <div
                        v-for="msg in localMessages"
                        :key="msg.id ?? msg.name"
                        class="pearl-wish-item"
                        :style="{ borderColor: bgColor === '#fff' ? '#eee' : primaryLight }"
                    >
                        <div class="pearl-wish-item__name" :style="{ fontFamily: fontHeading, color: primary }">
                            {{ msg.name }}
                        </div>
                        <p class="pearl-wish-item__msg" :style="{ fontFamily: fontBody }">{{ msg.message }}</p>
                    </div>
                </div>
            </section>

            <!-- ── Closing ── -->
            <section
                v-if="sectionEnabled('closing')"
                class="pearl-closing"
                :style="{ background: darkBg }"
            >
                <div
                    v-if="sectionBg('closing') || coverPhotoUrl"
                    class="pearl-closing__bg"
                    :style="sectionBg('closing')
                        ? bgStyle(sectionBg('closing'))
                        : { backgroundImage: `url(${coverPhotoUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' }"
                />
                <div class="pearl-closing__overlay" :style="{ background: `${darkBg}cc` }" />
                <div class="pearl-closing__content">
                    <p class="pearl-closing__sub" :style="{ fontFamily: fontBody, color: primaryLight }">JOIN OUR WEDDING</p>
                    <h2 class="pearl-closing__names" :style="{ fontFamily: fontTitle, color: '#fff' }">
                        {{ groomNick }} &amp; {{ brideNick }}
                    </h2>
                    <p v-if="firstEventDate" :style="{ fontFamily: fontHeading, color: '#fff', opacity: 0.7 }">
                        {{ firstEventDate }}
                    </p>
                    <div class="pearl-closing__divider" :style="{ background: primaryLight }"></div>
                    <p class="pearl-closing__text" :style="{ fontFamily: fontBody, color: '#fff', opacity: 0.6 }">
                        {{ closingText }}
                    </p>
                </div>
            </section>

        </div>

        <!-- ── Toast ── -->
        <Transition name="pearl-fade">
            <div
                v-if="toastVisible"
                class="pearl-toast"
                :style="{ background: accent, fontFamily: fontBody }"
            >
                {{ toastMsg }}
            </div>
        </Transition>

    </div>
</template>

<style scoped>
/* ── Reset ── */
.pearl * { box-sizing: border-box; }
.pearl img { max-width: 100%; display: block; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

/* ── Gate ── */
.pearl-gate {
    position: fixed; inset: 0; z-index: 50;
    display: flex; flex-direction: column;
    cursor: pointer; overflow: hidden;
    background: #000;
}
.pearl-gate__bg {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    opacity: 0.75;
}
.pearl-gate__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.5) 60%, rgba(0,0,0,0.75) 100%);
}
.pearl-gate__content {
    position: relative; z-index: 1;
    display: flex; flex-direction: column;
    align-items: center;
    height: 100%; width: 100%;
}
.pearl-gate__initials {
    flex: 1;
    display: flex; justify-content: center; align-items: center;
    gap: 0;
    line-height: 1;
    color: #fff;
    font-size: clamp(28vw, 40vw, 320px);
    letter-spacing: -0.02em;
}
.pearl-gate__bottom {
    padding: 0 24px 48px;
    display: flex; flex-direction: column; align-items: center;
    gap: 8px; text-align: center;
}
.pearl-gate__dear {
    font-size: 0.75rem; letter-spacing: 0.35em;
    text-transform: uppercase; color: rgba(255,255,255,0.7);
    margin: 0;
}
.pearl-gate__guest-name {
    font-size: 1.1rem; color: #fff;
    margin: 0; letter-spacing: 0.05em;
}
.pearl-gate__btn {
    margin-top: 16px;
    padding: 14px 40px;
    background: rgba(0,0,0,0.6);
    border: 1px solid rgba(255,255,255,0.6);
    color: #fff;
    cursor: pointer;
    font-size: 0.8rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    transition: all 0.3s;
    min-width: 220px;
    border-radius: 2px;
}
.pearl-gate__btn:hover { background: rgba(255,255,255,0.15); }
.pearl-gate--sliding {
    animation: pearl-slide-up 1.3s cubic-bezier(0.76, 0, 0.24, 1) forwards;
}
@keyframes pearl-slide-up {
    from { transform: translateY(0); }
    to   { transform: translateY(-100%); }
}

/* ── Cover ── */
.pearl-cover {
    position: relative; overflow: hidden;
    min-height: 60vw; max-height: 70vh;
    display: flex; align-items: flex-end;
}
.pearl-cover__img {
    position: absolute; inset: 0;
}
.pearl-cover__img img {
    width: 100%; height: 100%; object-fit: cover;
}
.pearl-cover__text {
    position: relative; z-index: 1;
    padding: 40px 24px;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    width: 100%; text-align: center;
}
.pearl-cover__sub {
    font-size: 0.65rem; letter-spacing: 0.3em; text-transform: uppercase; margin: 0 0 8px;
}
.pearl-cover__names {
    font-size: clamp(1.8rem, 6vw, 3rem); margin: 0 0 8px; line-height: 1.2;
}

/* ── Section padding ── */
.pearl-section-pad { padding: 60px 24px; text-align: center; }

/* ── Section title ── */
.pearl-section-title {
    font-size: clamp(1.5rem, 4vw, 2.2rem);
    text-align: center;
    margin-bottom: 32px;
}

/* ── Divider label ── */
.pearl-divider-label {
    display: flex; align-items: center; gap: 12px;
    font-size: 0.65rem; letter-spacing: 0.25em; text-transform: uppercase;
    margin-bottom: 32px;
}
.pearl-line { flex: 1; height: 1px; }

/* ── Opening ── */
.pearl-opening {
    padding: 60px 24px;
    max-width: 800px; margin: 0 auto;
    opacity: 0; transition: opacity 0.8s ease, transform 0.8s ease; transform: translateY(20px);
}
.pearl-opening.p-visible { opacity: 1; transform: none; }
.pearl-opening__text {
    text-align: center; font-size: 1rem; line-height: 1.8;
    margin-bottom: 48px; color: #555;
}

/* ── Couple ── */
.pearl-couple {
    display: grid; grid-template-columns: 1fr auto 1fr; gap: 16px;
    align-items: start;
}
.pearl-couple__person { text-align: center; }
.pearl-couple__nick { font-size: clamp(2rem, 6vw, 3rem); margin: 0 0 8px; }
.pearl-couple__fullname { font-size: 1rem; margin-bottom: 8px; }
.pearl-couple__order { font-size: 0.8rem; color: #888; margin-bottom: 4px; }
.pearl-couple__parents { font-size: 0.8rem; color: #666; line-height: 1.6; }
.pearl-couple__ampersand {
    font-size: clamp(2rem, 6vw, 3rem);
    padding-top: 8px;
}

/* ── Quote ── */
.pearl-quote {
    padding: 60px 32px; text-align: center;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-quote.p-visible { opacity: 1; }
.pearl-quote__mark { font-size: 6rem; line-height: 0.5; margin-bottom: 16px; opacity: 0.6; }
.pearl-quote__text {
    font-size: clamp(1.1rem, 3vw, 1.4rem); line-height: 1.8;
    margin: 0 auto; max-width: 600px;
    font-style: italic;
}

/* ── Countdown ── */
.pearl-countdown {
    padding: 60px 24px; text-align: center;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-countdown.p-visible { opacity: 1; }
.pearl-countdown__boxes {
    display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;
}
.pearl-countdown__box {
    border: 1px solid; padding: 16px 20px; min-width: 72px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.pearl-countdown__num { font-size: 2rem; line-height: 1; }
.pearl-countdown__lbl { font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; }

/* ── Events ── */
.pearl-events {
    padding: 60px 24px;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-events.p-visible { opacity: 1; }
.pearl-events__grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px; max-width: 700px; margin: 0 auto;
}
.pearl-event-card {
    border: 1px solid; padding: 32px 24px; text-align: center;
    display: flex; flex-direction: column; gap: 10px;
}
.pearl-event-card__name { font-size: 1.3rem; margin: 0; }
.pearl-event-card__date { font-size: 1rem; }
.pearl-event-card__time { font-size: 0.85rem; }
.pearl-event-card__venue { font-size: 0.9rem; }
.pearl-event-card__maps { font-size: 0.8rem; text-decoration: none; letter-spacing: 0.1em; }

/* ── Love Story ── */
.pearl-story {
    padding: 60px 24px; max-width: 600px; margin: 0 auto;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-story.p-visible { opacity: 1; }
.pearl-story__list { display: flex; flex-direction: column; gap: 32px; padding-left: 24px; }
.pearl-story__item {
    display: flex; gap: 20px;
    position: relative;
}
.pearl-story__item:not(:last-child)::before {
    content: ''; position: absolute; left: 5px; top: 16px;
    bottom: -32px; width: 1px;
}
.pearl-story__dot {
    width: 12px; height: 12px; border-radius: 50%;
    flex-shrink: 0; margin-top: 4px;
}
.pearl-story__body { flex: 1; }
.pearl-story__date { font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 4px; }
.pearl-story__title { font-size: 1.1rem; margin: 0 0 8px; }
.pearl-story__desc { font-size: 0.9rem; line-height: 1.7; color: #666; margin: 0; }

/* ── Gallery ── */
.pearl-gallery {
    padding: 60px 16px;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-gallery.p-visible { opacity: 1; }
.pearl-gallery__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
.pearl-gallery__item img {
    width: 100%; height: 200px; object-fit: cover;
}
@media (min-width: 600px) {
    .pearl-gallery__grid { grid-template-columns: repeat(3, 1fr); }
}

/* ── Video ── */
.pearl-video {
    padding: 0 16px 48px;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-video.p-visible { opacity: 1; }
.pearl-video iframe { width: 100%; aspect-ratio: 16/9; border: none; display: block; }

/* ── Additional info ── */
.pearl-additional { padding: 32px 24px; text-align: center; }

/* ── RSVP ── */
.pearl-rsvp {
    padding: 60px 24px; max-width: 480px; margin: 0 auto;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-rsvp.p-visible { opacity: 1; }

/* ── Gift ── */
.pearl-gift {
    padding: 60px 24px; text-align: center;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-gift.p-visible { opacity: 1; }
.pearl-gift__desc { max-width: 480px; margin: 0 auto 32px; font-size: 0.9rem; }
.pearl-gift__accounts {
    display: flex; flex-direction: column; gap: 16px;
    max-width: 400px; margin: 0 auto;
}
.pearl-gift__card { border: 1px solid; padding: 24px; display: flex; flex-direction: column; gap: 8px; }
.pearl-gift__bank { font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; }
.pearl-gift__name { font-size: 1rem; }
.pearl-gift__number { font-size: 1.4rem; letter-spacing: 0.1em; }
.pearl-gift__copy {
    background: transparent; border: 1px solid;
    padding: 8px 20px; cursor: pointer;
    font-size: 0.8rem; letter-spacing: 0.1em;
    align-self: center; transition: opacity 0.2s;
}
.pearl-gift__copy:hover { opacity: 0.7; }

/* ── Wishes ── */
.pearl-wishes {
    padding: 60px 24px; max-width: 600px; margin: 0 auto;
    opacity: 0; transition: opacity 0.8s ease;
}
.pearl-wishes.p-visible { opacity: 1; }
.pearl-wishes__list { margin-top: 40px; display: flex; flex-direction: column; gap: 16px; }
.pearl-wish-item { border-bottom: 1px solid; padding-bottom: 16px; }
.pearl-wish-item__name { font-size: 1rem; margin-bottom: 4px; }
.pearl-wish-item__msg { font-size: 0.9rem; color: #666; margin: 0; line-height: 1.6; }

/* ── Form shared ── */
.pearl-form { display: flex; flex-direction: column; gap: 12px; }
.pearl-form input,
.pearl-form select,
.pearl-form textarea {
    width: 100%; padding: 12px 16px;
    border: 1px solid; background: transparent;
    font-size: 0.9rem; outline: none;
    transition: opacity 0.2s;
}
.pearl-form textarea { min-height: 100px; resize: vertical; }
.pearl-form button {
    padding: 14px; color: #fff; border: none;
    cursor: pointer; font-size: 0.85rem;
    letter-spacing: 0.15em; text-transform: uppercase;
    transition: opacity 0.2s;
}
.pearl-form button:hover:not(:disabled) { opacity: 0.85; }
.pearl-form button:disabled { opacity: 0.5; cursor: not-allowed; }
.pearl-form__error { color: #e53e3e; font-size: 0.85rem; margin: 0; }
.pearl-form__success { color: #38a169; font-size: 0.85rem; margin: 0; }

/* ── Closing ── */
.pearl-closing {
    position: relative; overflow: hidden;
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
}
.pearl-closing__bg {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.pearl-closing__overlay { position: absolute; inset: 0; }
.pearl-closing__content {
    position: relative; z-index: 1;
    text-align: center; padding: 40px 24px;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.pearl-closing__sub {
    font-size: 0.7rem; letter-spacing: 0.3em; text-transform: uppercase; margin: 0;
}
.pearl-closing__names {
    font-size: clamp(2rem, 8vw, 4rem); line-height: 1.1; margin: 0;
}
.pearl-closing__divider { width: 40px; height: 1px; }
.pearl-closing__text {
    font-size: 0.85rem; line-height: 1.8; max-width: 400px; margin: 0;
}

/* ── Music button ── */
.pearl-music-btn {
    position: fixed; bottom: 24px; right: 16px; z-index: 40;
    width: 48px; height: 48px; border-radius: 50%;
    border: none; color: #fff; cursor: pointer;
    font-size: 14px; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: opacity 0.2s;
}
.pearl-music-btn:hover { opacity: 0.85; }

/* ── Toast ── */
.pearl-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    color: #fff; padding: 10px 20px; border-radius: 4px;
    z-index: 60; white-space: nowrap; font-size: 0.85rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.pearl-fade-enter-active, .pearl-fade-leave-active { transition: opacity 0.3s; }
.pearl-fade-enter-from, .pearl-fade-leave-to { opacity: 0; }

/* ── Reveal animation ── */
.pearl-opening, .pearl-quote, .pearl-countdown,
.pearl-events, .pearl-story, .pearl-gallery,
.pearl-video, .pearl-rsvp, .pearl-gift, .pearl-wishes {
    opacity: 0; transform: translateY(20px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.p-visible {
    opacity: 1 !important; transform: none !important;
}

/* ── Btn outline ── */
.pearl-btn-outline {
    display: inline-block; padding: 12px 32px;
    border: 1px solid; text-decoration: none;
    font-size: 0.85rem; letter-spacing: 0.15em; text-transform: uppercase;
    transition: opacity 0.2s;
}
.pearl-btn-outline:hover { opacity: 0.7; }
</style>
