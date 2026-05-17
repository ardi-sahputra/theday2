<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard, Users, CreditCard, FileText, Gift, Package,
    Sun, Moon, MonitorSmartphone, LogOut, X, ChevronRight,
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

const sections = [
    {
        title: 'Main',
        items: [
            { label: 'Dashboard',     icon: LayoutDashboard, href: '/admin' },
            { label: 'Users',         icon: Users,           href: '/admin/users' },
            { label: 'Subscriptions', icon: CreditCard,      href: '/admin/subscriptions' },
            { label: 'Gift Pro',      icon: Gift,            href: '/admin/gifts' },
            { label: 'Paket',         icon: Package,         href: '/admin/plans' },
        ],
    },
    {
        title: 'Content',
        items: [
            { label: 'Articles', icon: FileText, href: '/admin/articles' },
        ],
    },
];

function isActive(href) {
    if (href === '/admin') return currentRoute.value === '/admin';
    return currentRoute.value.startsWith(href);
}

const themeIcon = computed(() => {
    if (theme.value === 'light') return Sun;
    if (theme.value === 'dark') return Moon;
    return MonitorSmartphone;
});
</script>

<template>
    <aside
        :class="[
            'w-64 shrink-0 flex flex-col font-admin',
            'border-r border-border bg-card text-card-foreground',
            'fixed inset-y-0 left-0 z-40 transition-transform duration-[180ms] ease-admin lg:static lg:translate-x-0',
            mobileOpen ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <!-- Brand header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-border">
            <Link
                href="/admin"
                class="flex items-center gap-2.5 group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/40 rounded-md"
            >
                <img
                    src="/image/assets/08-appicon-sage.svg"
                    alt=""
                    width="32"
                    height="32"
                    class="h-8 w-8 rounded-lg shadow-sm"
                />
                <div class="leading-tight">
                    <p class="text-[13px] font-semibold tracking-tight text-foreground">TheDay</p>
                    <p class="text-[10px] uppercase tracking-wider text-muted-foreground">Admin</p>
                </div>
            </Link>
            <button
                @click="emit('close')"
                class="lg:hidden inline-flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors duration-150"
                aria-label="Close sidebar"
            >
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-5 space-y-6 overflow-y-auto">
            <div v-for="section in sections" :key="section.title">
                <p class="px-3 mb-1.5 text-[10px] font-semibold uppercase tracking-[0.08em] text-muted-foreground/80">
                    {{ section.title }}
                </p>
                <Link
                    v-for="item in section.items"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'group relative flex items-center gap-3 pl-3 pr-2 py-2 rounded-md text-sm transition-colors duration-150 ease-admin',
                        isActive(item.href)
                            ? 'bg-brand-primary/10 text-foreground font-medium dark:bg-brand-primary/15'
                            : 'text-muted-foreground hover:bg-accent/60 hover:text-foreground',
                    ]"
                    :aria-current="isActive(item.href) ? 'page' : undefined"
                >
                    <!-- Sage active indicator -->
                    <span
                        v-if="isActive(item.href)"
                        aria-hidden="true"
                        class="absolute left-0 top-1.5 bottom-1.5 w-[3px] rounded-r-full bg-brand-primary"
                    />
                    <component
                        :is="item.icon"
                        :class="[
                            'w-4 h-4 shrink-0 transition-colors',
                            isActive(item.href) ? 'text-brand-primary' : 'text-muted-foreground group-hover:text-foreground',
                        ]"
                    />
                    <span class="flex-1 truncate">{{ item.label }}</span>
                    <ChevronRight
                        v-if="isActive(item.href)"
                        class="w-3.5 h-3.5 text-brand-primary opacity-70"
                        aria-hidden="true"
                    />
                </Link>
            </div>
        </nav>

        <!-- Footer: theme + logout -->
        <div class="border-t border-border p-3 space-y-1">
            <button
                @click="cycleTheme"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-muted-foreground hover:bg-accent/60 hover:text-foreground transition-colors duration-150 ease-admin"
                :aria-label="`Cycle theme (current: ${theme})`"
            >
                <component :is="themeIcon" class="w-4 h-4" aria-hidden="true" />
                <span class="capitalize">{{ theme }}</span>
                <span class="ml-auto text-[10px] uppercase tracking-wider text-muted-foreground/60">theme</span>
            </button>

            <form method="POST" action="/admin/logout">
                <input type="hidden" name="_token" :value="page.props.csrf_token ?? ''">
                <button
                    type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-colors duration-150 ease-admin"
                >
                    <LogOut class="w-4 h-4" aria-hidden="true" />
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>
</template>
