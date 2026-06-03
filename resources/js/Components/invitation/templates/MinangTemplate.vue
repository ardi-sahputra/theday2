<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import GallerySection from '@/Components/invitation/sections/GallerySection.vue';

const props = defineProps({
  invitation: { type: Object, required: true },
  messages: { type: Array, default: () => [] },
  isDemo: { type: Boolean, default: false },
  autoOpen: { type: Boolean, default: false },
});

const cfg = computed(() => props.invitation.config ?? {});
const sections = computed(() => props.invitation.sections ?? {});
const details = computed(() => props.invitation.details ?? {});
const events = computed(() => props.invitation.events ?? []);
const galleries = computed(() => props.invitation.galleries ?? []);
const storyItems = computed(() => details.value.love_story ?? []);
const giftAccounts = computed(() => details.value.gift_accounts ?? []);
const additionalInfo = computed(() => details.value.additional_info ?? []);
const galleryLayout = computed(() => cfg.value.gallery_layout ?? 'grid');

const primary = computed(() => cfg.value.primary_color ?? '#7A1F2B');
const primaryLight = computed(() => cfg.value.primary_color_light ?? '#D4A63A');
const darkBg = computed(() => cfg.value.dark_bg ?? '#1C1412');
const bgColor = computed(() => cfg.value.secondary_color ?? '#F8F0E6');
const accent = computed(() => cfg.value.accent_color ?? '#4A352D');
const fontTitle = computed(() => cfg.value.font_title ?? 'Cormorant Garamond');
const fontHeading = computed(() => cfg.value.font_heading ?? 'Cormorant Garamond');
const fontBody = computed(() => cfg.value.font_body ?? 'DM Sans');

