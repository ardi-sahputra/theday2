<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useMoodboard } from '@/Composables/useMoodboard';
import MoodboardColorPicker from '@/Components/dashboard/moodboard/MoodboardColorPicker.vue';

const props = defineProps({
  moodboard: { type: Object, required: true },
  items:     { type: Array,  default: () => [] },
  couple:    { type: Object, default: () => ({}) },
  stats:     { type: Object, default: () => ({}) },
});

const board = useMoodboard(props);
const { moodboard, items, stats, pending } = board;

// ── Couple label ────────────────────────────────────────────────────────────
const coupleLabel = computed(() => {
  const g = props.couple?.groom, b = props.couple?.bride;
  return (g && b) ? `${g} & ${b}` : (g || b || 'Pasangan');
});

// ── Hero gradient derived from palette (darkened so white text reads) ─────────
function darken(hex, amt = 0.55) {
  const h = (hex || '').replace('#', '');
  if (h.length < 6) return null;
  const c = i => parseInt(h.slice(i, i + 2), 16);
  const m = v => Math.round(v * (1 - amt));
  return `rgb(${m(c(0))}, ${m(c(2))}, ${m(c(4))})`;
}
const heroGradient = computed(() => {
  const pal = (moodboard.value.palette || []).map(s => s.hex).filter(Boolean);
  const a = pal[0] ? darken(pal[0], 0.5) : '#2C3A30';
  const b = pal[1] ? darken(pal[1], 0.4) : (pal[0] ? darken(pal[0], 0.25) : '#566B53');
  return `linear-gradient(135deg, ${a} 0%, ${b} 100%)`;
});

// ── Concept editing ───────────────────────────────────────────────────────────
const editing = ref(false);
function onNameInput(e)  { board.saveBoard({ name: e.target.value }); }
function onNoteInput(e)  { board.saveBoard({ concept_note: e.target.value }); }

// ── Toast ─────────────────────────────────────────────────────────────────────
const toast = ref('');
let toastTimer = null;
function flash(msg) {
  toast.value = msg;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => (toast.value = ''), 1600);
}
async function copySwatch(hex) {
  await board.copyColor(hex);
  flash(`${hex} disalin`);
}

// ── Palette add popover ────────────────────────────────────────────────────────
const showAdd = ref(false);
const suggestions = computed(() => {
  const seen = new Set((moodboard.value.palette || []).map(s => (s.hex || '').toLowerCase()));
  const out = [];
  for (const it of items.value) {
    for (const c of (it.colors || [])) {
      const k = (c || '').toLowerCase();
      if (!seen.has(k)) { seen.add(k); out.push(c); }
    }
  }
  return out.slice(0, 12);
});
function onPickColor(hex) {
  if (board.addColor(hex)) { showAdd.value = false; flash('Warna ditambah'); }
  else flash('Tidak bisa (duplikat / maks 6)');
}

// ── Tag filter ─────────────────────────────────────────────────────────────────
const TAGS = [
  { key: null,       label: 'Semua' },
  { key: 'dekor',    label: 'Dekor' },
  { key: 'bunga',    label: 'Bunga' },
  { key: 'gaun',     label: 'Gaun' },
  { key: 'suasana',  label: 'Suasana' },
  { key: 'lainnya',  label: 'Lainnya' },
];
const activeTag = ref(null);
const visibleItems = computed(() =>
  activeTag.value ? items.value.filter(i => i.tag === activeTag.value) : items.value
);
const canReorder = computed(() => activeTag.value === null);

// ── Upload (button + drag-drop) ────────────────────────────────────────────────
const fileInput = ref(null);
const isDragFile = ref(false);

function pickFiles() { fileInput.value?.click(); }
async function handleFiles(fileList) {
  const files = Array.from(fileList || []).filter(f => f.type.startsWith('image/') && f.size <= 8 * 1024 * 1024);
  const rejected = (fileList?.length || 0) - files.length;
  if (rejected > 0) flash('Sebagian dilewati (hanya gambar, maks 8MB)');
  for (const f of files) {
    try { await board.addItem(f); } catch { flash('Gagal upload satu gambar'); }
  }
}
function onInputChange(e) { handleFiles(e.target.files); e.target.value = ''; }

