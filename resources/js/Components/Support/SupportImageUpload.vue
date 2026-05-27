<script setup>
import { ref } from 'vue';

const emit = defineEmits(['change']);
const input = ref(null);
const preview = ref(null);
const error = ref('');

const MAX_BYTES = 5 * 1024 * 1024;
const MIMES = ['image/jpeg', 'image/png', 'image/webp'];

function trigger() { input.value?.click(); }

function onFile(e) {
    const file = e.target.files?.[0];
    error.value = '';
    if (!file) return;
    if (!MIMES.includes(file.type)) {
        error.value = 'Format harus JPG, PNG, atau WebP';
        emit('change', null);
        return;
    }
    if (file.size > MAX_BYTES) {
        error.value = 'Ukuran maksimal 5MB';
        emit('change', null);
        return;
    }
    preview.value = URL.createObjectURL(file);
    emit('change', file);
}

function clear() {
    if (input.value) input.value.value = '';
    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = null;
    error.value = '';
    emit('change', null);
}

defineExpose({ clear });
</script>

<template>
    <div>
        <input ref="input" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onFile" />

        <button type="button" @click="trigger" class="p-2 text-stone-500 hover:text-stone-700 rounded-lg hover:bg-stone-100" aria-label="Upload gambar">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
        </button>

        <div v-if="preview" class="mt-2 relative inline-block">
            <img :src="preview" alt="Preview" class="max-h-24 rounded-lg" />
            <button type="button" @click="clear" class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-stone-700 text-white text-xs flex items-center justify-center">×</button>
        </div>
        <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
    </div>
</template>
