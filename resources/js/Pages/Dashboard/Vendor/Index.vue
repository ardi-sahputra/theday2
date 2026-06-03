<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useVendors } from '@/Composables/useVendors';
import VendorModal from '@/Components/dashboard/vendor/VendorModal.vue';

const props = defineProps({
  vendors:       { type: Array, default: () => [] },
  stats:         { type: Object, default: () => ({}) },
  gapCategories: { type: Array, default: () => [] },
  categories:    { type: Array, default: () => [] },
});

const vm = useVendors(props);
const { vendors, categories, stats, gapCategories, usedCategories, saving } = vm;

// ── Category visuals ────────────────────────────────────────────────────────
const CAT_META = {
  venue:             { emoji: '📍', color: '#6F8270' },
  catering:          { emoji: '🍽️', color: '#D9A24A' },
  foto_video:        { emoji: '📷', color: '#4A5A4C' },
  dekorasi:          { emoji: '🌸', color: '#C19089' },
  mua:               { emoji: '💄', color: '#D9B5B0' },
  busana:            { emoji: '👗', color: '#9CAB8E' },
  mc:                { emoji: '🎤', color: '#C9A45B' },
  wedding_organizer: { emoji: '🗂️', color: '#6E7F94' },
  sound_system:      { emoji: '🔊', color: '#6F8270' },
  mobil_pengantin:   { emoji: '🚗', color: '#B98E7A' },
  hiburan:           { emoji: '🎶', color: '#6F8270' },
  souvenir:          { emoji: '🎁', color: '#D9A24A' },
  lainnya:           { emoji: '✦', color: '#9aa6a0' },
};
const catMeta = (k) => CAT_META[k] || CAT_META.lainnya;

const STATUS_META = {
  lunas:  { bg: 'rgba(156,171,142,.18)', fg: '#4A5A4C', dot: '#9CAB8E' },
  dp:     { bg: 'rgba(217,162,74,.18)',  fg: '#8E6515', dot: '#D9A24A' },
  booked: { bg: 'rgba(108,122,117,.15)', fg: '#3D4A4D', dot: '#6C7A75' },
};
const statusMeta = (k) => STATUS_META[k] || STATUS_META.booked;

function rupiahShort(n) {
  n = Number(n) || 0;
  if (n >= 1e9) return 'Rp ' + (n / 1e9).toFixed(n % 1e9 ? 1 : 0) + 'M';
  if (n >= 1e6) return 'Rp ' + (n / 1e6).toFixed(n % 1e6 ? 1 : 0) + 'jt';
  return 'Rp ' + n.toLocaleString('id-ID');
}
function waLink(phone) {
  let d = (phone || '').replace(/\D/g, '');
  if (!d) return null;
  if (d.startsWith('0')) d = '62' + d.slice(1);
  return `https://wa.me/${d}`;
}

// ── Filter ──────────────────────────────────────────────────────────────────
const activeCat = ref(null);
const filtered = computed(() => activeCat.value ? vendors.value.filter(v => v.category === activeCat.value) : vendors.value);
const lunasPct = computed(() => stats.value.total ? Math.round(stats.value.lunas / stats.value.total * 100) : 0);

// ── Modal ───────────────────────────────────────────────────────────────────
const showModal = ref(false);
const editing   = ref(null);
function openAdd()  { editing.value = null; showModal.value = true; }
function openEdit(v){ editing.value = v;    showModal.value = true; }

const toast = ref('');
let toastTimer = null;
function flash(m) { toast.value = m; clearTimeout(toastTimer); toastTimer = setTimeout(() => (toast.value = ''), 1800); }

async function onSubmit(payload) {
  try {
    if (editing.value) { await vm.updateVendor(editing.value.id, payload); flash('Vendor diperbarui'); }
    else               { await vm.addVendor(payload); flash('Vendor ditambah'); }
    showModal.value = false;
  } catch (e) {
    flash(e?.response?.data?.message || 'Gagal menyimpan vendor');
  }
}
async function onDelete(v) {
  if (!confirm(`Hapus vendor "${v.name}"?`)) return;
  try { await vm.deleteVendor(v.id); flash('Vendor dihapus'); }
  catch { flash('Gagal menghapus'); }
}
</script>