function onDragOver(e) {
  if (e.dataTransfer?.types?.includes('Files')) { e.preventDefault(); isDragFile.value = true; }
}
function onDragLeave(e) {
  if (!e.currentTarget.contains(e.relatedTarget)) isDragFile.value = false;
}
function onDrop(e) {
  if (e.dataTransfer?.types?.includes('Files')) {
    e.preventDefault(); isDragFile.value = false;
    handleFiles(e.dataTransfer.files);
  }
}

// ── Pin reorder (HTML5 DnD) ─────────────────────────────────────────────────────
const dragId = ref(null);
function onPinDragStart(id) { if (canReorder.value) dragId.value = id; }
function onPinDragOver(e) { if (dragId.value) e.preventDefault(); }
function onPinDrop(targetId) {
  if (!dragId.value || dragId.value === targetId) { dragId.value = null; return; }
  const ids = items.value.map(i => i.id);
  const from = ids.indexOf(dragId.value);
  const to   = ids.indexOf(targetId);
  if (from < 0 || to < 0) { dragId.value = null; return; }
  ids.splice(to, 0, ids.splice(from, 1)[0]);
  board.reorderItems(ids).catch(() => flash('Gagal simpan urutan'));
  dragId.value = null;
}

// ── Pin edit modal ──────────────────────────────────────────────────────────────
const editItem = ref(null); // { id, caption, tag }
function openEdit(it) { editItem.value = { id: it.id, caption: it.caption || '', tag: it.tag || '' }; }
async function saveEdit() {
  const it = editItem.value;
  try { await board.updateItem(it.id, { caption: it.caption || null, tag: it.tag || null }); }
  catch { flash('Gagal simpan'); }
  editItem.value = null;
}
async function removeItem(id) {
  if (!confirm('Hapus gambar ini dari moodboard?')) return;
  try { await board.deleteItem(id); } catch { flash('Gagal hapus'); }
}

const TAG_LABEL = { dekor: 'Dekor', bunga: 'Bunga', gaun: 'Gaun', suasana: 'Suasana', lainnya: 'Lainnya' };
</script>

