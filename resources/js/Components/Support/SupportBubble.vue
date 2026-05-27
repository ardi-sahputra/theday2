<script setup>
import { ref } from 'vue';
import { useSupportChat } from '@/Composables/useSupportChat';
import SupportChatIcon from './SupportChatIcon.vue';
import SupportChatPanel from './SupportChatPanel.vue';

const chat = useSupportChat();
const lightboxUrl = ref(null);

function toggle() { chat.isOpen.value = !chat.isOpen.value; }
function close()  { chat.isOpen.value = false; }
function onSend(body, file) { chat.sendMessage(body, file); }
function previewImage(url) { lightboxUrl.value = url; }
function closeLightbox() { lightboxUrl.value = null; }
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50">
        <!-- Collapsed bubble -->
        <button
            v-if="!chat.isOpen.value"
            type="button"
            @click="toggle"
            class="relative w-14 h-14 rounded-full bg-brand-primary hover:bg-brand-primary-hover text-white shadow-xl hover:scale-105 transition-transform"
            aria-label="Buka chat support"
        >
            <SupportChatIcon class="w-6 h-6 mx-auto" />
            <span
                v-if="chat.unreadCount.value > 0"
                class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[11px] font-semibold flex items-center justify-center"
            >
                {{ chat.unreadCount.value }}
            </span>
        </button>

        <!-- Expanded panel -->
        <div
            v-else
            class="w-[384px] h-[600px] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden"
        >
            <SupportChatPanel
                :messages="chat.messages.value"
                :admin-status="chat.adminStatus.value"
                :is-sending="chat.isSending.value"
                :send-error="chat.sendError.value"
                @send="onSend"
                @close="close"
                @preview-image="previewImage"
            />
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <div
                v-if="lightboxUrl"
                @click="closeLightbox"
                class="fixed inset-0 z-[100] bg-black/80 flex items-center justify-center p-6 cursor-zoom-out"
            >
                <img :src="lightboxUrl" alt="Preview" class="max-w-full max-h-full rounded-lg" />
            </div>
        </Teleport>
    </div>
</template>
