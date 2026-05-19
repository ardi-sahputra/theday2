<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useSupportChat } from '@/Composables/useSupportChat';
import SupportChatPanel from '@/Components/Support/SupportChatPanel.vue';

const props = defineProps({
    conversation:  { type: Object, required: true },
    messages:      { type: Array,  required: true },
    admin_status:  { type: Object, required: true },
});

const chat = useSupportChat();
const lightboxUrl = ref(null);

// Seed composable BEFORE polling kicks in (avoids since=0 refetch + duplicate messages).
// seedMessages() sets both messages.value AND the internal lastMsgId tracker.
chat.seedMessages(props.messages);
chat.adminStatus.value = props.admin_status;
chat.isOpen.value = true;

function onSend(body, file) { chat.sendMessage(body, file); }
function previewImage(url) { lightboxUrl.value = url; }
function closeLightbox() { lightboxUrl.value = null; }
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h1 class="text-base font-semibold text-stone-800">Support</h1>
        </template>

        <div class="h-[calc(100vh-4rem)] bg-white">
            <SupportChatPanel
                :messages="chat.messages.value"
                :admin-status="chat.adminStatus.value"
                :is-sending="chat.isSending.value"
                :send-error="chat.sendError.value"
                :show-header="false"
                @send="onSend"
                @preview-image="previewImage"
            />
        </div>

        <Teleport to="body">
            <div
                v-if="lightboxUrl"
                @click="closeLightbox"
                class="fixed inset-0 z-[100] bg-black/80 flex items-center justify-center p-6"
            >
                <img :src="lightboxUrl" alt="Preview" class="max-w-full max-h-full rounded-lg" />
            </div>
        </Teleport>
    </DashboardLayout>
</template>
