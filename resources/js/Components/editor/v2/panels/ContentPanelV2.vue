<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  details:      { type: Object, required: true }, // reactive: groom_name, bride_name, *_parent_names, *_photo_url
  sectionsData: { type: Object, required: true }, // reactive: { quote: { data: { text, source } } }
  events:       { type: Array,  default: () => [] },
  caps:         { type: Object, default: () => ({}) }, // template capability flags
});

const emit = defineEmits([
  'save-details',          // debounced text save
  'upload-photo',          // (side, file)
  'save-quote',            // debounced quote save
  'save-event',            // (event) — persist date onto first event
]);

// Couple ────────────────────────────────────────────────────────────────────
function onDetailInput() { emit('save-details'); }

// Date — bound to the first event's event_date (details has no date field) ────
const firstEvent = computed(() => props.events[0] ?? null);
const dateParts = computed(() => {
  const d = firstEvent.value?.event_date;
  if (!d) return { d: '', m: '', y: '' };
  const [y, m, day] = d.split('-');
  return { d: day ?? '', m: m ?? '', y: y ?? '' };
});
const day   = ref(dateParts.value.d);
const month = ref(dateParts.value.m);
const year  = ref(dateParts.value.y);

function onDateInput() {
  if (!firstEvent.value) return;
  const pad = (v, n) => String(v || '').padStart(n, '0');
  if (!day.value || !month.value || !year.value) return;
  firstEvent.value.event_date = `${pad(year.value, 4)}-${pad(month.value, 2)}-${pad(day.value, 2)}`;
  emit('save-event', firstEvent.value);
}

// Quote ───────────────────────────────────────────────────────────────────────
const quote = computed(() => {
  if (!props.sectionsData.quote) props.sectionsData.quote = { data: { text: '', source: '' }, is_enabled: true };
  if (!props.sectionsData.quote.data) props.sectionsData.quote.data = { text: '', source: '' };
  return props.sectionsData.quote.data;
});
function onQuoteInput() { emit('save-quote'); }

// Photos ──────────────────────────────────────────────────────────────────────
const groomInput = ref(null);
const brideInput = ref(null);
function pickGroom() { groomInput.value?.click(); }
function pickBride() { brideInput.value?.click(); }
function onGroomFile(e) { const f = e.target.files?.[0]; if (f) emit('upload-photo', 'groom', f); e.target.value = ''; }
function onBrideFile(e) { const f = e.target.files?.[0]; if (f) emit('upload-photo', 'bride', f); e.target.value = ''; }
</script>

<template>
  <div>
    <!-- Pasangan -->
    <div class="section-block">
      <h4>Pasangan</h4>
      <div class="desc">Nama ini juga dipakai di dashboard & dokumen kalian.</div>
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
      <div v-if="caps.parents !== false" class="field-row">
        <div class="field">
          <label class="label">Putra dari</label>
          <input class="input" v-model="details.groom_parent_names" @input="onDetailInput" placeholder="Bp. … & Ibu …" />
        </div>
        <div class="field">
          <label class="label">Putri dari</label>
          <input class="input" v-model="details.bride_parent_names" @input="onDetailInput" placeholder="Bp. … & Ibu …" />
        </div>
      </div>
    </div>

    <!-- Tanggal & Salam Pembuka -->
    <div class="section-block">
      <h4>Tanggal &amp; Salam Pembuka</h4>
      <div class="field-row-3">
        <div class="field">
          <label class="label">Tanggal</label>
          <input class="input" type="number" min="1" max="31" v-model="day" @input="onDateInput" :disabled="!firstEvent" placeholder="DD" />
        </div>
        <div class="field">
          <label class="label">Bulan</label>
          <input class="input" type="number" min="1" max="12" v-model="month" @input="onDateInput" :disabled="!firstEvent" placeholder="MM" />
        </div>
        <div class="field">
          <label class="label">Tahun</label>
          <input class="input" type="number" v-model="year" @input="onDateInput" :disabled="!firstEvent" placeholder="YYYY" />
        </div>
      </div>
      <p v-if="!firstEvent" class="help" style="margin-top:-8px;margin-bottom:14px;">Tambahkan acara di tab Acara untuk mengatur tanggal.</p>
      <div v-if="caps.quote !== false" class="field" style="margin-bottom:0;">
        <label class="label">Kutipan / Quote</label>
        <textarea class="textarea" v-model="quote.text" @input="onQuoteInput"></textarea>
        <div class="help">Akan tampil di section Kisah Kami / Quote</div>
      </div>
    </div>

    <!-- Foto Pengantin -->
    <div v-if="caps.photos !== false" class="section-block">
      <h4>Foto Pengantin</h4>
      <div class="desc">Unggah maks 5MB · JPG/PNG/WebP · 1:1 disarankan</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <button type="button" class="uploader" @click="pickGroom"
                :style="details.groom_photo_url ? `background-image:url(${details.groom_photo_url});background-size:cover;background-position:center;border-style:solid;` : ''">
          <template v-if="!details.groom_photo_url">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CAB8E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/></svg>
            <span style="font-size:11px;color:var(--muted);">Foto Pria</span>
          </template>
        </button>
        <button type="button" class="uploader" @click="pickBride"
                :style="details.bride_photo_url ? `background-image:url(${details.bride_photo_url});background-size:cover;background-position:center;border-style:solid;` : ''">
          <template v-if="!details.bride_photo_url">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CAB8E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/></svg>
            <span style="font-size:11px;color:var(--muted);">Foto Wanita</span>
          </template>
        </button>
      </div>
      <input ref="groomInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onGroomFile" />
      <input ref="brideInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onBrideFile" />
    </div>
  </div>
</template>
