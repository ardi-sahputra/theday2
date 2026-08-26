<script setup>
import { ref } from 'vue';

const props = defineProps({
  galleries: { type: Array,  default: () => [] },     // [{ id, image_url, caption, sort_order }]
  caps:      { type: Object, default: () => ({}) },
  onAdd:     { type: Function, default: null },        // (file) => Promise
  onDelete:  { type: Function, default: null },        // (gallery) => Promise
  bare:      { type: Boolean, default: false },        // embed mode: skip the section-block header
});

const fileInput = ref(null);
const pending   = ref(0);   // how many uploads are in flight → that many spinner tiles
const deleting  = ref({});  // { [id]: true }

function pick() { fileInput.value?.click(); }

async function onFiles(e) {
  const files = Array.from(e.target.files || []);
  e.target.value = '';
  if (!files.length || !props.onAdd) return;
  for (const f of files) {
    if (!f.type.startsWith('image/')) continue;
    pending.value++;
    props.onAdd(f).catch(() => {}).finally(() => { pending.value--; });
  }
}

async function remove(g) {
  if (!props.onDelete || deleting.value[g.id]) return;
  deleting.value = { ...deleting.value, [g.id]: true };
  try { await props.onDelete(g); }
  finally { const d = { ...deleting.value }; delete d[g.id]; deleting.value = d; }
}
</script>

<template>
  <div>
    <div :class="bare ? '' : 'section-block'">
      <template v-if="!bare">
        <h4>Galeri Foto</h4>
        <div class="desc">Foto pre-wedding atau momen kalian. Maks 5MB/foto.</div>
      </template>

      <div class="gal-grid">
        <!-- existing photos -->
        <div v-for="g in galleries" :key="g.id" class="gal-cell">
          <img :src="g.image_url" alt="" class="gal-img" loading="lazy" />
          <button type="button" class="gal-del" :disabled="deleting[g.id]" @click="remove(g)" title="Hapus">
            <span v-if="deleting[g.id]" class="ev-spin gal-spin"></span>
            <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- in-flight uploads -->
        <div v-for="n in pending" :key="'p' + n" class="gal-cell gal-busy">
          <span class="ev-spin"></span>
        </div>

        <!-- add tile -->
        <button type="button" class="gal-cell gal-add" @click="pick">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CAB8E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
          <span>Tambah foto</span>
        </button>
      </div>

      <p v-if="!galleries.length && !pending" class="help" style="margin-top:10px;">Belum ada foto. Tap "Tambah foto" untuk mulai.</p>
      <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="onFiles" />
    </div>
  </div>
</template>

<style scoped>
.gal-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.gal-cell { position:relative; aspect-ratio:1; border-radius:12px; overflow:hidden; }
.gal-img { width:100%; height:100%; object-fit:cover; display:block; }
.gal-del {
  position:absolute; top:5px; right:5px; width:24px; height:24px; border-radius:50%;
  background:rgba(31,42,46,0.7); color:#fff; border:none; cursor:pointer;
  display:grid; place-items:center; backdrop-filter:blur(2px);
}
.gal-del:hover { background:rgba(193,144,137,0.95); }
.gal-spin { width:13px; height:13px; border-width:2px; }
.gal-busy, .gal-add {
  border:1.5px dashed var(--d-line-2,#C7D0BE); background:var(--d-surface,#F6F8F3);
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px;
  font-size:11px; color:var(--d-muted,#6C7A75); cursor:pointer; font-family:inherit;
}
.gal-add:hover { border-color:var(--d-sage,#92A89C); }
</style>
