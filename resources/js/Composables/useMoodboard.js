// resources/js/Composables/useMoodboard.js
//
// State + axios methods for the Moodboard page. Mirrors the useEditorV2 gallery
// idiom (compress → extract colors → upload → optimistic state). One board per
// couple; items are pins on a masonry board.
//
// Contract (backend agent owns these routes):
//   PATCH  /dashboard/moodboard                 { name?, concept_note?, palette? } -> { moodboard }
//   POST   /dashboard/moodboard/items           multipart { image, tag?, caption?, colors[]? } -> { item }
//   PATCH  /dashboard/moodboard/items/{id}       { caption?, tag? } -> { item }
//   DELETE /dashboard/moodboard/items/{id}
//   PUT    /dashboard/moodboard/items/reorder    { ids:[...] } -> { ok:true }

import { ref } from 'vue';
import axios from 'axios';
import { compressImage } from '@/utils/imageCompress';
import { extractColors } from '@/utils/imageColors';

const MAX_PALETTE = 6;
const HEX_RE = /^#[0-9a-fA-F]{6}$/;

export function useMoodboard(props) {
    const moodboard = ref({
        id: props.moodboard?.id ?? null,
        name: props.moodboard?.name ?? '',
        concept_note: props.moodboard?.concept_note ?? '',
        palette: Array.isArray(props.moodboard?.palette) ? [...props.moodboard.palette] : [],
    });

    const items = ref(Array.isArray(props.items) ? [...props.items] : []);
    const stats = ref({
        count: props.stats?.count ?? (props.items?.length ?? 0),
        categories: props.stats?.categories ?? 0,
        dibuatBerdua: props.stats?.dibuatBerdua ?? false,
    });

    const saving = ref(false);
    const error = ref(null);

    // Optimistic upload placeholders (not yet persisted). Keyed by temp id.
    const pending = ref([]); // [{ tempId, preview }]

    function recountStats() {
        stats.value.count = items.value.length;
        stats.value.categories = new Set(
            items.value.map(i => i.tag).filter(Boolean)
        ).size;
    }

    // ── Items ────────────────────────────────────────────────────────────────
    async function addItem(file, { tag = null, caption = null } = {}) {
        if (!file) return null;
        const tempId = `tmp_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`;
        const preview = URL.createObjectURL(file);
        pending.value = [...pending.value, { tempId, preview }];
        error.value = null;

        try {
            const [compressed, colors] = await Promise.all([
                compressImage(file, { maxEdge: 1600, quality: 0.82 }),
                extractColors(file, 4),
            ]);

            const fd = new FormData();
            fd.append('image', compressed);
            if (tag) fd.append('tag', tag);
            if (caption) fd.append('caption', caption);
            (colors || []).forEach(c => fd.append('colors[]', c));

            const { data } = await axios.post('/dashboard/moodboard/items', fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            items.value = [...items.value, data.item];
            recountStats();
            return data.item;
        } catch (e) {
            error.value = e;
            throw e;
        } finally {
            pending.value = pending.value.filter(p => p.tempId !== tempId);
            URL.revokeObjectURL(preview);
        }
    }

    async function updateItem(id, patch) {
        const { data } = await axios.patch(`/dashboard/moodboard/items/${id}`, patch);
        items.value = items.value.map(i => (i.id === id ? { ...i, ...data.item } : i));
        recountStats();
        return data.item;
    }

    async function deleteItem(id) {
        const prev = items.value;
        items.value = items.value.filter(i => i.id !== id); // optimistic
        recountStats();
        try {
            await axios.delete(`/dashboard/moodboard/items/${id}`);
        } catch (e) {
            items.value = prev; // rollback
            recountStats();
            throw e;
        }
    }

    async function reorderItems(ids) {
        // Optimistic local reorder by submitted id order.
        const byId = new Map(items.value.map(i => [i.id, i]));
        const next = ids.map(id => byId.get(id)).filter(Boolean);
        // keep any items not in ids (defensive) at the end
        items.value.forEach(i => { if (!ids.includes(i.id)) next.push(i); });
        items.value = next;
        return axios.put('/dashboard/moodboard/items/reorder', { ids });
    }

    // ── Board (name / concept_note / palette), debounced PATCH ───────────────
    let saveTimer = null;
    function saveBoard(patch = {}, { debounce = 600 } = {}) {
        // Merge locally right away so the UI is responsive.
        Object.assign(moodboard.value, patch);

        return new Promise((resolve, reject) => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(async () => {
                saving.value = true;
                error.value = null;
                try {
                    const body = {};
                    if ('name' in patch) body.name = moodboard.value.name;
                    if ('concept_note' in patch) body.concept_note = moodboard.value.concept_note;
                    if ('palette' in patch) body.palette = moodboard.value.palette;
                    const { data } = await axios.patch('/dashboard/moodboard', body);
                    if (data?.moodboard) {
                        // sync server-normalised values without clobbering newer edits
                        moodboard.value.name = data.moodboard.name ?? moodboard.value.name;
                        moodboard.value.concept_note = data.moodboard.concept_note ?? moodboard.value.concept_note;
                        if (Array.isArray(data.moodboard.palette)) {
                            moodboard.value.palette = data.moodboard.palette;
                        }
                    }
                    resolve(data);
                } catch (e) {
                    error.value = e;
                    reject(e);
                } finally {
                    saving.value = false;
                }
            }, debounce);
        });
    }

    // ── Palette helpers ──────────────────────────────────────────────────────
    function normalizeHex(hex) {
        if (!hex) return null;
        let h = String(hex).trim();
        if (!h.startsWith('#')) h = `#${h}`;
        h = h.toLowerCase();
        return HEX_RE.test(h) ? h : null;
    }

    function addColor(hex, label = null) {
        const h = normalizeHex(hex);
        if (!h) return false;
        if (moodboard.value.palette.length >= MAX_PALETTE) return false;
        if (moodboard.value.palette.some(s => (s.hex || '').toLowerCase() === h)) return false;
        const next = [...moodboard.value.palette, { hex: h, label: label || '' }];
        saveBoard({ palette: next }, { debounce: 0 });
        return true;
    }

    function removeColor(hex) {
        const h = (hex || '').toLowerCase();
        const next = moodboard.value.palette.filter(s => (s.hex || '').toLowerCase() !== h);
        saveBoard({ palette: next }, { debounce: 0 });
    }

    async function copyColor(hex) {
        try {
            await navigator.clipboard.writeText(hex);
            return true;
        } catch {
            return false;
        }
    }

    return {
        // state
        moodboard, items, stats, pending, saving, error,
        // items
        addItem, updateItem, deleteItem, reorderItems,
        // board
        saveBoard,
        // palette
        addColor, removeColor, copyColor, normalizeHex,
        // constants
        MAX_PALETTE,
    };
}
