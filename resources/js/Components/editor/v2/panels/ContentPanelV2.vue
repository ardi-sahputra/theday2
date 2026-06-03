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
});

const emit = defineEmits(['save-details', 'upload-photo', 'save-quote', 'save-event', 'toggle-section', 'set-gallery-layout']);

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
const summary = { pasangan: coupleSummary, foto: fotoSummary, galeri: galeriSummary, tanggal: tanggalSummary };

// ── Field handlers ────────────────────────────────────────────────
function onDetailInput() { emit('save-details'); }
function onDate(v) { if (!firstEvent.value) return; firstEvent.value.event_date = v; emit('save-event', firstEvent.value); }

const quote = computed(() => {
  if (!props.sectionsData.quote) props.sectionsData.quote = { data: { text: '', source: '' }, is_enabled: true };
  if (!props.sectionsData.quote.data) props.sectionsData.quote.data = { text: '', source: '' };
  return props.sectionsData.quote.data;
});
function onQuoteInput() { emit('save-quote'); }

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
          <div class="desc">Unggah maks 5MB · auto-kompres WebP · 1:1 disarankan</div>
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
      </div>
    </div>
  </div>
</template>

<style scoped>
.acc { border:1px solid var(--d-line,#D8DFD2); border-radius:14px; margin-bottom:10px; background:#fff; overflow:hidden; }
.acc-head { width:100%; display:flex; align-items:center; gap:10px; padding:13px 14px; background:transparent; border:none; cursor:pointer; font-family:inherit; text-align:left; }
.acc-head:hover { background:#FBFCF9; }
.acc-head.off .acc-title { color:#9aa6a0; }
.acc-head.off .acc-sum { color:#b3bcb4; font-style:italic; }
.acc-titles { flex:1; min-width:0; display:flex; flex-direction:column; }
.acc-title { font-family:'Cormorant','Cormorant Garamond',serif; font-size:17px; font-weight:600; color:var(--d-ink,#1F2A2E); line-height:1.1; }
.acc-sum { font-size:11.5px; color:var(--d-muted,#6C7A75); margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.acc-chev { color:#9aa6a0; flex-shrink:0; transition:transform .2s ease; }
.acc-chev.open { transform:rotate(180deg); }
.acc-body { padding:4px 14px 16px; border-top:1px solid #EEF1EC; }
.seg { display:flex; gap:4px; background:#F1F4EF; border-radius:10px; padding:3px; }
.seg-btn { flex:1; padding:7px 4px; border:none; background:transparent; border-radius:8px; font-size:12px; font-weight:600; color:#6C7A75; cursor:pointer; font-family:inherit; transition:all .12s; }
.seg-btn.on { background:#fff; color:#1F2A2E; box-shadow:0 1px 3px rgba(0,0,0,0.08); }
</style>
