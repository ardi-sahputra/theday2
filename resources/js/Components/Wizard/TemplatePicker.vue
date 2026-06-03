<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    invitationId:   { type: String,  required: true },
    currentTemplateId: { type: String, required: true },
    templates:      { type: Array,   default: () => [] },
    canUsePremium:  { type: Boolean, default: false },
    invitationStatus: { type: String, default: 'draft' },
});

const emit = defineEmits(['changed', 'close']);

// ── State ─────────────────────────────────────────────────────────
const search        = ref('');
const tierFilter    = ref('all');   // 'all' | 'free' | 'premium'
const pending       = ref(null);    // template being confirmed
const showUpgrade   = ref(false);
const loading       = ref(false);
const error         = ref('');

// ── Computed ──────────────────────────────────────────────────────
const filtered = computed(() => {
    return props.templates.filter(t => {
        const matchSearch = !search.value ||
            t.name.toLowerCase().includes(search.value.toLowerCase()) ||
            (t.category?.name ?? '').toLowerCase().includes(search.value.toLowerCase());
        const matchTier = tierFilter.value === 'all' || t.tier === tierFilter.value;
        return matchSearch && matchTier;
    });
});

const isPublished = computed(() => props.invitationStatus === 'published');

// ── Actions ───────────────────────────────────────────────────────
function selectTemplate(t) {
    if (t.id === props.currentTemplateId) return; // already active
    if (t.tier === 'premium' && !props.canUsePremium) {
        showUpgrade.value = true;
        return;
    }
    pending.value = t;
}

function cancelConfirm() {
    pending.value = null;
}

