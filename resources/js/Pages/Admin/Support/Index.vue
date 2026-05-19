<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConversationList from './components/ConversationList.vue';
import AdminChatPanel from './components/AdminChatPanel.vue';

const props = defineProps({
    conversations:         { type: Object, default: null },
    selected_conversation: { type: Object, default: null },
    messages:              { type: Array,  default: () => [] },
    filters:               { type: Object, default: () => ({ filter: 'open', q: '' }) },
});

const selectedId = computed(() => props.selected_conversation?.id ?? null);

function onResolved() {
    router.reload({ only: ['conversations', 'selected_conversation'] });
}
</script>

<template>
    <Head title="Support Chat" />
    <AdminLayout>
        <div class="flex h-[calc(100vh-4rem)] -m-4 lg:-m-6">
            <div class="w-80 flex-shrink-0">
                <ConversationList
                    v-if="conversations"
                    :conversations="conversations"
                    :selected-id="selectedId"
                    :filter="filters.filter"
                />
            </div>
            <div class="flex-1 min-w-0">
                <AdminChatPanel
                    v-if="selected_conversation"
                    :conversation="selected_conversation"
                    :initial-messages="messages"
                    @resolved="onResolved"
                />
                <div v-else class="flex items-center justify-center h-full text-sm text-stone-400">
                    Pilih conversation dari daftar untuk mulai chat.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
