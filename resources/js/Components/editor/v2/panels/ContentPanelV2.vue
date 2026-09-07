<script setup>
import { computed, ref, reactive } from 'vue';
import DateTimeField from '@/Components/ui/DateTimeField.vue';
import GalleryPanelV2 from '@/Components/editor/v2/panels/GalleryPanelV2.vue';

const props = defineProps({
  details:       { type: Object, required: true },
  sectionsData:  { type: Object, required: true },
  events:        { type: Array,  default: () => [] },
  galleries:     { type: Array,  default: () => [] },
  caps:          { type: Object, default: () => ({}) },
  onUploadPhoto: { type: Function, default: null },  // (side, file) → Promise
  onAddGallery:  { type: Function, default: null },  // (file) → Promise
  onDeleteGallery:{ type: Function, default: null }, // (gallery) → Promise
  galleryLayout: { type: String, default: 'grid' },
  onUploadImage: { type: Function, default: null },  // (file) → Promise<url>
});

const emit = defineEmits(['save-details', 'upload-photo', 'save-quote', 'save-event', 'toggle-section', 'set-gallery-layout', 'save-section']);

const LAYOUTS = [
  { key: 'grid', label: 'Grid' },
  { key: 'masonry', label: 'Masonry' },
  { key: 'carousel', label: 'Carousel' },
  { key: 'polaroid', label: 'Polaroid' },
];

// ── Accordion state ───────────────────────────────────────────────
const openCard = ref('pasangan');
function toggle(key) { openCard.value = openCard.value === key ? null : key; }

// toggleKey → this card maps to a section that can be turned on/off in the header.
const cards = computed(() => [
  { key: 'pasangan', title: 'Pasangan',  show: true },
  { key: 'foto',     title: 'Foto Pengantin', show: props.caps.photos !== false },
  { key: 'galeri',   title: 'Galeri Foto', show: props.caps.gallery !== false, toggleKey: 'gallery' },
  { key: 'tanggal',  title: 'Tanggal & Quote', show: true },
  { key: 'love_story',      title: 'Kisah Kami',    show: props.caps.loveStory     !== false, toggleKey: 'love_story' },
  { key: 'gift',            title: 'Hadiah',        show: props.caps.gift          !== false, toggleKey: 'gift' },
  { key: 'live_streaming',  title: 'Live Streaming',show: props.caps.liveStreaming !== false, toggleKey: 'live_streaming' },
  { key: 'video',           title: 'Video',         show: props.caps.video         !== false, toggleKey: 'video' },
  { key: 'additional_info', title: 'Info Tambahan', show: props.caps.additionalInfo!== false, toggleKey: 'additional_info' },
].filter(c => c.show));

// Section on/off (default ON when no record — matches the renderer).
function isOn(key) { return props.sectionsData?.[key]?.is_enabled ?? true; }
function onToggleSection(key) { emit('toggle-section', key); }

// Header summaries
const coupleSummary = computed(() => {
  const g = props.details.groom_name?.trim(), b = props.details.bride_name?.trim();
  return (g || b) ? [g, b].filter(Boolean).join(' & ') : 'Belum diisi';
});
const fotoSummary = computed(() => {
  const n = [props.details.groom_photo_url, props.details.bride_photo_url].filter(Boolean).length;
  return n ? `${n}/2 foto` : 'Belum ada foto';
});
const galeriSummary = computed(() => props.galleries.length ? `${props.galleries.length} foto` : 'Belum ada foto');
const firstEvent = computed(() => props.events[0] ?? null);
const tanggalSummary = computed(() => {
  const d = firstEvent.value?.event_date;
  if (!d) return 'Belum diatur';
  const [y, m, day] = d.split('-');
  return `${day}-${m}-${y}`;
});
const storySummary  = computed(() => story.value.stories.length ? `${story.value.stories.length} momen` : 'Belum ada momen');
const giftSummary   = computed(() => gift.value.accounts.length ? `${gift.value.accounts.length} rekening` : 'Belum ada rekening');
const streamSummary = computed(() => stream.value.url ? 'Link siaran diisi' : 'Belum ada link');
const videoSummary  = computed(() => video.value.url ? 'Link video diisi' : 'Belum ada video');
const infoSummary   = computed(() => extraInfo.value.text?.trim() ? 'Sudah diisi' : 'Belum diisi');
const summary = {
  pasangan: coupleSummary, foto: fotoSummary, galeri: galeriSummary, tanggal: tanggalSummary,
  love_story: storySummary, gift: giftSummary, live_streaming: streamSummary,
  video: videoSummary, additional_info: infoSummary,
};