async function confirmChange() {
    if (!pending.value) return;
    loading.value = true;
    error.value   = '';
    try {
        const res = await axios.patch(
            `/dashboard/invitations/${props.invitationId}/template`,
            { template_id: pending.value.id }
        );
        emit('changed', res.data.template);
        pending.value = null;
        emit('close');
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal mengganti template. Coba lagi.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
  <Teleport to="body">
    <!-- Dim backdrop (klik untuk tutup); preview undangan tetap terlihat di belakang -->
    <div class="tpl-backdrop fixed inset-0 z-[80] bg-stone-900/25" @click="$emit('close')"></div>

    <!-- Right-side drawer beside the invitation preview -->
    <div class="tpl-drawer fixed right-0 top-0 bottom-0 z-[90] w-[380px] max-w-[88vw] flex flex-col bg-stone-50 shadow-2xl" @keydown.esc="$emit('close')">

        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 bg-white border-b border-stone-100 shadow-sm flex-shrink-0">
            <h2 class="text-base font-semibold text-stone-800">Pilih Template</h2>
            <button @click="$emit('close')" class="p-2 text-stone-400 hover:text-stone-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Filters -->
        <div class="flex flex-col gap-2 px-4 py-3 bg-white border-b border-stone-100 flex-shrink-0">
            <!-- Search -->
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari template..."
                    class="w-full pl-9 pr-4 py-2 text-sm border border-stone-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30 focus:border-[#92A89C]"
                />
            </div>
            <!-- Tier filter -->
            <div class="flex gap-1 self-start">
                <button v-for="opt in [{ val: 'all', label: 'Semua' }, { val: 'free', label: 'Gratis' }, { val: 'premium', label: 'Premium' }]"
                    :key="opt.val"
                    @click="tierFilter = opt.val"
                    :class="[
                        'px-3 py-2 text-xs font-medium rounded-lg transition-colors',
                        tierFilter === opt.val
                            ? 'bg-[#92A89C]/100 text-white'
                            : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
                    ]">
                    {{ opt.label }}
                </button>
            </div>
        </div>

        <!-- Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div v-if="filtered.length === 0" class="flex flex-col items-center justify-center h-48 text-stone-400">
                <svg class="w-12 h-12 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-.293.707L13 14.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-6.586L4.293 7.707A1 1 0 014 7V5z"/>
                </svg>
                <p class="text-sm">Tidak ada template ditemukan</p>
            </div>

            <div v-else class="grid grid-cols-2 gap-3">
                <div
                    v-for="t in filtered"
                    :key="t.id"
                    @click="selectTemplate(t)"
                    :class="[
                        'relative rounded-xl overflow-hidden border-2 transition-all cursor-pointer group',
                        t.id === currentTemplateId
                            ? 'border-[#92A89C] shadow-lg shadow-[#92A89C]/20'
                            : 'border-stone-200 hover:border-[#92A89C]/50 hover:shadow-md'
                    ]">

                    <!-- Thumbnail -->
                    <div class="aspect-[9/16] bg-stone-100 relative overflow-hidden">
                        <img
                            v-if="t.thumbnail_url"
                            :src="t.thumbnail_url"
                            :alt="t.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>

                        <!-- Active badge -->
                        <div v-if="t.id === currentTemplateId"
                            class="absolute top-2 left-2 px-2 py-0.5 bg-[#92A89C]/100 text-white text-xs font-semibold rounded-full">
                            Aktif
                        </div>

                        <!-- Premium badge -->
                        <div v-if="t.tier === 'premium'"
                            class="absolute top-2 right-2 px-2 py-0.5 bg-stone-800 text-[#B8C7BF] text-xs font-semibold rounded-full">
                            Premium
                        </div>

                        <!-- Lock overlay for premium on free plan -->
                        <div v-if="t.tier === 'premium' && !canUsePremium"
                            class="absolute inset-0 bg-stone-900/40 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-2">
                        <p class="text-xs font-semibold text-stone-800 truncate">{{ t.name }}</p>
                        <p v-if="t.category" class="text-xs text-stone-400 truncate">{{ t.category.name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Dialog -->
        <Teleport to="body">
            <div v-if="pending" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <h3 class="text-base font-semibold text-stone-800 mb-2">
                        Ganti ke template <span class="text-[#73877C]">{{ pending.name }}</span>?
                    </h3>
                    <p class="text-sm text-stone-500 mb-3">
                        Data undanganmu seperti nama, tanggal, dan foto akan tetap tersimpan. Tampilan akan berubah menggunakan template baru.
                    </p>
                    <p v-if="isPublished" class="text-sm text-[#73877C] bg-[#92A89C]/10 border border-[#B8C7BF] rounded-lg px-3 py-2 mb-4">
                        Undanganmu sudah dipublikasi. Mengganti template akan mengubah tampilan yang dilihat tamu segera setelah disimpan.
                    </p>
                    <p v-if="error" class="text-sm text-red-600 mb-3">{{ error }}</p>
                    <div class="flex gap-3">
                        <button
                            @click="cancelConfirm"
                            :disabled="loading"
                            class="flex-1 px-4 py-2 text-sm font-medium text-stone-700 bg-stone-100 rounded-xl hover:bg-stone-200 transition-colors disabled:opacity-50">
                            Batal
                        </button>
                        <button
                            @click="confirmChange"
                            :disabled="loading"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-[#92A89C]/100 rounded-xl hover:bg-[#73877C] transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            {{ loading ? 'Mengganti...' : 'Ya, Ganti Template' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upgrade prompt -->
            <div v-if="showUpgrade" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-[#92A89C]/20 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-[#73877C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-800 mb-1">Template Premium</h3>
                    <p class="text-sm text-stone-500 mb-5">Template ini tersedia untuk paket Premium.</p>
                    <div class="flex flex-col gap-2">
                        <a href="/dashboard/upgrade"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-[#92A89C]/100 rounded-xl hover:bg-[#73877C] transition-colors">
                            Upgrade Sekarang
                        </a>
                        <button
                            @click="showUpgrade = false; tierFilter = 'free'"
                            class="w-full px-4 py-2 text-sm font-medium text-stone-600 bg-stone-100 rounded-xl hover:bg-stone-200 transition-colors">
                            Lihat Template Gratis
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
  </Teleport>
</template>

<style scoped>
.tpl-backdrop { animation: tpl-fade .18s ease-out; }
.tpl-drawer   { animation: tpl-slide .22s cubic-bezier(.32,.72,0,1); }
@keyframes tpl-fade  { from { opacity: 0; } to { opacity: 1; } }
@keyframes tpl-slide { from { transform: translateX(100%); } to { transform: translateX(0); } }
</style>
