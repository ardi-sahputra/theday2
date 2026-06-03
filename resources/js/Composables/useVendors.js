// resources/js/Composables/useVendors.js
//
// State + axios for the Vendor Tracker page. A list of vendors per couple.
// Create/update always go as multipart (optional contract file); update uses
// POST + _method=PATCH so PHP populates $_FILES (it doesn't for native PATCH).
//
// Contract (backend):
//   POST   /dashboard/vendor              multipart -> { vendor }
//   PATCH  /dashboard/vendor/{id}          multipart (_method) -> { vendor }
//   DELETE /dashboard/vendor/{id}

import { ref, computed } from 'vue';
import axios from 'axios';

const FIELDS = ['name', 'category', 'pic_name', 'phone', 'total_cost', 'paid_amount', 'next_action', 'booked_at', 'rating', 'notes'];

export function useVendors(props) {
  const vendors       = ref(Array.isArray(props.vendors) ? [...props.vendors] : []);
  const categories    = ref(Array.isArray(props.categories) ? [...props.categories] : []);
  const initialGaps   = Array.isArray(props.gapCategories) ? [...props.gapCategories] : [];

  const saving = ref(false);
  const error  = ref(null);

  // ── Derived stats (recomputed locally after every mutation) ────────────────
  const stats = computed(() => {
    const v = vendors.value;
    return {
      total:           v.length,
      lunas:           v.filter(x => x.status_key === 'lunas').length,
      dp:              v.filter(x => x.status_key === 'dp').length,
      total_committed: v.reduce((a, x) => a + (Number(x.total_cost)  || 0), 0),
      total_paid:      v.reduce((a, x) => a + (Number(x.paid_amount) || 0), 0),
    };
  });

  // Present categories → drives filter chips + gap analysis.
  const presentCats = computed(() => new Set(vendors.value.map(x => x.category)));

  // Gaps = important categories that started empty and are still empty.
  // (Shrinks as you fill them; a page refresh re-derives the full truth.)
  const gapCategories = computed(() => initialGaps.filter(g => !presentCats.value.has(g.key)));

  // Categories that actually have vendors (for the filter row), with counts.
  const usedCategories = computed(() =>
    categories.value
      .filter(c => presentCats.value.has(c.key))
      .map(c => ({ ...c, count: vendors.value.filter(v => v.category === c.key).length }))
  );

  // ── Mutations ──────────────────────────────────────────────────────────────
  function buildForm(payload) {
    const fd = new FormData();
    for (const f of FIELDS) {
      const val = payload[f];
      if (val !== null && val !== undefined && val !== '') fd.append(f, val);
    }
    if (payload._contract instanceof File) fd.append('contract', payload._contract);
    if (payload._removeContract) fd.append('remove_contract', '1');
    return fd;
  }

  async function addVendor(payload) {
    saving.value = true; error.value = null;
    try {
      const { data } = await axios.post('/dashboard/vendor', buildForm(payload), {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      vendors.value = [data.vendor, ...vendors.value];
      return data.vendor;
    } catch (e) { error.value = e; throw e; }
    finally { saving.value = false; }
  }

  async function updateVendor(id, payload) {
    saving.value = true; error.value = null;
    try {
      const fd = buildForm(payload);
      fd.append('_method', 'PATCH'); // method spoofing so $_FILES is populated
      const { data } = await axios.post(`/dashboard/vendor/${id}`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      vendors.value = vendors.value.map(v => (v.id === id ? data.vendor : v));
      return data.vendor;
    } catch (e) { error.value = e; throw e; }
    finally { saving.value = false; }
  }

  async function deleteVendor(id) {
    const prev = vendors.value;
    vendors.value = vendors.value.filter(v => v.id !== id); // optimistic
    try {
      await axios.delete(`/dashboard/vendor/${id}`);
    } catch (e) {
      vendors.value = prev; // rollback
      throw e;
    }
  }

  return {
    vendors, categories, stats, gapCategories, usedCategories,
    saving, error,
    addVendor, updateVendor, deleteVendor,
  };
}