<template>
  <Head title="Vendor" />
  <DashboardLayout>
    <template #header>
      <h1 class="text-base font-semibold text-stone-800">Vendor</h1>
    </template>

    <div class="vp">
      <!-- Header -->
      <div class="vp-head">
        <div>
          <h1 class="vp-title">Vendor</h1>
          <p class="vp-sub">{{ stats.total }} vendor · semua kontak, kontrak &amp; pembayaran di satu tempat.</p>
        </div>
        <button type="button" class="vp-add" @click="openAdd">+ Tambah Vendor</button>
      </div>

      <!-- Stat strip -->
      <div class="vp-stats">
        <div class="vp-stat"><div class="lbl">Total Vendor</div><div class="v">{{ stats.total }}</div><div class="d muted">dari {{ usedCategories.length }} kategori</div></div>
        <div class="vp-stat"><div class="lbl">Lunas</div><div class="v" style="color:#4A5A4C;">{{ stats.lunas }}</div><div class="d sage">✓ {{ lunasPct }}%</div></div>
        <div class="vp-stat"><div class="lbl">DP / Cicilan</div><div class="v" style="color:#8E6515;">{{ stats.dp }}</div><div class="d amber">menunggu pelunasan</div></div>
        <div class="vp-stat"><div class="lbl">Total Komitmen</div><div class="v">{{ rupiahShort(stats.total_committed) }}</div><div class="d muted">{{ rupiahShort(stats.total_paid) }} terbayar</div></div>
      </div>

      <div class="vp-grid-wrap">
        <!-- main column -->
        <div>
          <!-- filter -->
          <div class="vp-filter" v-if="vendors.length">
            <button type="button" :class="['vp-chip', activeCat === null ? 'on' : '']" @click="activeCat = null">
              Semua <span class="vp-chip-n">{{ vendors.length }}</span>
            </button>
            <button v-for="c in usedCategories" :key="c.key" type="button"
                    :class="['vp-chip', activeCat === c.key ? 'on' : '']" @click="activeCat = c.key">
              {{ c.label }} <span class="vp-chip-n">{{ c.count }}</span>
            </button>
          </div>

          <!-- empty -->
          <div v-if="!vendors.length" class="vp-empty">
            <div class="vp-empty-ic">🤝</div>
            <p class="vp-empty-t">Belum ada vendor</p>
            <p class="vp-empty-s">Catat venue, catering, MUA, dan lainnya — lengkap dengan kontak, kontrak &amp; pembayaran.</p>
            <button type="button" class="vp-add" @click="openAdd">+ Tambah Vendor Pertama</button>
          </div>

          <!-- vendor cards -->
          <div v-else class="vp-cards">
            <div v-for="v in filtered" :key="v.id" class="vp-card">
              <div class="vp-card-top">
                <div class="vp-logo" :style="{ background: catMeta(v.category).color }">{{ catMeta(v.category).emoji }}</div>
                <div style="flex:1; min-width:0;">
                  <div class="vp-row1">
                    <div style="min-width:0;">
                      <div class="vp-name">{{ v.name }}</div>
                      <div class="vp-meta">
                        <span class="vp-tag">{{ v.category_label }}</span>
                        <span v-if="v.rating" class="vp-rating">★ {{ v.rating }}</span>
                      </div>
                    </div>
                    <span class="vp-status" :style="{ background: statusMeta(v.status_key).bg, color: statusMeta(v.status_key).fg }">
                      <span class="vp-dot" :style="{ background: statusMeta(v.status_key).dot }"></span>{{ v.status_label }}
                    </span>
                  </div>
                  <div class="vp-contact">
                    <span v-if="v.pic_name">👤 {{ v.pic_name }}</span>
                    <span v-if="v.phone" class="mono">📞 {{ v.phone }}</span>
                  </div>
                </div>
              </div>

              <div v-if="v.total_cost" class="vp-pay">
                <div class="vp-pay-row">
                  <span class="vp-pay-lbl">Pembayaran</span>
                  <span class="vp-pay-val mono">{{ rupiahShort(v.paid_amount) }} <span class="muted">/ {{ rupiahShort(v.total_cost) }}</span></span>
                </div>
                <div class="vp-bar"><div class="vp-bar-fill" :style="{ width: v.paid_pct + '%', background: catMeta(v.category).color }"></div></div>
              </div>

              <div class="vp-foot">
                <div class="vp-next"><span class="muted">Next:</span> <strong v-if="v.next_action">{{ v.next_action }}</strong><span v-else class="muted">—</span></div>
                <div class="vp-actions">
                  <a v-if="waLink(v.phone)" :href="waLink(v.phone)" target="_blank" class="vp-act" title="WhatsApp" style="color:#25723f;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.5 15.3L2 22l4.8-1.3A10 10 0 1012 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8 0-1.3.7-2 .9-2.2.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 1.9c.1.2.1.4 0 .5l-.4.6c-.2.2-.3.4-.1.7.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.2.1.4.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.8.9c.3.1.4.2.5.3.1.2.1.6-.1 1z"/></svg>
                  </a>
                  <a v-if="v.contract_url" :href="v.contract_url" target="_blank" class="vp-act" title="Kontrak">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h4"/></svg>
                  </a>
                  <button type="button" class="vp-act" title="Edit" @click="openEdit(v)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg>
                  </button>
                  <button type="button" class="vp-act" title="Hapus" @click="onDelete(v)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2m2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- rail -->
        <aside class="vp-rail">
          <div v-if="gapCategories.length" class="vp-gap">
            <div class="vp-gap-h">
              <h3>Kategori Belum Lengkap</h3>
              <p>{{ gapCategories.length }} kategori penting belum ada vendor</p>
            </div>
            <div class="vp-gap-list">
              <button v-for="g in gapCategories" :key="g.key" type="button" class="vp-gap-item" @click="openAdd">
                <div>
                  <div class="vp-gap-name">{{ g.label }}</div>
                  <div class="vp-gap-why">{{ g.why }}</div>
                </div>
                <span class="vp-gap-add">+ Tambah</span>
              </button>
            </div>
          </div>

          <div class="vp-rec">
            <div class="vp-rec-h"><h3>Rekomendasi Vendor</h3><span class="vp-soon">Segera Hadir</span></div>
            <p class="vp-rec-p">Nanti kamu bisa jelajahi vendor populer langsung dari sini.</p>
          </div>
        </aside>
      </div>
    </div>

    <VendorModal :open="showModal" :vendor="editing" :categories="categories" :saving="saving"
                 @close="showModal = false" @submit="onSubmit" />

    <Transition name="vp-toast">
      <div v-if="toast" class="vp-toast">{{ toast }}</div>
    </Transition>
  </DashboardLayout>
