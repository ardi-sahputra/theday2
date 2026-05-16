<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard, Users, CreditCard, FileText,
    User, Sun, Moon, MonitorSmartphone, LogOut, X,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

defineProps({
    mobileOpen: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const page = usePage();
const currentRoute = computed(() => page.url);

const { theme, cycleTheme } = useAdminTheme();

const mainNav = [
    { label: 'Dashboard',     icon: LayoutDashboard, href: '/admin' },
    { label: 'Users',         icon: Users,           href: '/admin/users' },
    { label: 'Subscriptions', icon: CreditCard,      href: '/admin/subscriptions' },
];
const contentNav = [
    { label: 'Articles', icon: FileText, href: '/admin/articles' },
];

function isActive(href) {
    if (href === '/admin') return currentRoute.value === '/admin';
    return currentRoute.value.startsWith(href);
}
</script>

<template>
    <aside
        :class="[
            'w-60 shrink-0 border-r border-border bg-card text-card-foreground flex flex-col font-admin',
            'fixed inset-y-0 left-0 z-40 transition-transform duration-[180ms] ease-out lg:static lg:translate-x-0',
            mobileOpen ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <div class="h-14 flex items-center justify-between px-4 border-b border-border">
            <Link href="/admin" class="font-semibold text-sm tracking-tight">TheDay Admin</Link>
            <button @click="emit('close')" class="lg:hidden text-muted-foreground hover:text-foreground">
                <X class="w-5 h-5" />
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
            <div>
                <p class="px-3 text-[10px] uppercase tracking-wider text-muted-foreground mb-2">Main</p>
                <Link
                    v-for="item in mainNav"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors duration-[150ms] ease-out',
                        isActive(item.href)
                            ? 'bg-accent text-accent-foreground font-medium'
                            : 'text-muted-foreground hover:bg-accent/50 hover:text-foreground',
                    ]"
                >
                    <component :is="item.icon" class="w-4 h-4" />
                    {{ item.label }}
                </Link>
            </div>

            <div>
                <p class="px-3 text-[10px] uppercase tracking-wider text-muted-foreground mb-2">Content</p>
                <Link
                    v-for="item in contentNav"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors duration-[150ms] ease-out',
                        isActive(item.href)
                            ? 'bg-accent text-accent-foreground font-medium'
                            : 'text-muted-foreground hover:bg-accent/50 hover:text-foreground',
                    ]"
                >
                    <component :is="item.icon" class="w-4 h-4" />
                    {{ item.label }}
                </Link>
            </div>
        </nav>

        <div class="border-t border-border p-3 space-y-1">
            <button
                @click="cycleTheme"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-muted-foreground hover:bg-accent/50 hover:text-foreground transition-colors duration-[150ms] ease-out"
                :aria-label="`Cycle theme (current: ${theme})`"
            >
                <Sun v-if="theme === 'light'" class="w-4 h-4" />
                <Moon v-else-if="theme === 'dark'" class="w-4 h-4" />
                <MonitorSmartphone v-else class="w-4 h-4" />
                <span class="capitalize">{{ theme }}</span>
            </button>

            <form method="POST" action="/admin/logout">
                <input type="hidden" name="_token" :value="page.props.csrf_token ?? ''">
                <button
                    type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-muted-foreground hover:bg-accent/50 hover:text-foreground transition-colors duration-[150ms] ease-out"
                >
                    <LogOut class="w-4 h-4" />
                    Logout
                </button>
            </form>
        </div>
    </aside>
</template>
