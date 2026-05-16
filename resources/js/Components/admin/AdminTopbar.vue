<script setup>
import { Menu, Search } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    breadcrumb: { type: String, default: '' },
});
const emit = defineEmits(['open-sidebar']);

const page = usePage();
const admin = computed(() => page.props.auth?.admin ?? null);
const initials = computed(() => (admin.value?.name ?? 'A').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase());
</script>

<template>
    <header class="h-14 shrink-0 flex items-center justify-between px-4 lg:px-6 border-b border-border bg-card">
        <div class="flex items-center gap-3">
            <button @click="emit('open-sidebar')" class="lg:hidden text-muted-foreground hover:text-foreground" aria-label="Open sidebar">
                <Menu class="w-5 h-5" />
            </button>
            <p class="text-sm font-medium font-admin truncate">{{ breadcrumb || 'Dashboard' }}</p>
        </div>

        <div class="flex items-center gap-3">
            <button class="hidden md:flex items-center gap-2 px-3 py-1.5 text-xs text-muted-foreground border border-border rounded-md hover:bg-accent/50 transition-colors">
                <Search class="w-3.5 h-3.5" />
                <span>Search…</span>
                <kbd class="ml-4 px-1.5 py-0.5 bg-muted rounded text-[10px]">⌘K</kbd>
            </button>

            <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-medium">
                {{ initials }}
            </div>
        </div>
    </header>
</template>