// ── Field handlers ────────────────────────────────────────────────
function onDetailInput() { emit('save-details'); }
function onDate(v) { if (!firstEvent.value) return; firstEvent.value.event_date = v; emit('save-event', firstEvent.value); }

const quote = computed(() => {
  if (!props.sectionsData.quote) props.sectionsData.quote = { data: { text: '', source: '' }, is_enabled: true };
  if (!props.sectionsData.quote.data) props.sectionsData.quote.data = { text: '', source: '' };
  return props.sectionsData.quote.data;
});
function onQuoteInput() { emit('save-quote'); }

// ── Section data (Kisah Kami / Hadiah / Live / Video / Info) ──────
// Seed the shape on first read so v-model has something to bind to. Keys match
// what the templates read (stories / accounts / url …), not the old form components.
function sec(key, shape) {
  if (!props.sectionsData[key]) props.sectionsData[key] = { data: {}, is_enabled: true };
  if (!props.sectionsData[key].data) props.sectionsData[key].data = {};
  const d = props.sectionsData[key].data;
  for (const [field, fallback] of Object.entries(shape)) {
    if (d[field] === undefined) d[field] = Array.isArray(fallback) ? [] : fallback;
  }
  return d;
}

const story     = computed(() => sec('love_story', { stories: [] }));
const gift      = computed(() => sec('gift', { accounts: [] }));
const stream    = computed(() => sec('live_streaming', { platform: 'youtube', url: '' }));
const video     = computed(() => sec('video', { url: '', caption: '' }));
const extraInfo = computed(() => sec('additional_info', { text: '' }));

function saveSection(key) { emit('save-section', key); }

// Kisah Kami
const openStory = ref(null);
const storyUploading = reactive({});
const storyError = ref(null);

function addStory() {
  story.value.stories.push({ date: '', title: '', description: '', photo_url: '' });
  openStory.value = story.value.stories.length - 1;
  saveSection('love_story');
}
function removeStory(i) { story.value.stories.splice(i, 1); saveSection('love_story'); }
function moveStory(i, dir) {
  const arr = story.value.stories;
  const j = i + dir;
  if (j < 0 || j >= arr.length) return;
  [arr[i], arr[j]] = [arr[j], arr[i]];
  saveSection('love_story');
}
async function uploadStoryPhoto(i, e) {
  const file = e.target.files?.[0];
  e.target.value = '';
  if (!file || !props.onUploadImage) return;
  storyError.value = null;
  storyUploading[i] = true;
  try {
    const url = await props.onUploadImage(file);
    if (url) { story.value.stories[i].photo_url = url; saveSection('love_story'); }
    else storyError.value = 'Upload foto gagal. Coba lagi.';
  } catch {
    storyError.value = 'Upload foto gagal. Coba lagi.';
  } finally {
    storyUploading[i] = false;
  }
}
function clearStoryPhoto(i) { story.value.stories[i].photo_url = ''; saveSection('love_story'); }

// Hadiah
function addAccount() {
  gift.value.accounts.push({ bank: '', account_number: '', account_name: '' });
  saveSection('gift');
}
function removeAccount(i) { gift.value.accounts.splice(i, 1); saveSection('gift'); }

// Photos
const uploading = reactive({ groom: false, bride: false });

const groomInput = ref(null);
const brideInput = ref(null);
function pickGroom() { groomInput.value?.click(); }
function pickBride() { brideInput.value?.click(); }
async function uploadSide(side, e) {
  const f = e.target.files?.[0]; e.target.value = '';
  if (!f) return;
  uploading[side] = true;
  try { if (props.onUploadPhoto) await props.onUploadPhoto(side, f); else emit('upload-photo', side, f); }
  finally { uploading[side] = false; }
}
function onGroomFile(e) { uploadSide('groom', e); }
function onBrideFile(e) { uploadSide('bride', e); }
</script>