const groomName = computed(() => details.value.groom_name ?? 'Mempelai Pria');
const brideName = computed(() => details.value.bride_name ?? 'Mempelai Wanita');
const groomNickname = computed(() => details.value.groom_nickname ?? '');
const brideNickname = computed(() => details.value.bride_nickname ?? '');
const groomOrder = computed(() => details.value.groom_child_order ?? '');
const brideOrder = computed(() => details.value.bride_child_order ?? '');
const openingText = computed(() => details.value.opening_text ?? 'Dengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir di hari istimewa kami.');
const closingText = computed(() => details.value.closing_text ?? 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir. Atas doa restu yang diberikan, kami ucapkan terima kasih.');
const quoteText = computed(() => details.value.quote_text ?? 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan hidup dari jenismu sendiri, supaya kamu cenderung dan merasa tenteram kepadanya.');
const quoteSource = computed(() => details.value.quote_source ?? 'QS. Ar-Rum: 21');
const guestName = computed(() => details.value.guest_name ?? props.invitation.guest_name ?? 'Tamu Undangan');
const guestGreeting = computed(() => details.value.guest_greeting ?? 'Dengan hormat, kami mengundang');
const venueNote = computed(() => details.value.venue_note ?? 'Silakan hadir tepat waktu agar seluruh rangkaian acara dapat berjalan dengan khidmat.');
const videoUrl = computed(() => details.value.video_url ?? '');
const videoTitle = computed(() => details.value.video_title ?? 'Prewedding Film');
const livestreamUrl = computed(() => details.value.livestream_url ?? '');
const livestreamLabel = computed(() => details.value.livestream_label ?? 'Live Streaming');
const livestreamNote = computed(() => details.value.livestream_note ?? 'Bagi keluarga dan sahabat yang berhalangan hadir, acara dapat disaksikan secara online.');

const firstEvent = computed(() => events.value[0] ?? null);
const firstEventDate = computed(() => firstEvent.value?.event_date_formatted ?? '');
const mapEvent = computed(() => events.value.find(e => e.maps_url) ?? firstEvent.value ?? null);

function sectionEnabled(key, fallback = true) {
  return sections.value?.[key]?.enabled ?? fallback;
}

function sectionVariant(key, fallback) {
  return sections.value?.[key]?.variant ?? fallback;
}

const gateOpen = ref(false);
const contentOpen = ref(false);
const gateAnimating = ref(false);
const musicPlaying = ref(false);
const audioEl = ref(null);

async function triggerGate() {
  if (gateAnimating.value || gateOpen.value) return;
  gateAnimating.value = true;
  await new Promise((r) => setTimeout(r, 1200));
  gateOpen.value = true;
  contentOpen.value = true;
  gateAnimating.value = false;
  if (props.invitation.music?.file_url && audioEl.value) {
    audioEl.value.play().catch(() => {});
    musicPlaying.value = true;
  }
}

function toggleMusic() {
  if (!audioEl.value) return;
  if (musicPlaying.value) {
    audioEl.value.pause();
    musicPlaying.value = false;
  } else {
    audioEl.value.play().then(() => { musicPlaying.value = true; }).catch(() => {});
  }
}

const localMessages = ref([...props.messages]);
const msgForm = ref({ name: '', message: '' });
const msgSubmitting = ref(false);
const msgSuccess = ref(false);
const msgError = ref('');

async function submitMessage() {
  if (props.isDemo) return void (msgError.value = 'Form tidak aktif di halaman demo.');
  if (!msgForm.value.name.trim() || !msgForm.value.message.trim()) return void (msgError.value = 'Nama dan ucapan wajib diisi.');
  msgSubmitting.value = true;
  msgError.value = '';
  try {
    const res = await fetch(`/${props.invitation.slug}/messages`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify(msgForm.value),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message ?? 'Gagal mengirim ucapan.');
    localMessages.value.unshift(data.data);
    msgForm.value = { name: '', message: '' };
    msgSuccess.value = true;
    setTimeout(() => { msgSuccess.value = false; }, 4000);
  } catch (e) {
    msgError.value = e.message;
  } finally {
    msgSubmitting.value = false;
  }
}

const rsvpForm = ref({ guest_name: '', attendance: '', guest_count: 1, notes: '' });
const rsvpSubmitting = ref(false);
const rsvpSuccess = ref(false);
const rsvpError = ref('');

async function submitRsvp() {
  if (props.isDemo) return void (rsvpError.value = 'Form tidak aktif di halaman demo.');
  if (!rsvpForm.value.guest_name.trim() || !rsvpForm.value.attendance) return void (rsvpError.value = 'Nama dan konfirmasi kehadiran wajib diisi.');
  rsvpSubmitting.value = true;
  rsvpError.value = '';
  try {
    const res = await fetch(`/${props.invitation.slug}/rsvp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify(rsvpForm.value),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message ?? 'Gagal mengirim RSVP.');
    rsvpSuccess.value = true;
  } catch (e) {
    rsvpError.value = e.message;
  } finally {
    rsvpSubmitting.value = false;
  }
}

async function copyText(text) {
  if (!text) return;
  try { await navigator.clipboard.writeText(text); } catch (_) {}
}

const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
const targetDate = computed(() => {
  if (!firstEvent.value?.event_date) return null;
  const t = firstEvent.value.start_time ? `T${firstEvent.value.start_time}` : 'T00:00';
  return new Date(firstEvent.value.event_date + t);
});
let cdTimer;
function updateCountdown() {
  if (!targetDate.value) return;
  const diff = targetDate.value - Date.now();
  if (diff <= 0) return void (countdown.value = { days: 0, hours: 0, minutes: 0, seconds: 0 });
  countdown.value = {
    days: Math.floor(diff / 86400000),
    hours: Math.floor((diff % 86400000) / 3600000),
    minutes: Math.floor((diff % 3600000) / 60000),
    seconds: Math.floor((diff % 60000) / 1000),
  };
}
const pad = (n) => String(n).padStart(2, '0');

function observeReveal(el) {
  if (!el) return;
  const obs = new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) {
      el.classList.add('p-visible');
      obs.disconnect();
    }
  }, { threshold: 0.12 });
  obs.observe(el);
}
const vReveal = (el) => observeReveal(el);

onMounted(() => {
  const fontParam = [
    'Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400',
    'DM+Sans:wght@400;500;700',
  ].join('&family=');
  const link = Object.assign(document.createElement('link'), {
    rel: 'stylesheet',
    href: `https://fonts.googleapis.com/css2?family=${fontParam}&display=swap`,
  });
  document.head.appendChild(link);
  updateCountdown();
  cdTimer = setInterval(updateCountdown, 1000);
  if (props.autoOpen || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    gateOpen.value = true;
    contentOpen.value = true;
  }
});

onUnmounted(() => clearInterval(cdTimer));
</script>

<template>
  <div class="p-root">
    <audio v-if="invitation.music?.file_url" ref="audioEl" :src="invitation.music.file_url" loop preload="none" class="sr-only" />

    <Transition name="p-overlay">
      <div v-if="!gateOpen" class="p-opening" :style="{ background: darkBg }" @click="triggerGate">
        <div class="p-opening-texture"></div>
        <div class="p-gate p-gate-left" :class="{ 'p-gate-opening': gateAnimating }"><div class="p-gate-panel"><div class="p-gonjong"></div><div class="p-marawa"></div><div class="p-songket"></div></div></div>
        <div class="p-gate p-gate-right" :class="{ 'p-gate-opening': gateAnimating }"><div class="p-gate-panel p-gate-panel-right"><div class="p-gonjong"></div><div class="p-marawa"></div><div class="p-songket"></div></div></div>
        <div class="p-opening-center" :class="{ 'p-opening-center-reveal': gateAnimating }">
          <p class="p-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</p>
          <p class="p-kicker" :style="{ color: primaryLight, fontFamily: fontHeading }">Undangan Pernikahan</p>
          <div class="p-divider"></div>
          <h1 class="p-name shimmer-gold" :style="{ fontFamily: fontTitle }">{{ groomName }}</h1>
          <p class="p-amp" :style="{ color: primaryLight, fontFamily: fontHeading }">&amp;</p>
          <h1 class="p-name shimmer-gold" :style="{ fontFamily: fontTitle }">{{ brideName }}</h1>
          <div class="p-divider"></div>
          <p v-if="firstEventDate" class="p-opening-date" :style="{ color: primaryLight, fontFamily: fontBody }">{{ firstEventDate }}</p>
          <button class="p-open-btn" :style="{ borderColor: primaryLight, color: primaryLight, fontFamily: fontHeading }" @click.stop="triggerGate">Buka Undangan <span>↓</span></button>
        </div>
      </div>
    </Transition>

    <Transition name="p-fade">
      <button v-if="gateOpen && invitation.music?.file_url" @click="toggleMusic" class="p-music-btn" :class="{ 'is-playing': musicPlaying }" :style="{ background: primary }" aria-label="Toggle musik">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13" /><circle cx="6" cy="18" r="3" /><circle cx="18" cy="15" r="3" /></svg>
      </button>
    </Transition>

    <div v-if="gateOpen" class="p-main">
      <section v-if="sectionEnabled('cover', true)" class="p-cover" :class="`variant-${sectionVariant('cover', 'hero-framed-classic')}`" :style="{ background: bgColor }">
        <div class="p-cover-bg"></div>
        <div class="p-marawa-top"></div>
        <div class="p-rumah-frame">
          <div class="p-rumah-roof"><span></span><span></span><span></span><span></span></div>
          <div class="p-cover-card">
            <p class="p-cover-label" :style="{ color: primary, fontFamily: fontHeading }">The Wedding Of</p>
            <h1 class="p-cover-name shimmer-gold" :style="{ fontFamily: fontTitle }">{{ groomName }}</h1>
            <p class="p-cover-amp" :style="{ color: primary, fontFamily: fontHeading }">&amp;</p>
            <h1 class="p-cover-name shimmer-gold" :style="{ fontFamily: fontTitle }">{{ brideName }}</h1>
            <p v-if="firstEventDate" class="p-cover-date" :style="{ color: accent, fontFamily: fontBody }">{{ firstEventDate }}</p>
            <div class="p-guest-pill" :style="{ borderColor: primaryLight + '55', color: accent, fontFamily: fontBody }">{{ guestGreeting }} <strong>{{ guestName }}</strong></div>
          </div>
        </div>
      </section>

      <Transition name="p-sections"><div v-if="contentOpen">

        <section v-if="sectionEnabled('opening_message', true)" class="p-section p-light" :style="{ background: bgColor }">
          <div class="p-section-pattern"></div>
          <div class="p-section-inner">
            <div :ref="el => vReveal(el)" class="p-reveal p-intro-card">
              <p class="p-assalamu" :style="{ color: primary, fontFamily: fontHeading }">Assalamu'alaikum Warahmatullahi Wabarakatuh</p>
              <p class="p-body-copy" :style="{ color: accent, fontFamily: fontBody }">{{ openingText }}</p>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('couple_profile', true)" class="p-section p-light" :style="{ background: bgColor }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: darkBg, fontFamily: fontHeading }">Mempelai</h2></div>
            <div :ref="el => vReveal(el)" class="p-reveal p-couple-grid">
              <div class="p-profile-card">
                <div v-if="details.groom_photo_url" class="p-profile-photo-wrap"><img :src="details.groom_photo_url" class="p-profile-photo" alt="" /></div>
                <div v-else class="p-profile-avatar" :style="{ color: primary, fontFamily: fontTitle }">{{ groomName.charAt(0) }}</div>
                <h3 class="p-profile-name" :style="{ color: darkBg, fontFamily: fontTitle }">{{ groomName }}</h3>
                <p v-if="groomNickname" class="p-profile-meta" :style="{ color: primary, fontFamily: fontBody }">{{ groomNickname }}</p>
                <p v-if="groomOrder" class="p-profile-copy" :style="{ color: accent, fontFamily: fontBody }">{{ groomOrder }}</p>
                <p class="p-profile-copy" :style="{ color: accent, fontFamily: fontBody }">Putra dari {{ details.groom_parent_names ?? '—' }}</p>
              </div>
              <div class="p-couple-sep"><span :style="{ color: primaryLight, fontFamily: fontHeading }">✦</span></div>
              <div class="p-profile-card">
                <div v-if="details.bride_photo_url" class="p-profile-photo-wrap"><img :src="details.bride_photo_url" class="p-profile-photo" alt="" /></div>
                <div v-else class="p-profile-avatar" :style="{ color: primary, fontFamily: fontTitle }">{{ brideName.charAt(0) }}</div>
                <h3 class="p-profile-name" :style="{ color: darkBg, fontFamily: fontTitle }">{{ brideName }}</h3>
                <p v-if="brideNickname" class="p-profile-meta" :style="{ color: primary, fontFamily: fontBody }">{{ brideNickname }}</p>
                <p v-if="brideOrder" class="p-profile-copy" :style="{ color: accent, fontFamily: fontBody }">{{ brideOrder }}</p>
                <p class="p-profile-copy" :style="{ color: accent, fontFamily: fontBody }">Putri dari {{ details.bride_parent_names ?? '—' }}</p>
              </div>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('quote_verse', true)" class="p-section p-dark" :style="{ background: darkBg }">
          <div class="p-section-inner">
            <div :ref="el => vReveal(el)" class="p-reveal p-quote-box">
              <div class="p-divider"></div>
              <blockquote class="p-quote" :style="{ color: bgColor, fontFamily: fontHeading }">“{{ quoteText }}”</blockquote>
              <p class="p-quote-source" :style="{ color: primaryLight, fontFamily: fontBody }">{{ quoteSource }}</p>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('event_details', true)" class="p-section p-dark" :style="{ background: darkBg }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: primaryLight, fontFamily: fontHeading }">Detail Acara</h2></div>
            <div class="p-events-list">
              <div v-for="(event, i) in events" :key="event.id ?? i" :ref="el => vReveal(el)" class="p-reveal p-event-card" :style="{ transitionDelay: `${i * 120}ms` }">
                <div class="p-event-songket"></div>
                <h3 class="p-event-name" :style="{ color: primaryLight, fontFamily: fontTitle }">{{ event.event_name }}</h3>
                <div class="p-event-row"><strong :style="{ color: primaryLight }">Tanggal</strong><span :style="{ color: bgColor, fontFamily: fontBody }">{{ event.event_date_formatted }}</span></div>
                <div class="p-event-row" v-if="event.start_time"><strong :style="{ color: primaryLight }">Waktu</strong><span :style="{ color: bgColor, fontFamily: fontBody }">{{ event.start_time }}{{ event.end_time ? ` – ${event.end_time}` : '' }} WIB</span></div>
                <div class="p-event-row"><strong :style="{ color: primaryLight }">Tempat</strong><span :style="{ color: bgColor, fontFamily: fontBody }">{{ event.venue_name }}</span></div>
                <p v-if="event.venue_address" class="p-event-address" :style="{ color: bgColor + 'b3', fontFamily: fontBody }">{{ event.venue_address }}</p>
                <p v-if="event.notes" class="p-event-note" :style="{ color: primaryLight + 'd0', fontFamily: fontBody }">{{ event.notes }}</p>
              </div>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('countdown', true)" class="p-section p-dark" :style="{ background: darkBg }">
          <div class="p-section-inner">
            <div v-if="targetDate" :ref="el => vReveal(el)" class="p-reveal p-countdown-wrap">
              <p class="p-countdown-label" :style="{ color: primaryLight, fontFamily: fontHeading }">Menghitung Hari</p>
              <div class="p-countdown-grid">
                <div v-for="({ val, label }) in [{ val: countdown.days, label: 'Hari' }, { val: countdown.hours, label: 'Jam' }, { val: countdown.minutes, label: 'Menit' }, { val: countdown.seconds, label: 'Detik' } ]" :key="label" class="p-count-cell">
                  <p class="p-count-num" :style="{ color: primaryLight, fontFamily: fontTitle }">{{ pad(val) }}</p>
                  <p class="p-count-text" :style="{ color: bgColor + 'cc', fontFamily: fontBody }">{{ label }}</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('venue_map', true) && mapEvent" class="p-section p-light" :style="{ background: bgColor }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: darkBg, fontFamily: fontHeading }">Lokasi Acara</h2></div>
            <div :ref="el => vReveal(el)" class="p-reveal p-map-card">
              <div class="p-map-preview">
                <div class="p-map-preview-inner">{{ mapEvent.venue_name }}</div>
              </div>
              <div class="p-map-info">
                <h3 :style="{ color: darkBg, fontFamily: fontTitle }">{{ mapEvent.venue_name }}</h3>
                <p :style="{ color: accent, fontFamily: fontBody }">{{ mapEvent.venue_address }}</p>
                <p class="p-map-note" :style="{ color: primary, fontFamily: fontBody }">{{ venueNote }}</p>
                <a v-if="mapEvent.maps_url" :href="mapEvent.maps_url" target="_blank" rel="noopener noreferrer" class="p-map-btn" :style="{ borderColor: primary, color: primary, fontFamily: fontBody }">Buka Google Maps</a>
              </div>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('love_story', false) && storyItems.length" class="p-section p-light" :style="{ background: bgColor }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: darkBg, fontFamily: fontHeading }">Cerita Kami</h2></div>
            <div class="p-story-list">
              <div v-for="(item, i) in storyItems" :key="i" :ref="el => vReveal(el)" class="p-reveal p-story-item">
                <div class="p-story-year" :style="{ color: primaryLight, fontFamily: fontTitle }">{{ item.year ?? item.date ?? 'Momen' }}</div>
                <div class="p-story-card">
                  <h3 :style="{ color: darkBg, fontFamily: fontHeading }">{{ item.title ?? 'Cerita Kami' }}</h3>
                  <p :style="{ color: accent, fontFamily: fontBody }">{{ item.description ?? '' }}</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('gallery', true) && galleries.length" class="p-section p-light" :style="{ background: bgColor }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: darkBg, fontFamily: fontHeading }">Galeri Kami</h2></div>
            <GallerySection :galleries="galleries" :layout="galleryLayout" :primary-color="'#D4A63A'" />
          </div>
        </section>

        <section v-if="sectionEnabled('video', false) && videoUrl" class="p-section p-dark" :style="{ background: darkBg }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: primaryLight, fontFamily: fontHeading }">{{ videoTitle }}</h2></div>
            <div :ref="el => vReveal(el)" class="p-reveal p-video-card">
              <div class="p-video-thumb">Play</div>
              <a :href="videoUrl" target="_blank" rel="noopener noreferrer" class="p-map-btn" :style="{ borderColor: primaryLight, color: primaryLight, fontFamily: fontBody }">Buka Video</a>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('rsvp', true)" class="p-section p-dark" :style="{ background: darkBg }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: primaryLight, fontFamily: fontHeading }">Konfirmasi Kehadiran</h2></div>
            <div :ref="el => vReveal(el)" class="p-reveal p-form-wrap">
              <div v-if="isDemo" class="p-demo-note" :style="{ borderColor: primaryLight + '50', color: bgColor, fontFamily: fontBody }">Form RSVP tidak aktif di halaman demo</div>
              <div v-else-if="rsvpSuccess" class="p-success-note" :style="{ borderColor: primaryLight, color: primaryLight, fontFamily: fontHeading }">Terima kasih! RSVP Anda telah diterima.</div>
              <form v-else class="p-rsvp-form" @submit.prevent="submitRsvp">
                <div class="p-form-field"><label :style="{ color: primaryLight + 'cc', fontFamily: fontBody }">Nama Lengkap</label><input v-model="rsvpForm.guest_name" type="text" class="p-input" placeholder="Nama Anda" :style="{ color: bgColor, borderColor: primaryLight + '50', fontFamily: fontBody }" /></div>
                <div class="p-form-field"><label :style="{ color: primaryLight + 'cc', fontFamily: fontBody }">Kehadiran</label><div class="p-radio-group"><label v-for="opt in [{ val: 'hadir', label: 'Hadir' }, { val: 'tidak_hadir', label: 'Tidak Hadir' }, { val: 'ragu', label: 'Belum Pasti' } ]" :key="opt.val" class="p-radio-option" :class="{ selected: rsvpForm.attendance === opt.val }" :style="{ borderColor: rsvpForm.attendance === opt.val ? primaryLight : primaryLight + '40', color: rsvpForm.attendance === opt.val ? darkBg : primaryLight, background: rsvpForm.attendance === opt.val ? primaryLight : 'transparent', fontFamily: fontBody }"><input v-model="rsvpForm.attendance" class="sr-only" type="radio" :value="opt.val" />{{ opt.label }}</label></div></div>
                <div v-if="rsvpForm.attendance === 'hadir'" class="p-form-field"><label :style="{ color: primaryLight + 'cc', fontFamily: fontBody }">Jumlah Tamu</label><input v-model.number="rsvpForm.guest_count" type="number" min="1" max="20" class="p-input" :style="{ color: bgColor, borderColor: primaryLight + '50', fontFamily: fontBody }" /></div>
                <div class="p-form-field"><label :style="{ color: primaryLight + 'cc', fontFamily: fontBody }">Ucapan (opsional)</label><textarea v-model="rsvpForm.notes" rows="3" class="p-input p-textarea" placeholder="Doa & ucapan untuk mempelai..." :style="{ color: bgColor, borderColor: primaryLight + '50', fontFamily: fontBody }"></textarea></div>
                <p v-if="rsvpError" class="p-form-error" :style="{ fontFamily: fontBody }">{{ rsvpError }}</p>
                <button type="submit" class="p-submit-btn" :disabled="rsvpSubmitting" :style="{ background: primaryLight, color: darkBg, fontFamily: fontHeading }">{{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi' }}</button>
              </form>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('guest_messages', true)" class="p-section p-light" :style="{ background: bgColor }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: darkBg, fontFamily: fontHeading }">Doa & Ucapan</h2></div>
            <div v-if="!isDemo" :ref="el => vReveal(el)" class="p-reveal p-msg-form-wrap">
              <form class="p-msg-form" @submit.prevent="submitMessage">
                <input v-model="msgForm.name" type="text" placeholder="Nama Anda" class="p-input-light" :style="{ color: darkBg, borderColor: primary + '50', fontFamily: fontBody }" />
                <textarea v-model="msgForm.message" rows="3" placeholder="Tulis ucapan dan doa terbaik..." class="p-input-light p-textarea" :style="{ color: darkBg, borderColor: primary + '50', fontFamily: fontBody }"></textarea>
                <p v-if="msgError" class="p-form-error-light" :style="{ fontFamily: fontBody }">{{ msgError }}</p>
                <p v-if="msgSuccess" class="p-form-success-light" :style="{ color: primary, fontFamily: fontBody }">Ucapan berhasil dikirim ✓</p>
                <button type="submit" class="p-submit-btn-light" :disabled="msgSubmitting" :style="{ background: primary, color: bgColor, fontFamily: fontHeading }">{{ msgSubmitting ? 'Mengirim...' : 'Kirim Ucapan' }}</button>
              </form>
            </div>
            <div class="p-messages-list">
              <TransitionGroup name="p-msg"><div v-for="msg in localMessages" :key="msg.id" class="p-message-card"><p class="p-msg-name" :style="{ color: darkBg, fontFamily: fontHeading }">{{ msg.name }}</p><p class="p-msg-text" :style="{ color: accent, fontFamily: fontBody }">“{{ msg.message }}”</p><p class="p-msg-time" :style="{ color: primary + 'a6', fontFamily: fontBody }">{{ msg.created_at }}</p></div></TransitionGroup>
              <p v-if="!localMessages.length" class="p-empty-msg" :style="{ color: accent + '99', fontFamily: fontBody }">Belum ada ucapan. Jadilah yang pertama ✦</p>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('gift_info', false) && giftAccounts.length" class="p-section p-light" :style="{ background: bgColor }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: darkBg, fontFamily: fontHeading }">Wedding Gift</h2></div>
            <div class="p-gift-grid">
              <div v-for="(gift, i) in giftAccounts" :key="i" :ref="el => vReveal(el)" class="p-reveal p-gift-card">
                <p class="p-gift-bank" :style="{ color: primary, fontFamily: fontHeading }">{{ gift.bank ?? gift.label ?? 'Transfer' }}</p>
                <p class="p-gift-number" :style="{ color: darkBg, fontFamily: fontTitle }">{{ gift.account_number ?? '—' }}</p>
                <p class="p-gift-name" :style="{ color: accent, fontFamily: fontBody }">a.n. {{ gift.account_name ?? '—' }}</p>
                <button class="p-copy-btn" :style="{ borderColor: primary, color: primary, fontFamily: fontBody }" @click="copyText(gift.account_number ?? '')">Salin</button>
              </div>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('live_streaming', false) && livestreamUrl" class="p-section p-dark" :style="{ background: darkBg }">
          <div class="p-section-inner">
            <div :ref="el => vReveal(el)" class="p-reveal p-stream-card">
              <h2 class="p-title" :style="{ color: primaryLight, fontFamily: fontHeading }">{{ livestreamLabel }}</h2>
              <p :style="{ color: bgColor, fontFamily: fontBody }">{{ livestreamNote }}</p>
              <a :href="livestreamUrl" target="_blank" rel="noopener noreferrer" class="p-map-btn" :style="{ borderColor: primaryLight, color: primaryLight, fontFamily: fontBody }">Tonton Sekarang</a>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('dress_code_info', false) && additionalInfo.length" class="p-section p-light" :style="{ background: bgColor }">
          <div class="p-section-inner">
            <div class="p-title-block"><div class="p-divider small"></div><h2 class="p-title" :style="{ color: darkBg, fontFamily: fontHeading }">Informasi Tambahan</h2></div>
            <div class="p-info-grid">
              <div v-for="(item, i) in additionalInfo" :key="i" :ref="el => vReveal(el)" class="p-reveal p-info-card"><h3 :style="{ color: primary, fontFamily: fontHeading }">{{ item.title ?? 'Info' }}</h3><p :style="{ color: accent, fontFamily: fontBody }">{{ item.description ?? item.text ?? '' }}</p></div>
            </div>
          </div>
        </section>

        <section v-if="sectionEnabled('footer_closing', true)" class="p-section p-dark p-closing" :style="{ background: darkBg }">
          <div class="p-section-inner">
            <div :ref="el => vReveal(el)" class="p-reveal p-closing-content">
              <div class="p-divider"></div>
              <p class="p-body-copy" :style="{ color: bgColor + 'e0', fontFamily: fontBody }">{{ closingText }}</p>
              <p class="p-closing-label" :style="{ color: primaryLight + 'cc', fontFamily: fontBody }">DENGAN CINTA,</p>
              <h2 class="p-closing-names shimmer-gold" :style="{ fontFamily: fontTitle }">{{ groomName }} &amp; {{ brideName }}</h2>
              <p class="p-closing-blessing" :style="{ color: bgColor + 'cc', fontFamily: fontHeading }">Wassalamu'alaikum Warahmatullahi Wabarakatuh</p>
              <p class="p-footer" :style="{ color: primaryLight + '80', fontFamily: fontBody }">Undangan ini dibuat dengan penuh cinta menggunakan Theday</p>
            </div>
          </div>
        </section>

      </div></Transition>
    </div>
  </div>