</template>

<style scoped>
.vp { padding: 4px 2px 40px; }
.mono { font-family: 'JetBrains Mono', monospace; }
.muted { color: #94a09a; }
.sage { color: #4A5A4C; }
.amber { color: #8E6515; }

.vp-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.vp-title { font: 600 24px 'Cormorant Garamond',Georgia,serif; color: #1F2A2E; }
.vp-sub { font: 12.5px system-ui; color: #8a958d; margin-top: 2px; }
.vp-add { font: 600 12px system-ui; color: #fff; background: #1F2A2E; border: none; padding: 10px 14px; border-radius: 10px; cursor: pointer; white-space: nowrap; }
.vp-add:hover { background: #2c3a30; }

.vp-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
.vp-stat { background: #fff; border: 1px solid #e7e9e3; border-radius: 14px; padding: 14px 16px; }
.vp-stat .lbl { font: 600 10.5px system-ui; letter-spacing: .04em; text-transform: uppercase; color: #8a958d; }
.vp-stat .v { font: 600 26px 'Cormorant Garamond',Georgia,serif; color: #1F2A2E; margin: 4px 0 2px; }
.vp-stat .d { font: 11px system-ui; }

.vp-grid-wrap { display: grid; grid-template-columns: minmax(0,1fr) 300px; gap: 18px; }

.vp-filter { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 16px; }
.vp-chip { font: 500 12px system-ui; color: #6b7280; background: #fff; border: 1px solid #e2e5df; padding: 6px 12px; border-radius: 999px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
.vp-chip.on { background: #1F2A2E; color: #fff; border-color: #1F2A2E; }
.vp-chip-n { font: 700 10px 'JetBrains Mono',monospace; background: rgba(0,0,0,.06); padding: 1px 5px; border-radius: 999px; }
.vp-chip.on .vp-chip-n { background: rgba(255,255,255,.18); }

.vp-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 13px; }
.vp-card { background: #fff; border: 1px solid #e7e9e3; border-radius: 16px; overflow: hidden; transition: border-color .12s; }
.vp-card:hover { border-color: #c7d0c0; }
.vp-card-top { display: flex; gap: 14px; padding: 16px; }
.vp-logo { width: 56px; height: 56px; border-radius: 13px; display: grid; place-items: center; font-size: 24px; flex-shrink: 0; }
.vp-row1 { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
.vp-name { font: 500 19px 'Cormorant Garamond',Georgia,serif; color: #1F2A2E; line-height: 1.15; }
.vp-meta { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
.vp-tag { font: 600 10.5px system-ui; background: rgba(74,90,76,.1); color: #3D4A4D; padding: 2px 8px; border-radius: 6px; }
.vp-rating { font: 600 11px 'JetBrains Mono',monospace; color: #D9A24A; }
.vp-status { display: inline-flex; align-items: center; gap: 5px; font: 600 10.5px system-ui; padding: 4px 9px; border-radius: 999px; white-space: nowrap; flex-shrink: 0; }
.vp-dot { width: 6px; height: 6px; border-radius: 50%; }
.vp-contact { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 11px; font: 12px system-ui; color: #3D4A4D; }
.vp-contact .mono { font-size: 11.5px; }

.vp-pay { padding: 0 16px 12px; }
.vp-pay-row { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px; }
.vp-pay-lbl { font: 600 10px system-ui; letter-spacing: .04em; text-transform: uppercase; color: #94a09a; }
.vp-pay-val { font-size: 11.5px; color: #3D4A4D; }
.vp-bar { height: 6px; background: #e7ece3; border-radius: 999px; overflow: hidden; }
.vp-bar-fill { height: 100%; border-radius: 999px; }

.vp-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 11px 16px; border-top: 1px solid #eef0ea; background: #FBFCF9; }
.vp-next { font: 11.5px system-ui; color: #3D4A4D; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.vp-next strong { font-weight: 600; color: #1F2A2E; }
.vp-actions { display: flex; gap: 5px; flex-shrink: 0; }
.vp-act { width: 30px; height: 30px; border-radius: 8px; border: none; background: #fff; box-shadow: inset 0 0 0 1px #e6e9e2; color: #3D4A4D; display: grid; place-items: center; cursor: pointer; text-decoration: none; }
.vp-act:hover { background: #f3f5f0; }

.vp-empty { text-align: center; padding: 48px 20px; background: #fff; border: 1px dashed #d6ddcd; border-radius: 16px; }
.vp-empty-ic { font-size: 34px; }
.vp-empty-t { font: 700 15px system-ui; color: #3f4a45; margin-top: 8px; }
.vp-empty-s { font: 12.5px system-ui; color: #94a09a; margin: 4px auto 16px; max-width: 320px; }

/* Rail */
.vp-rail { display: flex; flex-direction: column; gap: 14px; }
.vp-gap { border: 1px solid #E0D2BD; border-radius: 16px; background: linear-gradient(135deg, #F4EDDC, #E9DFC4); padding: 16px; }
.vp-gap-h h3 { font: 600 15px 'Cormorant Garamond',Georgia,serif; color: #1F2A2E; }
.vp-gap-h p { font: 11.5px system-ui; color: #8E6515; margin-top: 2px; }
.vp-gap-list { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
.vp-gap-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; text-align: left; background: rgba(255,255,255,.6); border: 1px solid rgba(0,0,0,.05); border-radius: 10px; padding: 10px 12px; cursor: pointer; }
.vp-gap-item:hover { background: rgba(255,255,255,.85); }
.vp-gap-name { font: 600 13px system-ui; color: #1F2A2E; }
.vp-gap-why { font: 11px system-ui; color: #8E6515; margin-top: 2px; line-height: 1.4; }
.vp-gap-add { font: 600 10.5px system-ui; color: #fff; background: #1F2A2E; padding: 4px 9px; border-radius: 7px; white-space: nowrap; flex-shrink: 0; }

.vp-rec { background: #fff; border: 1px solid #e7e9e3; border-radius: 16px; padding: 16px; }
.vp-rec-h { display: flex; align-items: center; justify-content: space-between; }
.vp-rec-h h3 { font: 600 15px 'Cormorant Garamond',Georgia,serif; color: #1F2A2E; }
.vp-soon { font: 600 9.5px system-ui; letter-spacing: .04em; text-transform: uppercase; color: #8a958d; background: #f1f3ee; padding: 3px 8px; border-radius: 999px; }
.vp-rec-p { font: 12px system-ui; color: #94a09a; margin-top: 6px; line-height: 1.5; }

/* Toast */
.vp-toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 80; background: #1F2A2E; color: #fff; font: 600 12.5px system-ui; padding: 10px 16px; border-radius: 10px; box-shadow: 0 10px 30px -8px rgba(0,0,0,.4); }
.vp-toast-enter-active, .vp-toast-leave-active { transition: opacity .2s, transform .2s; }
.vp-toast-enter-from, .vp-toast-leave-to { opacity: 0; transform: translate(-50%, 8px); }

/* Responsive */
@media (max-width: 1023px) {
  .vp-grid-wrap { grid-template-columns: 1fr; }
  .vp-rail { flex-direction: row; flex-wrap: wrap; }
  .vp-rail > * { flex: 1; min-width: 240px; }
}
@media (max-width: 639px) {
  .vp-stats { grid-template-columns: repeat(2, 1fr); }
  .vp-cards { grid-template-columns: 1fr; }
  .vp-rail { flex-direction: column; }
}
</style>
