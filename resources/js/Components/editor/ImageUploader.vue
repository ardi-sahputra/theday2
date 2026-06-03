<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { compressImage } from '@/utils/imageCompress';

const props = defineProps({
    modelValue:    { type: String,  default: '' },       // current URL
    invitationId:  { type: String,  required: true },
    uploadType:    { type: String,  default: 'photo' },  // photo | gallery | cover
    label:         { type: String,  default: 'Foto' },
    aspectClass:   { type: String,  default: 'aspect-video' }, // aspect-video | aspect-square
    hint:          { type: String,  default: '' },
});

const emit = defineEmits(['update:modelValue']);

const uploading  = ref(false);
const uploadError = ref('');
const fileInput  = ref(null);

async function onFileChange(e) {
    const original = e.target.files?.[0];
    if (!original) return;

    uploadError.value = '';
    uploading.value   = true;

    // Resize + WebP-compress in the browser before upload (big perf win).
    const file = await compressImage(original);

    // Safety: server caps at 5 MB. After compression this is rarely hit.
    if (file.size > 5 * 1024 * 1024) {
        uploadError.value = 'Ukuran file maksimal 5 MB.';
        uploading.value = false;
        if (fileInput.value) fileInput.value.value = '';
        return;
    }

    const form = new FormData();
    form.append('file', file);
    form.append('type', props.uploadType);

    try {
        const { data } = await axios.post(
            route('dashboard.invitations.upload', props.invitationId),
            form,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        emit('update:modelValue', data.url);
    } catch (err) {
        uploadError.value = err.response?.data?.message ?? 'Upload gagal, coba lagi.';
    } finally {
        uploading.value = false;
        // reset so same file can be re-selected
        if (fileInput.value) fileInput.value.value = '';
    }
}

function removeImage() {
    emit('update:modelValue', '');
}

function triggerPick() {
    fileInput.value?.click();
}
</script>

<template>
    <div>
        <label class="block text-xs font-medium text-stone-600 mb-1.5">{{ label }}</label>

        <!-- Preview -->
        <div
            v-if="modelValue"
            :class="['relative rounded-xl overflow-hidden bg-stone-100 mb-2', aspectClass]"
        >
            <img :src="modelValue" class="w-full h-full object-cover" alt="preview"/>

            <!-- Overlay actions -->
            <div class="absolute inset-0 bg-black/0 hover:bg-black/30 transition-colors flex items-center justify-center gap-2 opacity-0 hover:opacity-100">
                <button
                    @click="triggerPick"
                    class="px-3 py-1.5 rounded-lg bg-white text-xs font-medium text-stone-700 shadow hover:bg-stone-50 transition-colors"
                >
                    Ganti
                </button>
                <button
                    @click="removeImage"
                    class="px-3 py-1.5 rounded-lg bg-white text-xs font-medium text-red-500 shadow hover:bg-red-50 transition-colors"
                >
                    Hapus
                </button>
            </div>
        </div>

        <!-- Drop zone (no image yet) -->
        <button
            v-else
            type="button"
            @click="triggerPick"
            :disabled="uploading"
            :class="[
                'w-full border-2 border-dashed rounded-xl transition-colors flex flex-col items-center justify-center gap-2 py-6',
                uploading
                    ? 'border-amber-300 bg-amber-50 cursor-wait'
                    : 'border-stone-200 hover:border-amber-300 hover:bg-amber-50/40 cursor-pointer',
            ]"
        >
            <!-- Spinner -->
            <svg v-if="uploading" class="w-6 h-6 text-amber-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <!-- Upload icon -->
            <svg v-else class="w-6 h-6 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span class="text-xs text-stone-400">
                {{ uploading ? 'Mengunggah...' : 'Klik untuk unggah foto' }}
            </span>
            <span v-if="hint && !uploading" class="text-[10px] text-stone-300">{{ hint }}</span>
        </button>

        <!-- Small "change" button when image exists but not hovering on mobile -->
        <button
            v-if="modelValue"
            type="button"
            @click="triggerPick"
            :disabled="uploading"
            class="mt-1.5 text-xs text-amber-600 hover:text-amber-800 font-medium transition-colors flex items-center gap-1"
        >
            <svg v-if="uploading" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            {{ uploading ? 'Mengunggah...' : 'Ganti foto' }}
        </button>

        <!-- Error -->
        <p v-if="uploadError" class="mt-1.5 text-xs text-red-500">{{ uploadError }}</p>

        <!-- Hidden file input -->
        <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/png,image/webp,image/gif"
            class="sr-only"
            @change="onFileChange"
        />
    </div>
</template>
