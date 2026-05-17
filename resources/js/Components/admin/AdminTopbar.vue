<script setup>
import { Menu, Search, Bell, Sun, Moon, MonitorSmartphone, ChevronDown, LogOut } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

defineProps({
    breadcrumb: { type: String, default: '' },
});
const emit = defineEmits(['open-sidebar']);

const page = usePage();
const admin = computed(() => page.props.auth?.admin ?? null);
const initials = computed(() =>
    (admin.value?.name ?? 'A')
        .split(' ')
        .map(w => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()
);

const { theme, cycleTheme } = useAdminTheme();
const themeIcon = computed(() => {
    if (theme.value === 'light') return Sun;
    if (theme.value === 'dark') return Moon;
    return MonitorSmartphone;
});

const menuOpen = ref(false);
const menuRef = ref(null);

function closeOnClickOutside(e) {
    if (menuOpen.value && menuRef.value && !menuRef.value.contains(e.target)) {
        menuOpen.value = false;
    }
}
function closeOnEsc(e) {
    if (e.key === 'Escape') menuOpen.value = false;
}
onMounted(() => {
    document.addEventListener('mousedown', closeOnClickOutside);
    document.addEventListener('keydown', closeOnEsc);
});
onBeforeUnmount(() => {
    document.removeEventListener('mousedown', closeOnClickOutside);
    document.removeEventListener('keydown', closeOnEsc);
});
</script>

<template>
    <header
        class="h-16 shrink-0 flex items-center justify-between gap-3 px-4 lg:px-6
               border-b border-border bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/80
               sticky top-0 z-30"
    >
        <!-- Left: menu + title -->
        <div class="flex items-center gap-3 min-w-0">
            <button
                @click="emit('open-sidebar')"
                class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors duration-150"
                aria-label="Open sidebar"
            >
                <Menu class="w-5 h-5" />
            </button>
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-wider text-muted-foreground hidden sm:block">Admin</p>
                <h1 class="text-sm sm:text-base font-semibold tracking-tight truncate">
                    {{ breadcrumb || 'Dashboard' }}
                </h1>
            </div>
        </div>

        <!-- Right: search + actions -->
        <div class="flex items-center gap-2">
            <button
                class="hidden md:flex items-center gap-2 h-9 px-3 text-xs text-muted-foreground
                       border border-border rounded-md bg-background/60
                       hover:bg-accent hover:text-foreground hover:border-brand-primary/40
                       transition-colors duration-150 ease-admin
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/40"
                aria-label="Search (Cmd K)"
            >
                <Search class="w-3.5 h-3.5" aria-hidden="true" />
                <span class="pr-8">Search…</span>
                <kbd class="ml-auto px-1.5 py-0.5 bg-muted rounded text-[10px] font-mono">⌘K</kbd>
            </button>

            <button
                @click="cycleTheme"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors duration-150 ease-admin"
                :aria-label="`Theme: ${theme}`"
            >
                <component :is="themeIcon" class="w-4 h-4" aria-hidden="true" />
            </button>

            <button
                class="relative inline-flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors duration-150 ease-admin"
                aria-label="Notifications"
            >
                <Bell class="w-4 h-4" aria-hidden="true" />
                <span class="absolute top-1.5 right-1.5 h-1.5 w-1.5 rounded-full bg-brand-premium" aria-hidden="true" />
            </button>

            <div class="h-6 w-px bg-border mx-1" aria-hidden="true" />

            <!-- Avatar dropdown -->
            <div ref="menuRef" class="relative">
                <button
                    @click="menuOpen = !menuOpen"
                    :aria-expanded="menuOpen"
                    aria-haspopup="menu"
                    class="flex items-center gap-2 h-9 pl-1 pr-2 rounded-md hover:bg-accent transition-colors duration-150 ease-admin
                           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/40"
                >
                    <span
                        class="h-7 w-7 rounded-full bg-brand-primary text-white flex items-center justify-center text-[11px] font-semibold"
                        aria-hidden="true"
                    >
                        {{ initials }}
                    </span>
                    <span class="hidden sm:inline text-xs font-medium text-foreground max-w-[100px] truncate">
                        {{ admin?.name ?? 'Admin' }}
                    </span>
                    <ChevronDown class="hidden sm:block w-3.5 h-3.5 text-muted-foreground" aria-hidden="true" />
                </button>

                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="menuOpen"
                        role="menu"
                        class="absolute right-0 mt-2 w-56 rounded-lg border border-border bg-popover text-popover-foreground shadow-lg overflow-hidden"
                    >
                        <div class="px-3 py-2.5 border-b border-border">
                            <p class="text-sm font-medium truncate">{{ admin?.name ?? 'Admin' }}</p>
                            <p class="text-xs text-muted-foreground truncate">{{ admin?.email ?? '' }}</p>
                        </div>
                        <form method="POST" action="/admin/logout">
                            <input type="hidden" name="_token" :value="page.props.csrf_token ?? ''">
                            <button
                                type="submit"
                                role="menuitem"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-colors duration-150"
                            >
                                <LogOut class="w-4 h-4" aria-hidden="true" />
                                Logout
                            </button>
                        </form>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>