<template>
  <div>
    <div v-for="c in cards" :key="c.key" class="acc">
      <div class="acc-head" :class="{ off: c.toggleKey && !isOn(c.toggleKey) }" @click="toggle(c.key)">
        <span class="acc-titles">
          <span class="acc-title">{{ c.title }}</span>
          <span class="acc-sum">{{ c.toggleKey && !isOn(c.toggleKey) ? 'Disembunyikan' : summary[c.key].value }}</span>
        </span>
        <button v-if="c.toggleKey" type="button" :class="['toggle-sw', isOn(c.toggleKey) ? 'on' : '']"
                role="switch" :aria-checked="isOn(c.toggleKey)" title="Tampilkan/sembunyikan section"
                @click.stop="onToggleSection(c.toggleKey)"></button>
        <svg class="acc-chev" :class="{ open: openCard === c.key }" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
      </div>

      <div v-show="openCard === c.key" class="acc-body">
        <!-- Pasangan -->
        <template v-if="c.key === 'pasangan'">
          <div class="field-row">
            <div class="field">
              <label class="label">Nama Pengantin Pria</label>
              <input class="input" v-model="details.groom_name" @input="onDetailInput" />
            </div>
            <div class="field">
              <label class="label">Nama Pengantin Wanita</label>
              <input class="input" v-model="details.bride_name" @input="onDetailInput" />
            </div>
          </div>
          <div class="field-row">
            <div class="field">
              <label class="label">Panggilan Pria</label>
              <input class="input" v-model="details.groom_nickname" @input="onDetailInput" maxlength="20" placeholder="mis. Rizki" />
            </div>
            <div class="field">
              <label class="label">Panggilan Wanita</label>
              <input class="input" v-model="details.bride_nickname" @input="onDetailInput" maxlength="20" placeholder="mis. Ayu" />
            </div>
          </div>
          <div v-if="caps.instagram !== false" class="field-row">
            <div class="field">
              <label class="label">Instagram Pria</label>
              <input class="input" v-model="details.groom_instagram" @input="onDetailInput" placeholder="@username" />
            </div>
            <div class="field">
              <label class="label">Instagram Wanita</label>
              <input class="input" v-model="details.bride_instagram" @input="onDetailInput" placeholder="@username" />
            </div>
          </div>
          <div v-if="caps.parents !== false" class="field-row" style="margin-bottom:0;">
            <div class="field" style="margin-bottom:0;">
              <label class="label">Putra dari</label>
              <input class="input" v-model="details.groom_parent_names" @input="onDetailInput" placeholder="Bp. … & Ibu …" />
            </div>
            <div class="field" style="margin-bottom:0;">
              <label class="label">Putri dari</label>
              <input class="input" v-model="details.bride_parent_names" @input="onDetailInput" placeholder="Bp. … & Ibu …" />
            </div>
          </div>
        </template>

        <!-- Foto Pengantin -->
        <template v-else-if="c.key === 'foto'">
          <div class="desc">Unggah maks 5MB · 1:1 disarankan</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <button type="button" class="uploader" @click="pickGroom" :disabled="uploading.groom"
                    :style="details.groom_photo_url ? `background-image:url(${details.groom_photo_url});background-size:cover;background-position:center;border-style:solid;` : ''">
              <template v-if="!details.groom_photo_url && !uploading.groom">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CAB8E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/></svg>
                <span style="font-size:11px;color:var(--muted);">Foto Pria</span>
              </template>
              <div v-if="uploading.groom" class="uploader-busy"><span class="ev-spin"></span><span>Mengonversi…</span></div>
            </button>
            <button type="button" class="uploader" @click="pickBride" :disabled="uploading.bride"
                    :style="details.bride_photo_url ? `background-image:url(${details.bride_photo_url});background-size:cover;background-position:center;border-style:solid;` : ''">
              <template v-if="!details.bride_photo_url && !uploading.bride">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CAB8E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/></svg>
                <span style="font-size:11px;color:var(--muted);">Foto Wanita</span>
              </template>
              <div v-if="uploading.bride" class="uploader-busy"><span class="ev-spin"></span><span>Mengonversi…</span></div>
            </button>
          </div>
          <input ref="groomInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onGroomFile" />
          <input ref="brideInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onBrideFile" />
        </template>

        <!-- Galeri (embedded, bare) -->
        <template v-else-if="c.key === 'galeri'">
          <div class="field">
            <label class="label">Tata letak</label>
            <div class="seg">
              <button v-for="l in LAYOUTS" :key="l.key" type="button"
                      :class="['seg-btn', galleryLayout === l.key ? 'on' : '']"
                      @click="emit('set-gallery-layout', l.key)">{{ l.label }}</button>
            </div>
          </div>
          <GalleryPanelV2 bare :galleries="galleries" :caps="caps" :on-add="onAddGallery" :on-delete="onDeleteGallery" />
        </template>

        <!-- Tanggal & Quote -->
        <template v-else-if="c.key === 'tanggal'">
          <div class="field" :style="firstEvent ? '' : 'opacity:.55;pointer-events:none;'">
            <label class="label">Tanggal</label>
            <DateTimeField :date="firstEvent?.event_date || ''" label="Tanggal pernikahan" @update:date="onDate" />
          </div>
          <p v-if="!firstEvent" class="help" style="margin-top:-8px;margin-bottom:14px;">Tambahkan acara di tab Acara untuk mengatur tanggal.</p>
          <div v-if="caps.quote !== false" class="field" style="margin-bottom:0;">
            <label class="label">Kutipan / Quote</label>
            <textarea class="textarea" v-model="quote.text" @input="onQuoteInput"></textarea>
            <div class="help">Akan tampil di section Kisah Kami / Quote</div>
          </div>
        </template>

        <!-- Kisah Kami -->
        <template v-else-if="c.key === 'love_story'">
          <div v-for="(s, i) in story.stories" :key="i" class="item">
            <div class="item-head" @click="openStory = openStory === i ? null : i">
              <span class="item-title">{{ s.title || 'Momen ' + (i + 1) }}</span>
              <span class="item-actions">
                <button type="button" class="mini" title="Naikkan" :disabled="i === 0" @click.stop="moveStory(i, -1)">↑</button>
                <button type="button" class="mini" title="Turunkan" :disabled="i === story.stories.length - 1" @click.stop="moveStory(i, 1)">↓</button>
                <button type="button" class="mini danger" @click.stop="removeStory(i)">Hapus</button>
              </span>
            </div>
            <div v-show="openStory === i" class="item-body">
              <div class="field">
                <label class="label">Judul momen</label>
                <input class="input" v-model="s.title" @input="saveSection('love_story')" placeholder="mis. Pertama Bertemu" />
              </div>
              <div class="field">
                <label class="label">Tanggal</label>
                <input class="input" type="date" v-model="s.date" @input="saveSection('love_story')" />
              </div>
              <div class="field">
                <label class="label">Cerita</label>
                <textarea class="textarea" rows="3" v-model="s.description" @input="saveSection('love_story')"
                          placeholder="Ceritakan momen ini…"></textarea>
              </div>
              <div class="field" style="margin-bottom:0;">
                <label class="label">Foto (opsional)</label>
                <div v-if="s.photo_url" class="thumb-row">
                  <img :src="s.photo_url" alt="" class="thumb" />
                  <button type="button" class="mini danger" @click="clearStoryPhoto(i)">Hapus foto</button>
                </div>
                <label v-else class="upload">
                  <input type="file" accept="image/*" hidden @change="uploadStoryPhoto(i, $event)" />
                  {{ storyUploading[i] ? 'Mengunggah…' : '+ Pilih foto' }}
                </label>
              </div>
            </div>
          </div>
          <p v-if="storyError" class="err">{{ storyError }}</p>
          <button type="button" class="add" @click="addStory">+ Tambah Momen</button>
        </template>

        <!-- Hadiah -->
        <template v-else-if="c.key === 'gift'">
          <div v-for="(a, i) in gift.accounts" :key="i" class="item">
            <div class="item-head static">
              <span class="item-title">{{ a.bank || 'Rekening ' + (i + 1) }}</span>
              <button type="button" class="mini danger" @click="removeAccount(i)">Hapus</button>
            </div>
            <div class="item-body">
              <div class="field">
                <label class="label">Bank / e-wallet</label>
                <input class="input" v-model="a.bank" @input="saveSection('gift')" placeholder="mis. BCA, GoPay" />
              </div>
              <div class="field">
                <label class="label">Nomor rekening</label>
                <input class="input" v-model="a.account_number" @input="saveSection('gift')" placeholder="1234567890" />
              </div>
              <div class="field" style="margin-bottom:0;">
                <label class="label">Atas nama</label>
                <input class="input" v-model="a.account_name" @input="saveSection('gift')" placeholder="Nama pemilik rekening" />
              </div>
            </div>
          </div>
          <p v-if="!gift.accounts.length" class="hint">Belum ada rekening.</p>
          <button type="button" class="add" @click="addAccount">+ Tambah Rekening</button>
        </template>

        <!-- Live streaming -->
        <template v-else-if="c.key === 'live_streaming'">
          <div class="field">
            <label class="label">Platform</label>
            <select class="input" v-model="stream.platform" @change="saveSection('live_streaming')">
              <option value="youtube">YouTube</option>
              <option value="instagram">Instagram</option>
              <option value="zoom">Zoom</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div class="field" style="margin-bottom:0;">
            <label class="label">Link siaran</label>
            <input class="input" v-model="stream.url" @input="saveSection('live_streaming')" placeholder="https://youtube.com/live/…" />
            <p class="help">Tamu yang tidak bisa hadir menonton lewat link ini.</p>
          </div>
        </template>

        <!-- Video -->
        <template v-else-if="c.key === 'video'">
          <div class="field">
            <label class="label">Link video</label>
            <input class="input" v-model="video.url" @input="saveSection('video')" placeholder="https://youtube.com/watch?v=…" />
          </div>
          <div class="field" style="margin-bottom:0;">
            <label class="label">Caption (opsional)</label>
            <input class="input" v-model="video.caption" @input="saveSection('video')" placeholder="mis. Prewedding kami" />
          </div>
        </template>

        <!-- Info tambahan -->
        <template v-else-if="c.key === 'additional_info'">
          <div class="field" style="margin-bottom:0;">
            <label class="label">Catatan untuk tamu</label>
            <textarea class="textarea" rows="4" v-model="extraInfo.text" @input="saveSection('additional_info')"
                      placeholder="mis. Dress code earth tone, mohon hadir 15 menit lebih awal."></textarea>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* .acc* live in app.css (.ev2 scope) — shared with the Bagian tab. */
