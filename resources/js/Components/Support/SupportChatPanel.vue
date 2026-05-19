<script setup>
import { ref, nextTick, watch, computed } from 'vue';
import SupportMessage from './SupportMessage.vue';
import SupportImageUpload from './SupportImageUpload.vue';
import SupportStatusBadge from './SupportStatusBadge.vue';

const props = defineProps({
    messages:     { type: Array,   default: () => [] },
    adminStatus:  { type: Object,  default: () => ({ online: false, work_hours_open: false }) },
    isSending:    { type: Boolean, default: false },
    sendError:    { type: String,  default: '' },
    showHeader:   { type: Boolean, default: true },
});

const emit = defineEmits(['send', 'preview-image', 'close']);

const body = ref('');
const imageFile = ref(null);
const imageUpload = ref(null);
const scrollEl = ref(null);

function onImageChange(file) { imageFile.value = file; }

function canSend() {
    return (body.value.trim().length > 0 || imageFile.value) && !props.isSending;
}

function send() {
    if (!canSend()) return;
    emit('send', body.value.trim(), imageFile.value);
    body.value = '';
    imageFile.value = null;
    imageUpload.value?.clear();
}

function onEnter(e) {
    if (e.shiftKey) return;
    e.preventDefault();
    send();
}

function scrollToBottom() {
    nextTick(() => {
        if (scrollEl.value) scrollEl.value.scrollTop = scrollEl.value.scrollHeight;
    });
}

watch(() => props.messages.length, scrollToBottom, { immediate: true });

const offHoursNotice = computed(() => !props.adminStatus.work_hours_open);
</script>

<template>
    <div class="flex flex-col h-full bg-white">
        <!-- Header -->
        <div v-if="showHeader" class="flex items-center justify-between px-4 py-3 border-b border-stone-100">
            <div>
                <p class="text-sm font-semibold text-stone-800">Support TheDay</p>
                <SupportStatusBadge :status="adminStatus" class="mt-0.5" />
            </div>
            <button type="button" @click="emit('close')" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Off-hours banner -->
        <div v-if="offHoursNotice" class="px-3 py-2 bg-amber-50 border-b border-amber-100 text-xs text-amber-700">
            ⏰ Di luar jam kerja — kami akan balas saat jam kerja berikutnya.
        </div>

        <!-- Messages -->
        <div ref="scrollEl" class="flex-1 overflow-y-auto px-3 py-4">
            <SupportMessage
                v-for="m in messages"
                :key="m.id"
                :message="m"
                :side="m.sender_role === 'user' ? 'right' : 'left'"
                @preview-image="(u) => emit('preview-image', u)"
            />

            <div v-if="!messages.length" class="text-center text-xs text-stone-400 mt-8">
                Belum ada pesan. Mulai chat dengan tim Support TheDay.
            </div>
        </div>

        <!-- Composer -->
        <div class="border-t border-stone-100 p-3 space-y-2">
            <SupportImageUpload ref="imageUpload" @change="onImageChange" />

            <div class="flex items-end gap-2">
                <textarea
                    v-model="body"
                    @keydown.enter="onEnter"
                    placeholder="Tulis pesan..."
                    rows="1"
                    class="flex-1 resize-none rounded-lg border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/30 max-h-32"
                />
                <button
                    type="button"
                    :disabled="!canSend()"
                    @click="send"
                    :class="['px-3 py-2 rounded-lg text-sm font-semibold text-white transition-colors', canSend() ? 'bg-brand-primary hover:bg-brand-primary-hover' : 'bg-stone-300 cursor-not-allowed']"
                >
                    Kirim
                </button>
            </div>

            <p v-if="sendError" class="text-xs text-red-500">{{ sendError }}</p>
        </div>
    </div>
</template>