<template>
  <Head title="Moodboard" />
  <DashboardLayout>
    <template #header>
      <h1 class="text-base font-semibold text-stone-800">Moodboard</h1>
    </template>

    <div class="mb-mood max-w-6xl mx-auto">
      <!-- ── HERO ───────────────────────────────────────────────────────── -->
      <div class="mb-hero" :style="{ background: heroGradient }">
        <button type="button" class="mb-hero-edit" @click="editing = !editing">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
          {{ editing ? 'Selesai' : 'Edit konsep' }}
        </button>

        <div class="mb-hero-label">Tema Visual · {{ coupleLabel }}</div>

        <template v-if="editing">
          <input class="mb-hero-name-input" :value="moodboard.name" @input="onNameInput"
                 placeholder="Beri nama konsep — mis. Sage & Earthy Romance" maxlength="80" />
          <textarea class="mb-hero-note-input" :value="moodboard.concept_note" @input="onNoteInput"
                    rows="2" maxlength="500" placeholder="Deskripsikan vibe-nya: hangat, natural, sentuhan dried flower…"></textarea>
        </template>
        <template v-else>
          <div class="mb-hero-name">{{ moodboard.name || 'Moodboard Pernikahan' }}</div>
          <div v-if="moodboard.concept_note" class="mb-hero-note">{{ moodboard.concept_note }}</div>
          <div v-else class="mb-hero-note mb-hero-note--empty" @click="editing = true">+ tambah deskripsi konsep</div>
        </template>

        <div class="mb-hero-stats">
          <span>✦ <b>{{ stats.count }}</b> inspirasi</span>
          <span>✦ <b>{{ stats.categories }}</b> kategori</span>
          <span v-if="stats.dibuatBerdua">✦ dibuat berdua</span>
        </div>
      </div>

      <!-- ── PALETTE BAR ────────────────────────────────────────────────── -->
      <div class="mb-palette">
        <span class="mb-palette-title">Palet</span>
        <div class="mb-swatches">
          <button v-for="s in moodboard.palette" :key="s.hex" type="button"
                  class="mb-swatch" :style="{ background: s.hex }" :title="`${s.hex} — klik untuk salin`"
                  @click="copySwatch(s.hex)">
            <span class="mb-swatch-x" @click.stop="board.removeColor(s.hex)">×</span>
          </button>
          <div class="mb-swatch-add-wrap">
            <button type="button" class="mb-swatch-add" @click="showAdd = !showAdd"
                    :disabled="moodboard.palette.length >= board.MAX_PALETTE">+</button>
            <div v-if="showAdd" class="mb-add-pop">
              <MoodboardColorPicker :suggestions="suggestions" @pick="onPickColor" />
            </div>
          </div>
        </div>
        <span class="mb-palette-hint">klik swatch = salin hex</span>
        <button type="button" class="mb-add-img" @click="pickFiles">+ Tambah Gambar</button>
        <input ref="fileInput" type="file" accept="image/*" multiple class="hidden" @change="onInputChange" />
      </div>

      <!-- ── TAG FILTER ─────────────────────────────────────────────────── -->
      <div class="mb-tags">
        <button v-for="t in TAGS" :key="t.label" type="button"
                :class="['mb-tag', activeTag === t.key ? 'is-on' : '']" @click="activeTag = t.key">
          {{ t.label }}
        </button>
      </div>

      <!-- ── BOARD (masonry + drop zone) ────────────────────────────────── -->
      <div class="mb-board" :class="{ 'is-drag': isDragFile }"
           @dragover="onDragOver" @dragleave="onDragLeave" @drop="onDrop">

        <div v-if="isDragFile" class="mb-droplay">⤓ Lepas foto untuk upload</div>

        <!-- Empty state -->
        <div v-if="!visibleItems.length && !pending.length" class="mb-empty">
          <div class="mb-empty-ic">🖼️</div>
          <p class="mb-empty-title">{{ activeTag ? 'Belum ada di kategori ini' : 'Belum ada inspirasi' }}</p>
          <p class="mb-empty-sub">Tarik & lepas foto ke sini, atau klik tombol di bawah.</p>
          <button type="button" class="mb-add-img" @click="pickFiles">+ Tambah Gambar Pertama</button>
        </div>

        <!-- Masonry grid -->
        <div v-else class="mb-masonry">
          <div v-for="it in visibleItems" :key="it.id" class="mb-pin"
               :draggable="canReorder" @dragstart="onPinDragStart(it.id)"
               @dragover="onPinDragOver" @drop="onPinDrop(it.id)"
               :class="{ 'is-dragging': dragId === it.id }">
            <img :src="it.image_url" :alt="it.caption || 'inspirasi'" loading="lazy" />
            <div class="mb-pin-ov">
              <span v-if="it.tag" class="mb-pin-tag">{{ TAG_LABEL[it.tag] || it.tag }}</span>
              <div class="mb-pin-actions">
                <button type="button" title="Edit" @click="openEdit(it)">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                </button>
                <button type="button" title="Hapus" @click="removeItem(it.id)">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                </button>
              </div>
              <div v-if="it.caption" class="mb-pin-cap">{{ it.caption }}</div>
            </div>
          </div>

          <!-- pending placeholders -->
          <div v-for="p in pending" :key="p.tempId" class="mb-pin mb-pin--loading">
            <img :src="p.preview" alt="" />
            <div class="mb-pin-spin"><span class="mb-spin"></span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <Transition name="mb-toast">
      <div v-if="toast" class="mb-toast">{{ toast }}</div>
    </Transition>

    <!-- Pin edit modal -->
    <Teleport to="body">
      <div v-if="editItem" class="mb-modal-bg" @click.self="editItem = null">
        <div class="mb-modal">
          <h3>Detail gambar</h3>
          <label class="mb-lbl">Caption</label>
          <input v-model="editItem.caption" class="mb-inp" maxlength="140" placeholder="mis. pelaminan rustic" />
          <label class="mb-lbl">Kategori</label>
          <select v-model="editItem.tag" class="mb-inp">
            <option value="">— tanpa kategori —</option>
            <option value="dekor">Dekor</option>
            <option value="bunga">Bunga</option>
            <option value="gaun">Gaun</option>
            <option value="suasana">Suasana</option>
            <option value="lainnya">Lainnya</option>
          </select>
          <div class="mb-modal-act">
            <button type="button" class="mb-btn-ghost" @click="editItem = null">Batal</button>
            <button type="button" class="mb-btn-dark" @click="saveEdit">Simpan</button>
          </div>
        </div>
      </div>
    </Teleport>
  </DashboardLayout>
