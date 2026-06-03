<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  open:       { type: Boolean, default: false },
  vendor:     { type: Object,  default: null },   // null = create
  categories: { type: Array,   default: () => [] },
  saving:     { type: Boolean, default: false },
});
const emit = defineEmits(['close', 'submit']);

const blank = () => ({
  name: '', category: '', pic_name: '', phone: '',
  total_cost: '', paid_amount: '', booked_at: '', rating: '',
  next_action: '', notes: '',
});

const form = ref(blank());
const contractFile = ref(null);
const removeContract = ref(false);
const err = ref('');

const isEdit = computed(() => !!props.vendor);
const existingContract = computed(() => props.vendor?.contract_url || null);

watch(() => props.open, (o) => {
  if (!o) return;
  err.value = ''; contractFile.value = null; removeContract.value = false;
  if (props.vendor) {
    const v = props.vendor;
    form.value = {
      name: v.name ?? '', category: v.category ?? '', pic_name: v.pic_name ?? '', phone: v.phone ?? '',
      total_cost: v.total_cost ?? '', paid_amount: v.paid_amount ?? '', booked_at: v.booked_at ?? '',
      rating: v.rating ?? '', next_action: v.next_action ?? '', notes: v.notes ?? '',
    };
  } else {
    form.value = blank();
  }
}, { immediate: true });

function onFile(e) { contractFile.value = e.target.files?.[0] ?? null; removeContract.value = false; }

function submit() {
  if (!form.value.name.trim()) { err.value = 'Nama vendor wajib diisi.'; return; }
  if (!form.value.category)    { err.value = 'Pilih kategori.'; return; }
  emit('submit', {
    ...form.value,
    name: form.value.name.trim(),
    _contract: contractFile.value,
    _removeContract: removeContract.value,
  });
}
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="vm-bg" @click.self="emit('close')">
      <div class="vm">
        <div class="vm-head">
          <h3>{{ isEdit ? 'Edit Vendor' : 'Tambah Vendor' }}</h3>
          <button type="button" class="vm-x" @click="emit('close')">×</button>
        </div>

        <div class="vm-body">
          <div class="vm-row">
            <div class="vm-field" style="flex:2;">
              <label>Nama vendor *</label>
              <input v-model="form.name" maxlength="120" placeholder="mis. The Manor BDG" />
            </div>
            <div class="vm-field" style="flex:1;">
              <label>Kategori *</label>
              <select v-model="form.category">
                <option value="" disabled>Pilih…</option>
                <option v-for="c in categories" :key="c.key" :value="c.key">{{ c.label }}</option>
              </select>
            </div>
          </div>

          <div class="vm-row">
            <div class="vm-field"><label>Narahubung (PIC)</label><input v-model="form.pic_name" maxlength="80" placeholder="mis. Mas Andi" /></div>
            <div class="vm-field"><label>No. WhatsApp</label><input v-model="form.phone" maxlength="30" placeholder="0812xxxxxxx" /></div>
          </div>

          <div class="vm-row">
            <div class="vm-field"><label>Total biaya (Rp)</label><input v-model="form.total_cost" type="number" min="0" placeholder="130000000" /></div>
            <div class="vm-field"><label>Sudah dibayar (Rp)</label><input v-model="form.paid_amount" type="number" min="0" placeholder="91000000" /></div>
          </div>

          <div class="vm-row">
            <div class="vm-field"><label>Tanggal booking</label><input v-model="form.booked_at" type="date" /></div>
            <div class="vm-field"><label>Rating (0–5)</label><input v-model="form.rating" type="number" min="0" max="5" step="0.1" placeholder="4.9" /></div>
          </div>

          <div class="vm-field"><label>Langkah berikutnya</label><input v-model="form.next_action" maxlength="120" placeholder="mis. Pelunasan H-14" /></div>

          <div class="vm-field"><label>Catatan</label><textarea v-model="form.notes" rows="2" maxlength="1000" placeholder="catatan kontrak / kesepakatan…"></textarea></div>

          <div class="vm-field">
            <label>Kontrak (PDF/gambar, maks 8MB)</label>
            <div v-if="existingContract && !contractFile && !removeContract" class="vm-contract">
              <a :href="existingContract" target="_blank">📄 Lihat kontrak tersimpan</a>
              <button type="button" class="vm-link-del" @click="removeContract = true">hapus</button>
            </div>
            <input v-else type="file" accept=".pdf,image/*" @change="onFile" />
            <div v-if="removeContract" class="vm-hint">Kontrak akan dihapus saat disimpan. <button type="button" class="vm-link" @click="removeContract = false">batal</button></div>
          </div>

          <p v-if="err" class="vm-err">{{ err }}</p>
        </div>

        <div class="vm-foot">
          <button type="button" class="vm-ghost" @click="emit('close')">Batal</button>
          <button type="button" class="vm-dark" :disabled="saving" @click="submit">{{ saving ? 'Menyimpan…' : 'Simpan' }}</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.vm-bg { position: fixed; inset: 0; z-index: 90; background: rgba(0,0,0,.45); display: grid; place-items: center; padding: 16px; }
.vm { width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 18px; }
.vm-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 12px; }
.vm-head h3 { font: 600 17px 'Cormorant Garamond',Georgia,serif; color: #1F2A2E; }
.vm-x { width: 30px; height: 30px; border: none; background: #f1f1ee; border-radius: 8px; font-size: 18px; color: #555; cursor: pointer; }
.vm-body { padding: 0 20px; }
.vm-row { display: flex; gap: 10px; }
.vm-row .vm-field { flex: 1; }
.vm-field { margin-bottom: 12px; }
.vm-field label { display: block; font: 600 11px system-ui; color: #6b7280; margin-bottom: 4px; }
.vm-field input, .vm-field select, .vm-field textarea { width: 100%; border: 1px solid #e2e5df; border-radius: 9px; padding: 9px 11px; font: 13px system-ui; outline: none; background: #fff; }
.vm-field input:focus, .vm-field select:focus, .vm-field textarea:focus { border-color: #92A89C; }
.vm-field textarea { resize: vertical; }
.vm-contract { display: flex; align-items: center; gap: 10px; font: 12.5px system-ui; }
.vm-contract a { color: #6F8270; font-weight: 600; text-decoration: none; }
.vm-link, .vm-link-del { border: none; background: none; color: #c0625a; font: 600 12px system-ui; cursor: pointer; padding: 0; }
.vm-link { color: #6F8270; }
.vm-hint { font: 11.5px system-ui; color: #9aa6a0; margin-top: 5px; }
.vm-err { font: 12px system-ui; color: #c0625a; margin: 4px 0 0; }
.vm-foot { display: flex; gap: 8px; padding: 14px 20px 18px; }
.vm-ghost { flex: 1; font: 600 12.5px system-ui; color: #555; background: #f1f1ee; border: none; padding: 11px; border-radius: 11px; cursor: pointer; }
.vm-dark { flex: 1; font: 600 12.5px system-ui; color: #fff; background: #1F2A2E; border: none; padding: 11px; border-radius: 11px; cursor: pointer; }
.vm-dark:disabled { opacity: .6; }
</style>
