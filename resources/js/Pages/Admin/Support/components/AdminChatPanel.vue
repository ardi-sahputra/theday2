<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import axios from 'axios';
import SupportMessage from '@/Components/Support/SupportMessage.vue';
import SupportImageUpload from '@/Components/Support/SupportImageUpload.vue';

const props = defineProps({
    conversation:    { type: Object, required: true },
    initialMessages: { type: Array,  default: () => [] },
});

const emit = defineEmits(['resolved']);

const messages   = ref([...props.initialMessages]);
const lastMsgId  = ref(messages.value.at(-1)?.id ?? 0);
const body       = ref('');
const imageFile  = ref(null);
const isSending  = ref(false);
const sendError  = ref('');
const scrollEl   = ref(null);
const imageUpload = ref(null);
let pollTimer = null;

function scrollToBottom() {
    nextTick(() => {
        if (scrollEl.value) scrollEl.value.scrollTop = scrollEl.value.scrollHeight;
    });
}

async function fetchNewMessages() {
    try {
        const { data } = await axios.get(`/admin/support/${props.conversation.id}/messages`, {
            params: { since: lastMsgId.value },
        });
        if (Array.isArray(data.messages) && data.messages.length) {
            messages.value.push(...data.messages);
            lastMsgId.value = data.messages.at(-1).id;
            scrollToBottom();
        }
    } catch (_) {}
}

async function send() {
    if ((!body.value.trim() && !imageFile.value) || isSending.value) return;
    isSending.value = true; sendError.value = '';
    try {
        const form = new FormData();
        if (body.value.trim()) form.append('body', body.value.trim());
        if (imageFile.value) form.append('image', imageFile.value);

        const { data } = await axios.post(`/admin/support/${props.conversation.id}/messages`, form, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        messages.value.push(data.message);
        lastMsgId.value = data.message.id;
        body.value = '';
        imageFile.value = null;
        imageUpload.value?.clear();
        scrollToBottom();
    } catch (e) {
        sendError.value = e.response?.data?.message ?? 'Gagal mengirim';
    } finally {
        isSending.value = false;
    }
}

async function markRead() {
    try { await axios.post(`/admin/support/${props.conversation.id}/mark-read`); } catch (_) {}
}

async function resolve() {
    if (!confirm('Tandai conversation ini sebagai resolved?')) return;
    await axios.post(`/admin/support/${props.conversation.id}/resolve`);
    emit('resolved');
}

function onImageChange(f) { imageFile.value = f; }

onMounted(() => {
    markRead();
    scrollToBottom();
    pollTimer = setInterval(fetchNewMessages, 15000);
});

onBeforeUnmount(() => { clearInterval(pollTimer); });

watch(() => props.conversation.id, () => {
    messages.value = [...props.initialMessages];
    lastMsgId.value = messages.value.at(-1)?.id ?? 0;
    markRead();
    scrollToBottom();
});
</script>

<template>
    <div class="flex flex-col h-full bg-white">
        <div class="flex items-center justify-between px-4 py-3 border-b border-stone-200">
            <div>
                <p class="text-sm font-semibold text-stone-800">{{ conversation.user.name }}</p>
                <p class="text-xs text-stone-500">{{ conversation.user.email }}</p>
            </div>
            <button
                type="button"
                @click="resolve"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-stone-200 text-stone-700 hover:bg-stone-50"
            >
                ✓ Mark Resolved
            </button>
        </div>

        <div ref="scrollEl" class="flex-1 overflow-y-auto px-3 py-4">
            <SupportMessage
                v-for="m in messages"
                :key="m.id"
                :message="m"
                :side="m.sender_role === 'admin' ? 'right' : 'left'"
            />
        </div>

        <div class="border-t border-stone-200 p-3 space-y-2">
            <SupportImageUpload ref="imageUpload" @change="onImageChange" />
            <div class="flex items-end gap-2">
                <textarea
                    v-model="body"
                    @keydown.enter.exact.prevent="send"
                    placeholder="Tulis balasan..."
                    rows="1"
                    class="flex-1 resize-none rounded-lg border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/30 max-h-32"
                />
                <button
                    type="button"
                    :disabled="(!body.trim() && !imageFile) || isSending"
                    @click="send"
                    class="px-3 py-2 rounded-lg text-sm font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover disabled:bg-stone-300 disabled:cursor-not-allowed"
                >
                    Kirim
                </button>
            </div>
            <p v-if="sendError" class="text-xs text-red-500">{{ sendError }}</p>
        </div>
    </div>
</template>