.item { border:1px solid #EEF1EC; border-radius:12px; margin-bottom:8px; background:#FBFCF9; overflow:hidden; }
.item-head { display:flex; align-items:center; gap:8px; padding:9px 11px; cursor:pointer; }
.item-head.static { cursor:default; }
.item-title { flex:1; min-width:0; font-size:12.5px; font-weight:600; color:#1F2A2E; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.item-actions { display:flex; gap:4px; flex-shrink:0; }
.item-body { padding:2px 11px 10px; }
.mini { border:1px solid #DDE4DA; background:#fff; border-radius:7px; padding:3px 7px; font-size:11px; color:#6C7A75; cursor:pointer; font-family:inherit; }
.mini:disabled { opacity:.4; cursor:default; }
.mini.danger { color:#B4524A; border-color:#EBD5D2; }
.add { width:100%; padding:9px; border:1px dashed #CBD5C6; border-radius:10px; background:transparent; font-size:12.5px; color:#6C7A75; cursor:pointer; font-family:inherit; }
.add:hover { border-color:#92A89C; color:#3D4A4D; }
.hint { font-size:11.5px; color:#9aa6a0; text-align:center; padding:6px 0 10px; }
.err { font-size:11.5px; color:#B4524A; padding:2px 0 8px; }
.thumb-row { display:flex; align-items:center; gap:8px; }
.thumb { width:56px; height:56px; object-fit:cover; border-radius:8px; border:1px solid #E3E9DF; }
.upload { display:block; text-align:center; padding:9px; border:1px dashed #CBD5C6; border-radius:10px; font-size:12.5px; color:#6C7A75; cursor:pointer; }
.upload:hover { border-color:#92A89C; }
.seg { display:flex; gap:4px; background:#F1F4EF; border-radius:10px; padding:3px; }

.seg-btn { flex:1; padding:7px 4px; border:none; background:transparent; border-radius:8px; font-size:12px; font-weight:600; color:#6C7A75; cursor:pointer; font-family:inherit; transition:all .12s; }
.seg-btn.on { background:#fff; color:#1F2A2E; box-shadow:0 1px 3px rgba(0,0,0,0.08); }
</style>