</template>

<style scoped>
.mb-mood { padding: 4px 2px 40px; }

/* Hero */
.mb-hero { position: relative; border-radius: 18px; padding: 26px 28px; color: #fff; box-shadow: 0 14px 40px -18px rgba(31,42,46,.55); overflow: hidden; }
.mb-hero-edit { position: absolute; top: 16px; right: 16px; display: inline-flex; align-items: center; gap: 6px; font: 600 11px system-ui; color: #fff; background: rgba(255,255,255,.14); border: none; padding: 7px 12px; border-radius: 9px; cursor: pointer; }
.mb-hero-edit:hover { background: rgba(255,255,255,.24); }
.mb-hero-label { font: 700 10px system-ui; letter-spacing: .2em; text-transform: uppercase; color: rgba(255,255,255,.7); }
.mb-hero-name { font: 600 30px/1.12 'Cormorant Garamond','Playfair Display',Georgia,serif; margin: 9px 0 7px; }
.mb-hero-note { font: italic 14px/1.55 'Cormorant Garamond',Georgia,serif; color: rgba(255,255,255,.86); max-width: 480px; }
.mb-hero-note--empty { font-style: normal; opacity: .65; cursor: pointer; font-size: 12.5px; }
.mb-hero-name-input { width: 100%; max-width: 480px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25); border-radius: 10px; padding: 8px 12px; color: #fff; font: 600 22px 'Cormorant Garamond',Georgia,serif; margin: 9px 0 8px; outline: none; }
.mb-hero-name-input::placeholder { color: rgba(255,255,255,.5); }
.mb-hero-note-input { width: 100%; max-width: 480px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25); border-radius: 10px; padding: 8px 12px; color: #fff; font: 13px system-ui; outline: none; resize: vertical; }
.mb-hero-note-input::placeholder { color: rgba(255,255,255,.5); }
.mb-hero-stats { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 16px; font: 600 11.5px system-ui; color: rgba(255,255,255,.82); }
.mb-hero-stats b { color: #fff; }

/* Palette bar */
.mb-palette { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px 4px 2px; }
.mb-palette-title { font: 700 10px system-ui; letter-spacing: .08em; text-transform: uppercase; color: #6C7A75; }
.mb-swatches { display: flex; align-items: center; gap: 7px; }
.mb-swatch { position: relative; width: 28px; height: 28px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 0 0 1px rgba(0,0,0,.07); }
.mb-swatch-x { position: absolute; top: -6px; right: -6px; width: 15px; height: 15px; border-radius: 50%; background: #1F2A2E; color: #fff; font: 700 10px/15px system-ui; text-align: center; opacity: 0; transition: opacity .12s; }
.mb-swatch:hover .mb-swatch-x { opacity: 1; }
.mb-swatch-add-wrap { position: relative; }
.mb-swatch-add { width: 28px; height: 28px; border-radius: 8px; border: 1.5px dashed #b9c2b3; background: #fff; color: #8a958d; font: 16px system-ui; cursor: pointer; line-height: 1; }
.mb-swatch-add:disabled { opacity: .4; cursor: not-allowed; }
.mb-add-pop { position: absolute; z-index: 30; top: 36px; left: 0; background: #fff; border: 1px solid #e3e7df; border-radius: 12px; box-shadow: 0 14px 34px -12px rgba(0,0,0,.25); padding: 12px; }
.mb-palette-hint { font: 11px system-ui; color: #9aa6a0; }
.mb-add-img { margin-left: auto; font: 600 11px system-ui; color: #fff; background: #1F2A2E; border: none; padding: 9px 13px; border-radius: 10px; cursor: pointer; }
.mb-add-img:hover { background: #2c3a30; }

/* Tag filter */
.mb-tags { display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 4px 10px; }
.mb-tag { font: 600 11.5px system-ui; color: #6b7280; background: #fff; border: 1px solid #e2e5df; padding: 6px 13px; border-radius: 999px; cursor: pointer; }
.mb-tag.is-on { background: #1F2A2E; color: #fff; border-color: #1F2A2E; }

/* Board */
.mb-board { position: relative; border-radius: 14px; min-height: 220px; transition: background .15s, box-shadow .15s; }
.mb-board.is-drag { background: #F1F5EE; box-shadow: inset 0 0 0 2px #b6c4ad; }
.mb-droplay { position: absolute; inset: 0; z-index: 20; display: grid; place-items: center; font: 700 13px system-ui; color: #5d6f59; pointer-events: none; }

.mb-masonry { columns: 4; column-gap: 12px; padding: 4px; }
@media (max-width: 1023px) { .mb-masonry { columns: 3; } }
@media (max-width: 639px)  { .mb-masonry { columns: 2; column-gap: 9px; } }

.mb-pin { position: relative; break-inside: avoid; margin-bottom: 12px; border-radius: 12px; overflow: hidden; background: #eef0ea; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.mb-pin img { width: 100%; display: block; }
.mb-pin[draggable="true"] { cursor: grab; }
.mb-pin.is-dragging { opacity: .4; }
.mb-pin-ov { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(31,42,46,.28) 0%, transparent 28%, transparent 60%, rgba(31,42,46,.55) 100%); opacity: 0; transition: opacity .15s; }
.mb-pin:hover .mb-pin-ov { opacity: 1; }
.mb-pin-tag { position: absolute; left: 8px; bottom: 8px; font: 600 9.5px system-ui; color: #fff; background: rgba(31,42,46,.72); padding: 3px 8px; border-radius: 6px; }
.mb-pin-cap { position: absolute; left: 8px; right: 8px; bottom: 8px; font: 500 10px system-ui; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,.5); }
.mb-pin-tag + .mb-pin-cap, .mb-pin-cap:not(:first-child) { display: none; }
.mb-pin-actions { position: absolute; top: 8px; right: 8px; display: flex; gap: 5px; }
.mb-pin-actions button { width: 27px; height: 27px; border-radius: 7px; border: none; background: rgba(255,255,255,.92); color: #1F2A2E; display: grid; place-items: center; cursor: pointer; }
.mb-pin-actions button:hover { background: #fff; }

.mb-pin--loading img { filter: blur(1px) brightness(.85); }
.mb-pin-spin { position: absolute; inset: 0; display: grid; place-items: center; }
.mb-spin { width: 22px; height: 22px; border: 3px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: mb-rot .7s linear infinite; }
@keyframes mb-rot { to { transform: rotate(360deg); } }

/* Empty */
.mb-empty { text-align: center; padding: 48px 20px; }
.mb-empty-ic { font-size: 34px; }
.mb-empty-title { font: 700 15px system-ui; color: #3f4a45; margin-top: 8px; }
.mb-empty-sub { font: 12.5px system-ui; color: #94a09a; margin: 4px 0 16px; }

/* Toast */
.mb-toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 80; background: #1F2A2E; color: #fff; font: 600 12.5px system-ui; padding: 10px 16px; border-radius: 10px; box-shadow: 0 10px 30px -8px rgba(0,0,0,.4); }
.mb-toast-enter-active, .mb-toast-leave-active { transition: opacity .2s, transform .2s; }
.mb-toast-enter-from, .mb-toast-leave-to { opacity: 0; transform: translate(-50%, 8px); }

/* Modal */
.mb-modal-bg { position: fixed; inset: 0; z-index: 90; background: rgba(0,0,0,.45); display: grid; place-items: center; padding: 16px; }
.mb-modal { width: 100%; max-width: 360px; background: #fff; border-radius: 16px; padding: 20px; }
.mb-modal h3 { font: 700 15px system-ui; color: #1F2A2E; margin-bottom: 12px; }
.mb-lbl { display: block; font: 600 11px system-ui; color: #6b7280; margin: 10px 0 4px; }
.mb-inp { width: 100%; border: 1px solid #e2e5df; border-radius: 9px; padding: 9px 11px; font: 13px system-ui; outline: none; }
.mb-inp:focus { border-color: #92A89C; }
.mb-modal-act { display: flex; gap: 8px; margin-top: 16px; }
.mb-btn-ghost { flex: 1; font: 600 12.5px system-ui; color: #555; background: #f1f1ee; border: none; padding: 10px; border-radius: 10px; cursor: pointer; }
.mb-btn-dark { flex: 1; font: 600 12.5px system-ui; color: #fff; background: #1F2A2E; border: none; padding: 10px; border-radius: 10px; cursor: pointer; }
</style>
