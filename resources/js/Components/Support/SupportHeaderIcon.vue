<script setup>
import { Link } from '@inertiajs/vue3';
import { useSupportChat } from '@/Composables/useSupportChat';
import SupportChatIcon from './SupportChatIcon.vue';

const chat = useSupportChat();
</script>

<template>
    <Link href="/dashboard/support" class="relative inline-flex items-center justify-center p-2 text-stone-600 hover:text-stone-900">
        <SupportChatIcon class="w-5 h-5" />
        <span
            v-if="chat.unreadCount.value > 0"
            class="absolute top-1 right-1 min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center"
        >
            {{ chat.unreadCount.value }}
        </span>
    </Link>
</template>
