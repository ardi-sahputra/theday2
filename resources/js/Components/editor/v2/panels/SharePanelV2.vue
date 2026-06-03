<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  slug:         { type: String, default: '' },
  invitationId: { type: String, default: '' },
  config:       { type: Object, default: () => ({}) }, // reactive custom_config; wa_template lives here
  onUpdateSlug: { type: Function, default: null },      // (slug) => Promise<newSlug>
  status:       { type: String, default: 'draft' },     // draft | published — link only opens when published
});

const emit = defineEmits([
  'save-config',  // (patch) — persist custom_config patch
  'publish',      // () — request publish so the link goes live
]);

// A draft invitation 404s for guests; surface that so nobody shares a dead link.
const isLive = computed(() => props.status === 'published');

const shareUrl = computed(() => `${window.location.origin}/${props.slug}`);

// ── Custom slug editor ───────────────────────────────────────────
const SLUG_RE = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
const draft   = ref(props.slug);
const status  = ref('idle');   // idle | invalid | checking | ok | taken | saving | saved | error
const suggestion = ref(null);
const errorMsg   = ref('');
watch(() => props.slug, (v) => { draft.value = v; status.value = 'idle'; });

function sanitize(e) {
  draft.value = e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '').replace(/-{2,}/g, '-');
}

let chkTimer = null;
watch(draft, (v) => {
  suggestion.value = null; errorMsg.value = '';
  clearTimeout(chkTimer);
  if (v === props.slug) { status.value = 'idle'; return; }
  if (v.length < 3 || !SLUG_RE.test(v)) { status.value = 'invalid'; return; }
  status.value = 'checking';
  chkTimer = setTimeout(async () => {
    try {
      const { data } = await axios.get('/api/invitations/check-slug', { params: { slug: v, exclude_id: props.invitationId } });
      if (draft.value !== v) return;            // outdated response
      status.value = data.available ? 'ok' : 'taken';
      suggestion.value = data.suggestion;
    } catch { status.value = 'idle'; }
  }, 400);
});

function useSuggestion() { if (suggestion.value) draft.value = suggestion.value; }

const slugInput = ref(null);
function focusSlug() { slugInput.value?.focus(); }

async function saveSlug() {
  if (status.value !== 'ok' || !props.onUpdateSlug) return;
  status.value = 'saving';
  try {
    await props.onUpdateSlug(draft.value);
    status.value = 'saved';
    setTimeout(() => { if (status.value === 'saved') status.value = 'idle'; }, 1500);
  } catch (e) {
    status.value = 'error';
    errorMsg.value = e?.response?.data?.message ?? 'Gagal menyimpan. Coba lagi.';
  }
}

