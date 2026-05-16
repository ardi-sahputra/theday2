<script setup>
import { ref } from 'vue';
import AdminSidebar from '@/Components/admin/AdminSidebar.vue';
import AdminTopbar from '@/Components/admin/AdminTopbar.vue';
import { Toaster } from '@/Components/ui/sonner';

defineProps({
    breadcrumb: { type: String, default: '' },
});

const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen flex bg-background text-foreground font-admin antialiased">
        <AdminSidebar
            :mobile-open="sidebarOpen"
            @close="sidebarOpen = false"
        />

        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-black/40 lg:hidden"
        />

        <div class="flex-1 flex flex-col min-w-0">
            <AdminTopbar
                :breadcrumb="breadcrumb"
                @open-sidebar="sidebarOpen = true"
            />

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <slot />
            </main>
        </div>

        <Toaster richColors closeButton />
    </div>
</template>