</template>

<style scoped>
.p-root{--p-primary:v-bind(primary);--p-gold:v-bind(primaryLight);--p-dark:v-bind(darkBg);--p-bg:v-bind(bgColor);--p-accent:v-bind(accent);--p-title:v-bind(fontTitle);--p-heading:v-bind(fontHeading);--p-body:v-bind(fontBody);font-family:var(--p-body),sans-serif}
*{box-sizing:border-box}.p-overlay-leave-active{transition:opacity .8s ease}.p-overlay-leave-to{opacity:0}.p-fade-enter-active{transition:opacity .5s ease .8s}.p-fade-enter-from{opacity:0}.p-sections-enter-active{transition:opacity .9s ease}.p-sections-enter-from{opacity:0}
.p-opening,.p-section,.p-cover{position:relative;overflow:hidden}.p-opening{position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;cursor:pointer}.p-opening-texture,.p-section-pattern,.p-cover-bg{position:absolute;inset:0;background-image:radial-gradient(circle at 24% 22%,rgba(212,166,58,.1) 0,transparent 28%),linear-gradient(45deg,rgba(255,255,255,.03) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.03) 50%,rgba(255,255,255,.03) 75%,transparent 75%,transparent);background-size:220px 220px,18px 18px;pointer-events:none}
.p-gate{position:absolute;top:0;width:clamp(78px,22vw,210px);height:100%;transition:transform 1.2s cubic-bezier(.76,0,.24,1)}.p-gate-left{left:0}.p-gate-right{right:0}.p-gate-left.p-gate-opening{transform:translateX(-110%)}.p-gate-right.p-gate-opening{transform:translateX(110%)}.p-gate-panel{position:relative;width:100%;height:100%;background:linear-gradient(180deg,rgba(212,166,58,.08),transparent 15%),var(--p-dark);border-right:1px solid rgba(212,166,58,.4)}.p-gate-panel-right{border-right:0;border-left:1px solid rgba(212,166,58,.4)}.p-gonjong{height:116px;background:linear-gradient(180deg,var(--p-primary),#551721);clip-path:polygon(0 100%,18% 54%,34% 100%,50% 32%,66% 100%,82% 54%,100% 100%,100% 0,0 0)}.p-marawa{height:8px;margin:12px 16px 0;background:linear-gradient(90deg,#111 0 33.33%,#b21f2d 33.33% 66.66%,#d4a63a 66.66% 100%);border-radius:999px}.p-songket{position:absolute;left:16px;right:16px;top:150px;bottom:24px;border:1px solid rgba(212,166,58,.26);background:linear-gradient(90deg,rgba(212,166,58,.07),transparent 50%,rgba(212,166,58,.07)),repeating-linear-gradient(0deg,transparent 0 18px,rgba(212,166,58,.06) 18px 19px),repeating-linear-gradient(90deg,transparent 0 18px,rgba(212,166,58,.06) 18px 19px)}
.p-opening-center{position:relative;z-index:3;text-align:center;width:min(360px,calc(100% - 40px));padding:30px 22px;border:1px solid rgba(212,166,58,.34);background:rgba(28,20,18,.72);backdrop-filter:blur(6px);display:flex;flex-direction:column;align-items:center;gap:6px;transition:transform .8s ease,opacity .8s ease}.p-opening-center-reveal{transform:scale(1.04);opacity:.7}.p-bismillah{color:var(--p-gold);font-size:18px}.p-kicker,.p-cover-label,.p-cover-date,.p-opening-date,.p-closing-label,.p-footer{font-size:11px;letter-spacing:.16em;text-transform:uppercase}.p-divider{width:92px;height:1px;margin:8px auto;background:linear-gradient(90deg,transparent,var(--p-gold),transparent)}.p-divider.small{width:74px}.p-name,.p-closing-names{font-size:clamp(28px,7vw,40px);line-height:1.1;font-weight:500}.p-amp,.p-cover-amp{font-size:28px}.p-open-btn{margin-top:14px;padding:12px 24px;border:1px solid;background:transparent;text-transform:uppercase;letter-spacing:.16em;font-size:12px;display:inline-flex;gap:8px}.p-open-btn:hover{background:var(--p-gold);color:var(--p-dark)!important}
.p-music-btn{position:fixed;right:16px;bottom:24px;z-index:40;width:48px;height:48px;border-radius:999px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(0,0,0,.28)}.p-music-btn.is-playing{animation:spin-slow 4s linear infinite}
.p-main{overflow-x:hidden}.p-cover{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:72px 20px 28px}.p-marawa-top{position:absolute;top:0;left:0;right:0;height:10px;background:linear-gradient(90deg,#111 0 33.33%,#b21f2d 33.33% 66.66%,#d4a63a 66.66% 100%)}.p-rumah-frame{position:relative;width:min(430px,100%);padding-top:74px}.p-rumah-roof{position:absolute;inset:0 0 auto;height:96px;display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.p-rumah-roof span{background:linear-gradient(180deg,var(--p-primary),#591720);border-radius:0 0 8px 8px;clip-path:polygon(50% 0,100% 100%,0 100%)}.p-rumah-roof span:nth-child(2),.p-rumah-roof span:nth-child(3){transform:translateY(14px)}.p-cover-card{position:relative;background:rgba(248,240,230,.92);border:1px solid rgba(122,31,43,.16);padding:42px 24px 28px;text-align:center;box-shadow:0 16px 40px rgba(65,27,24,.08)}.p-cover-name{font-size:clamp(34px,9vw,52px);line-height:1.05;font-weight:500}.p-guest-pill{margin-top:16px;padding:10px 14px;border:1px solid;border-radius:999px;font-size:13px;background:rgba(255,255,255,.4)}
.p-section-inner{position:relative;z-index:1;max-width:760px;margin:0 auto;padding:64px 22px}.p-title-block,.p-closing-content{text-align:center}.p-title{font-size:clamp(28px,6vw,40px);margin-top:10px;line-height:1.1}.p-reveal{opacity:0;transform:translateY(26px);transition:opacity .8s ease,transform .8s ease}.p-visible{opacity:1;transform:translateY(0)}.p-intro-card,.p-quote-box,.p-stream-card,.p-video-card{max-width:580px;margin:0 auto;padding:28px 22px;text-align:center;border:1px solid rgba(212,166,58,.2);background:rgba(255,255,255,.04)}.p-body-copy{font-size:15px;line-height:1.9}.p-assalamu{font-size:18px;margin-bottom:16px}.p-quote{font-size:24px;line-height:1.6;font-style:italic}.p-quote-source{margin-top:12px;text-transform:uppercase;letter-spacing:.14em;font-size:11px}
.p-couple-grid{display:grid;grid-template-columns:1fr auto 1fr;gap:20px;align-items:center}.p-profile-card{padding:24px 18px;border:1px solid rgba(122,31,43,.14);background:rgba(255,255,255,.45);text-align:center}.p-profile-photo-wrap{width:120px;height:148px;padding:4px;background:linear-gradient(180deg,rgba(212,166,58,.7),rgba(122,31,43,.7));clip-path:polygon(50% 0,100% 14%,100% 100%,0 100%,0 14%);margin:0 auto}.p-profile-photo{width:100%;height:100%;object-fit:cover;display:block;clip-path:polygon(50% 0,100% 14%,100% 100%,0 100%,0 14%)}.p-profile-avatar{width:116px;height:116px;margin:0 auto;border-radius:999px;display:grid;place-items:center;background:rgba(122,31,43,.08);border:1px solid rgba(122,31,43,.15);font-size:34px}.p-profile-name{margin-top:16px;font-size:28px}.p-profile-meta{margin-top:8px;text-transform:uppercase;letter-spacing:.14em;font-size:11px}.p-profile-copy{margin-top:8px;font-size:14px;line-height:1.7}.p-couple-sep{display:flex;align-items:center;justify-content:center;font-size:30px}
.p-events-list,.p-story-list,.p-gift-grid,.p-info-grid{display:flex;flex-direction:column;gap:20px;margin-top:28px}.p-event-card,.p-story-card,.p-gift-card,.p-info-card,.p-map-card{position:relative;padding:22px 20px;border:1px solid rgba(212,166,58,.22);background:rgba(255,255,255,.04)}.p-event-songket{position:absolute;inset:0 auto 0 0;width:8px;background:repeating-linear-gradient(180deg,var(--p-gold) 0 12px,var(--p-primary) 12px 24px,#111 24px 36px)}.p-event-name{padding-left:10px;font-size:26px;margin-bottom:14px}.p-event-row{display:grid;grid-template-columns:88px 1fr;gap:12px;padding-left:10px;margin-bottom:10px;font-size:14px}.p-event-address,.p-event-note{padding-left:110px;font-size:13px;line-height:1.7}.p-event-note{margin-top:8px}
.p-countdown-wrap{text-align:center}.p-countdown-label{font-size:18px;margin-bottom:16px}.p-countdown-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.p-count-cell{padding:16px 8px;background:rgba(212,166,58,.08);border:1px solid rgba(212,166,58,.22)}.p-count-num{font-size:clamp(28px,7vw,40px);line-height:1}.p-count-text{font-size:11px;letter-spacing:.14em;text-transform:uppercase}
.p-map-card{display:grid;grid-template-columns:1fr 1fr;gap:18px;align-items:center}.p-map-preview{min-height:220px;background:linear-gradient(135deg,rgba(212,166,58,.18),rgba(122,31,43,.12));display:grid;place-items:center;border:1px solid rgba(122,31,43,.12)}.p-map-preview-inner{padding:14px 18px;border:1px solid rgba(122,31,43,.18);background:rgba(255,255,255,.55);font-family:var(--p-heading)}.p-map-note{margin-top:12px;font-size:13px;line-height:1.7}.p-map-btn,.p-copy-btn{display:inline-flex;align-items:center;justify-content:center;margin-top:16px;padding:10px 18px;border:1px solid;text-decoration:none;text-transform:uppercase;letter-spacing:.12em;font-size:12px;background:transparent}.p-map-btn:hover,.p-copy-btn:hover{background:rgba(212,166,58,.08)}
.p-story-item{display:grid;grid-template-columns:110px 1fr;gap:14px;align-items:start}.p-story-year{font-size:24px;line-height:1.1;padding-top:10px}.p-story-card h3,.p-gift-bank,.p-info-card h3{margin-bottom:8px}.p-video-thumb{display:grid;place-items:center;min-height:220px;margin-bottom:18px;background:linear-gradient(135deg,rgba(212,166,58,.12),rgba(122,31,43,.12));font-size:28px;font-family:var(--p-title);color:var(--p-gold)}
.p-form-wrap,.p-msg-form-wrap{max-width:500px;margin:0 auto}.p-demo-note,.p-success-note{padding:18px;border:1px solid;text-align:center}.p-rsvp-form,.p-msg-form{display:flex;flex-direction:column;gap:18px}.p-form-field{display:flex;flex-direction:column;gap:6px}.p-form-field label{font-size:11px;letter-spacing:.14em;text-transform:uppercase}.p-input,.p-input-light{width:100%;border:0;border-bottom:1px solid;background:transparent;padding:10px 4px;font-size:14px;outline:none}.p-textarea{min-height:86px;resize:vertical}.p-input::placeholder,.p-input-light::placeholder{color:inherit;opacity:.45}.p-radio-group{display:flex;flex-wrap:wrap;gap:10px}.p-radio-option{padding:10px 14px;border:1px solid;font-size:13px;cursor:pointer}.p-form-error,.p-form-error-light{color:#ef4444;font-size:13px}.p-form-success-light{font-size:13px}.p-submit-btn,.p-submit-btn-light{border:0;padding:14px 16px;text-transform:uppercase;letter-spacing:.16em;font-size:12px;font-weight:600}.p-submit-btn:disabled,.p-submit-btn-light:disabled{opacity:.5;cursor:not-allowed}
.p-messages-list{max-width:560px;margin:28px auto 0;display:flex;flex-direction:column;gap:14px;max-height:480px;overflow-y:auto}.p-message-card{padding:16px 18px;background:rgba(122,31,43,.05);border-left:3px solid var(--p-gold)}.p-msg-name{font-size:18px;margin-bottom:6px}.p-msg-text{font-size:14px;line-height:1.8}.p-msg-time{margin-top:8px;font-size:11px;letter-spacing:.1em;text-transform:uppercase}.p-empty-msg{text-align:center;padding:32px 0}.p-msg-enter-active{transition:all .5s ease}.p-msg-enter-from{opacity:0;transform:translateY(-14px)}
.p-gift-grid,.p-info-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.p-gift-number{font-size:26px;line-height:1.2}.p-gift-name{margin-top:8px}
.p-closing-content{max-width:560px;margin:0 auto}.p-closing-blessing{margin-top:12px;font-size:16px}.p-footer{margin-top:20px}
.shimmer-gold{background:linear-gradient(90deg,var(--p-primary) 0%,var(--p-gold) 28%,#ffe9a8 50%,var(--p-gold) 72%,var(--p-primary) 100%);background-size:200% auto;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;animation:shimmer 4s linear infinite}
@keyframes shimmer{0%{background-position:0 center}100%{background-position:200% center}}@keyframes spin-slow{to{transform:rotate(360deg)}}
@media (max-width:680px){.p-couple-grid,.p-map-card,.p-story-item{grid-template-columns:1fr}.p-couple-sep{padding:4px 0}.p-countdown-grid,.p-gift-grid,.p-info-grid{grid-template-columns:repeat(2,1fr)}.p-event-row{grid-template-columns:1fr;gap:4px}.p-event-address,.p-event-note{padding-left:10px}.p-section-inner{padding:52px 18px}}
@media (prefers-reduced-motion:reduce){.shimmer-gold,.p-music-btn.is-playing{animation:none}.p-reveal,.p-gate,.p-opening-center{transition:none}.p-reveal{opacity:1;transform:none}}
</style>