const copied = ref(false);
async function copyLink() {
  try {
    await navigator.clipboard.writeText(shareUrl.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
  } catch (_) { /* clipboard unavailable */ }
}

</script>

<template>
  <div>
    <!-- Status link: aktif vs belum dipublikasikan -->
    <div class="live-banner" :class="isLive ? 'on' : 'off'">
      <span class="live-dot"></span>
      <div style="flex:1;">
        <div class="live-title">{{ isLive ? 'Link aktif' : 'Link belum aktif' }}</div>
        <div class="live-sub">{{ isLive ? 'Tamu bisa buka link ini sekarang.' : 'Tamu akan lihat halaman error sampai kamu Publikasikan.' }}</div>
      </div>
      <button v-if="!isLive" type="button" class="btn btn-primary btn-sm" style="flex-shrink:0;" @click="emit('publish')">Publikasikan</button>
    </div>

    <!-- Link Undangan (custom slug) -->
    <div class="section-block">
      <h4>Link Undangan</h4>
      <div class="desc">Klik untuk ubah jadi nama kalian — gampang diingat & dibagikan.</div>

      <label class="label" style="display:block;margin-bottom:6px;">Link kustom</label>
      <div class="slugbox" :class="status" @click="focusSlug">
        <span class="slug-host">theday.id/</span>
        <input ref="slugInput" class="slug-input" :value="draft" @input="sanitize"
               spellcheck="false" autocapitalize="off" autocomplete="off" placeholder="ayu-rizki" />
        <span class="slug-ic">
          <svg v-if="status==='checking'||status==='saving'" class="ev-spin" style="width:15px;height:15px;border-width:2px;"></svg>
          <svg v-else-if="status==='ok'||status==='saved'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3f8a5c" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-11"/></svg>
          <svg v-else-if="status==='taken'||status==='invalid'||status==='error'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c0625a" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18L18 6M6 6l12 12"/></svg>
          <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9aa6a0" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
        </span>
        <button type="button" class="slug-copy" @click.stop="copyLink" :title="copied ? 'Tersalin' : 'Salin link'">
          <svg v-if="!copied" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6F8270" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6F8270" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-11"/></svg>
        </button>
      </div>

      <div class="slug-msg">
        <span v-if="status==='checking'" style="color:var(--muted);">Mengecek ketersediaan…</span>
        <span v-else-if="status==='ok'" style="color:#3f8a5c;">✓ Tersedia — klik <strong>Simpan link</strong></span>
        <span v-else-if="status==='saved'" style="color:#3f8a5c;">✓ Link tersimpan</span>
        <span v-else-if="status==='invalid'" style="color:#c0625a;">Min 3 karakter · huruf kecil, angka, atau dash.</span>
        <span v-else-if="status==='error'" style="color:#c0625a;">{{ errorMsg }}</span>
        <span v-else-if="status==='taken'" style="color:#c0625a;">
          Sudah dipakai.<template v-if="suggestion"> Coba: <button type="button" class="slug-sugg" @click="useSuggestion">{{ suggestion }}</button></template>
        </span>
        <span v-else style="color:var(--muted);">Ketik untuk ganti link. Link lama tetap aktif (auto-redirect).</span>
      </div>

      <button v-if="status==='ok' || status==='saving'" type="button" class="btn btn-primary btn-sm" style="margin-top:10px;width:100%;justify-content:center;" :disabled="status==='saving'" @click="saveSlug">
        {{ status==='saving' ? 'Menyimpan…' : 'Simpan link' }}
      </button>
    </div>

    <!-- Kelola tamu & sebar personal → semua diurus di menu Tamu -->
    <div class="section-block">
      <h4>Sebar ke tamu</h4>
      <div class="desc">Daftar tamu, kirim WhatsApp personal (nama tiap tamu otomatis), & link khusus per tamu diatur di menu Tamu.</div>
      <a href="/dashboard/guest-list" class="guest-cta">
        <span class="guest-cta-ic">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6F8270" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"/><circle cx="17" cy="10" r="2.5"/><path d="M14 20c0-2 1-3.5 3-3.5s3 1.5 3 3.5"/></svg>
        </span>
        <span style="flex:1;min-width:0;">
          <span class="guest-cta-title">Kelola &amp; sebar ke tamu</span>
          <span class="guest-cta-sub">Tambah/import daftar tamu, kirim undangan WhatsApp personal</span>
        </span>
        <svg class="guest-cta-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9aa6a0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
      </a>
    </div>
  </div>
</template>

<style scoped>
.live-banner { display:flex; align-items:center; gap:10px; padding:11px 13px; border-radius:12px; margin-bottom:16px; border:1px solid; }
.live-banner.on  { background:rgba(63,138,92,0.08);  border-color:rgba(63,138,92,0.25); }
.live-banner.off { background:rgba(214,158,46,0.10);  border-color:rgba(214,158,46,0.30); }
.live-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.live-banner.on  .live-dot { background:#3f8a5c; box-shadow:0 0 0 3px rgba(63,138,92,0.18); }
.live-banner.off .live-dot { background:#d69e2e; box-shadow:0 0 0 3px rgba(214,158,46,0.18); }
.live-title { font-size:12.5px; font-weight:700; color:var(--ink,#1F2A2E); }
.live-sub   { font-size:11px; color:var(--muted,#6C7A75); line-height:1.35; margin-top:1px; }
.slugbox { display:flex; align-items:center; gap:2px; background:#fff; border:1.5px solid var(--line-2,#C7D0BE); border-radius:10px; padding:6px 8px 6px 12px; cursor:text; transition:border-color .15s, box-shadow .15s; }
.slugbox:hover { border-color:var(--sage,#92A89C); }
.slugbox:focus-within { border-color:var(--sage,#92A89C); box-shadow:0 0 0 3px rgba(146,168,156,0.18); }
.slugbox.ok:focus-within   { border-color:#5aa775; box-shadow:0 0 0 3px rgba(90,167,117,0.18); }
.slugbox.taken, .slugbox.invalid, .slugbox.error { border-color:#e0b3ae; }
.slug-host { font-family:'JetBrains Mono',monospace; font-size:12.5px; color:var(--muted,#6C7A75); flex-shrink:0; }
.slug-input { flex:1; min-width:0; border:none; outline:none; font-family:'JetBrains Mono',monospace; font-size:12.5px; font-weight:600; color:var(--ink,#1F2A2E); padding:3px 7px; border-radius:6px; background:rgba(146,168,156,0.10); }
.slug-input:focus { background:rgba(146,168,156,0.18); }
.slug-input::placeholder { color:#aab4ae; font-weight:400; }
.slug-ic { display:flex; align-items:center; justify-content:center; width:20px; flex-shrink:0; }
.slug-copy { width:30px; height:30px; border-radius:8px; background:transparent; border:none; display:grid; place-items:center; cursor:pointer; flex-shrink:0; }
.slug-copy:hover { background:var(--sage-soft,#DCE4D3); }
.slug-msg { font-size:11.5px; margin-top:7px; min-height:15px; line-height:1.4; }
.slug-sugg { border:none; background:rgba(63,138,92,0.12); color:#3f8a5c; font-weight:700; cursor:pointer; font-family:'JetBrains Mono',monospace; font-size:11.5px; padding:1px 6px; border-radius:5px; }
.slug-sugg:hover { background:rgba(63,138,92,0.2); }

.guest-cta { display:flex; align-items:center; gap:12px; padding:13px 14px; border:1.5px solid var(--line-2,#C7D0BE); border-radius:12px; background:#fff; text-decoration:none; transition:border-color .15s, box-shadow .15s, background .15s; }
.guest-cta:hover { border-color:var(--sage,#92A89C); background:rgba(146,168,156,0.05); box-shadow:0 1px 0 rgba(146,168,156,0.12); }
.guest-cta-ic { width:38px; height:38px; flex-shrink:0; border-radius:10px; background:var(--sage-soft,#DCE4D3); display:grid; place-items:center; }
.guest-cta-title { display:block; font-size:13px; font-weight:700; color:var(--ink,#1F2A2E); }
.guest-cta-sub { display:block; font-size:11px; color:var(--muted,#6C7A75); line-height:1.4; margin-top:2px; }
.guest-cta-arrow { flex-shrink:0; }
</style>
