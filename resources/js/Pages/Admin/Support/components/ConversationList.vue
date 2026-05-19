<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    conversations: { type: Object, required: true },
    selectedId:    { type: [Number, String], default: null },
    filter:        { type: String, default: 'open' },
});

function formatTime(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const now = new Date();
    if (d.toDateString() === now.toDateString()) {
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
}
</script>

<template>
    <div class="border-r border-stone-200 h-full overflow-y-auto bg-stone-50">
        <div class="p-3 border-b border-stone-200 bg-white sticky top-0 z-10">
            <div class="flex items-center gap-2 text-xs flex-wrap">
                <Link :href="`/admin/support?filter=open`"     :class="['px-2 py-1 rounded-full', filter==='open'     ? 'bg-stone-800 text-white' : 'bg-stone-200 text-stone-600']">Open</Link>
                <Link :href="`/admin/support?filter=unread`"   :class="['px-2 py-1 rounded-full', filter==='unread'   ? 'bg-stone-800 text-white' : 'bg-stone-200 text-stone-600']">Unread</Link>
                <Link :href="`/admin/support?filter=resolved`" :class="['px-2 py-1 rounded-full', filter==='resolved' ? 'bg-stone-800 text-white' : 'bg-stone-200 text-stone-600']">Resolved</Link>
                <Link :href="`/admin/support?filter=all`"      :class="['px-2 py-1 rounded-full', filter==='all'      ? 'bg-stone-800 text-white' : 'bg-stone-200 text-stone-600']">All</Link>
            </div>
        </div>

        <ul>
            <li v-for="c in conversations.data" :key="c.id">
                <Link
                    :href="`/admin/support/${c.id}`"
                    :class="['block px-3 py-3 border-b border-stone-100 hover:bg-white transition-colors', String(selectedId) === String(c.id) ? 'bg-white' : '']"
                >
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-semibold text-stone-800 truncate">{{ c.user.name }}</p>
                        <span class="text-[10px] text-stone-400">{{ formatTime(c.last_message_at) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs text-stone-500 truncate flex-1">
                            {{ c.latest_message?.body ?? (c.latest_message ? '[gambar]' : '') }}
                        </p>
                        <span
                            v-if="c.unread_by_admin_count > 0"
                            class="flex-shrink-0 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center"
                        >
                            {{ c.unread_by_admin_count }}
                        </span>
                        <span v-if="c.resolved_at" class="text-[10px] text-green-600">✓ Resolved</span>
                    </div>
                </Link>
            </li>
        </ul>

        <div v-if="!conversations.data.length" class="text-center text-xs text-stone-400 mt-8">
            Tidak ada conversation di filter ini.
        </div>
    </div>
</template>
