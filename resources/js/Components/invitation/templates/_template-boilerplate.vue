<!-- AI: before editing, READ docs/AI-NEW-TEMPLATE-GUIDE.md — defines composable contract, section catalog, animation minimums, anti-halu rules. -->
<script setup>
import { onMounted } from 'vue';
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate';

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
    details, events, galleries,
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
} = useInvitationTemplate(props, {
    galleryLayout: 'vertical',  // ganti: 'vertical' | 'horizontal' | 'grid' | 'masonry'
    openingStyle:  'fade',      // ganti: 'fade' | 'gate' | 'slide'
    revealClass:   'is-visible',
});

// Inject Google Fonts untuk template ini
onMounted(() => {
    // Ganti dengan font yang dipakai template ini
    // const link = Object.assign(document.createElement('link'), {
    //     rel:  'stylesheet',
    //     href: 'https://fonts.googleapis.com/css2?family=...',
    // });
    // document.head.appendChild(link);
});
</script>

<template>
    <div>
        <!-- ── Audio ── -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            class="sr-only"
        />

        <!-- ── Cover / Opening screen ── -->
        <!-- Ganti dengan cover visual sesuai desain HTML -->
        <div
            v-if="!gateOpen"
            @click="triggerGate"
            style="min-height:100vh;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;cursor:pointer"
            :style="{ background: bgColor }"
        >
            <h1 :style="{ fontFamily: fontTitle, color: primary }">
                {{ groomNick }} &amp; {{ brideNick }}
            </h1>
            <p v-if="firstEventDate" :style="{ color: accent, fontFamily: fontHeading }">
                {{ firstEventDate }}
            </p>
            <p v-if="guest?.name" :style="{ fontFamily: fontHeading }">
                Kepada Yth. {{ guest.name }}
            </p>
            <button
                @click.stop="triggerGate"
                :style="{ background: primary, color: '#fff', padding: '12px 32px', border: 'none', borderRadius: '8px', fontFamily: fontHeading, cursor: 'pointer' }"
            >
                Buka Undangan
            </button>
        </div>

        <!-- ── Main content (setelah gate terbuka) ── -->
        <div v-if="contentOpen">

            <!-- Music float button -->
            <button
                v-if="sectionEnabled('music') && invitation.music?.file_url"
                @click="toggleMusic"
                :style="{ position:'fixed', bottom:'24px', right:'16px', zIndex:40, background:primary, color:'#fff', border:'none', borderRadius:'50%', width:'48px', height:'48px', cursor:'pointer', fontSize:'18px' }"
                aria-label="Toggle musik"
            >♪</button>

            <!-- Cover section -->
            <section v-if="sectionEnabled('cover')">
                <!-- TODO: implementasi cover visual dari HTML -->
            </section>

            <!-- Opening / Sambutan -->
            <section v-if="sectionEnabled('opening')">
                <!-- TODO: implementasi opening dari HTML -->
                <p>{{ openingText }}</p>
            </section>

            <!-- Quote / Ayat -->
            <section v-if="sectionEnabled('quote') && sectionData('quote').text">
                <blockquote>{{ sectionData('quote').text }}</blockquote>
            </section>

            <!-- Events: Akad + Resepsi -->
            <section v-if="sectionEnabled('events') && events.length">
                <!-- TODO: implementasi tampilan event dari HTML -->
                <div v-for="event in events" :key="event.id">
                    <h3 :style="{ fontFamily: fontHeading }">{{ event.event_name }}</h3>
                    <p>{{ event.event_date_formatted }}</p>
                    <p v-if="event.start_time">{{ event.start_time }}</p>
                    <p>{{ event.location }}</p>
                    <a v-if="event.maps_url" :href="event.maps_url" target="_blank">Buka Maps</a>
                </div>
            </section>

            <!-- Countdown -->
            <section v-if="sectionEnabled('countdown') && targetDate">
                <!-- TODO: implementasi countdown visual dari HTML -->
                <div>
                    {{ pad(countdown.days) }} Hari
                    {{ pad(countdown.hours) }} Jam
                    {{ pad(countdown.minutes) }} Menit
                    {{ pad(countdown.seconds) }} Detik
                </div>
            </section>

            <!-- Live streaming -->
            <section v-if="sectionEnabled('live_streaming') && sectionData('live_streaming').url">
                <a :href="sectionData('live_streaming').url" target="_blank" rel="noopener">
                    Tonton Live Streaming
                </a>
            </section>

            <!-- Additional info -->
            <section v-if="sectionEnabled('additional_info') && sectionData('additional_info').text">
                <p>{{ sectionData('additional_info').text }}</p>
            </section>

            <!-- Gallery -->
            <section v-if="sectionEnabled('gallery') && galleries.length">
                <!-- TODO: implementasi gallery sesuai galleryLayout dari HTML -->
                <!-- galleryLayout = 'vertical' | 'horizontal' | 'grid' | 'masonry' -->
                <div v-for="img in galleries" :key="img.id">
                    <img :src="img.file_url" :alt="img.caption ?? ''" loading="lazy" />
                </div>
            </section>

            <!-- Love story -->
            <section v-if="sectionEnabled('love_story') && sectionData('love_story').stories?.length">
                <!-- TODO: implementasi timeline kisah cinta dari HTML -->
                <div v-for="story in sectionData('love_story').stories" :key="story.date">
                    <h4>{{ story.title }}</h4>
                    <p>{{ story.date }}</p>
                    <p>{{ story.description }}</p>
                </div>
            </section>

            <!-- Video embed -->
            <section v-if="sectionEnabled('video') && videoEmbedUrl(sectionData('video').url)">
                <iframe
                    :src="videoEmbedUrl(sectionData('video').url)"
                    frameborder="0"
                    allowfullscreen
                    style="width:100%;aspect-ratio:16/9"
                />
            </section>

            <!-- RSVP -->
            <section v-if="sectionEnabled('rsvp')">
                <!-- TODO: implementasi RSVP form dari HTML -->
                <form @submit.prevent="submitRsvp">
                    <input v-model="rsvpForm.guest_name" placeholder="Nama lengkap" required />
                    <select v-model="rsvpForm.attendance" required>
                        <option value="">Konfirmasi kehadiran</option>
                        <option value="hadir">Hadir</option>
                        <option value="tidak_hadir">Tidak Hadir</option>
                    </select>
                    <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" />
                    <textarea v-model="rsvpForm.notes" placeholder="Catatan (opsional)" />
                    <p v-if="rsvpError" style="color:red">{{ rsvpError }}</p>
                    <p v-if="rsvpSuccess" style="color:green">Terima kasih atas konfirmasinya!</p>
                    <button type="submit" :disabled="rsvpSubmitting">
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim RSVP' }}
                    </button>
                </form>
            </section>

            <!-- Gift / Nomor rekening -->
            <section v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length">
                <!-- TODO: implementasi tampilan rekening dari HTML -->
                <div v-for="acc in sectionData('gift').accounts" :key="acc.account_number">
                    <p>{{ acc.bank }}</p>
                    <p>{{ acc.account_name }}</p>
                    <p>{{ acc.account_number }}</p>
                    <button @click="copyToClipboard(acc.account_number)">
                        {{ copiedAccount === acc.account_number ? 'Tersalin ✓' : 'Salin' }}
                    </button>
                </div>
            </section>

            <!-- Wishes / Buku tamu -->
            <section v-if="sectionEnabled('wishes')">
                <!-- TODO: implementasi form + daftar ucapan dari HTML -->
                <form @submit.prevent="submitMessage">
                    <input v-model="msgForm.name" placeholder="Nama" required />
                    <textarea v-model="msgForm.message" placeholder="Ucapan & doa" required />
                    <p v-if="msgError" style="color:red">{{ msgError }}</p>
                    <p v-if="msgSuccess" style="color:green">Ucapan terkirim!</p>
                    <button type="submit" :disabled="msgSubmitting">
                        {{ msgSubmitting ? 'Mengirim...' : 'Kirim Ucapan' }}
                    </button>
                </form>
                <div v-for="msg in localMessages" :key="msg.id ?? msg.name">
                    <strong>{{ msg.name }}</strong>
                    <p>{{ msg.message }}</p>
                </div>
            </section>

            <!-- Closing -->
            <section v-if="sectionEnabled('closing')">
                <!-- TODO: implementasi penutup dari HTML -->
                <p>{{ closingText }}</p>
                <p>{{ groomName }} &amp; {{ brideName }}</p>
            </section>

        </div>

        <!-- Toast notification -->
        <Transition name="fade">
            <div
                v-if="toastVisible"
                style="position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:8px 16px;border-radius:8px;z-index:50;white-space:nowrap"
            >
                {{ toastMsg }}
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
