import { ref, onMounted, onUnmounted } from 'vue';

const STORAGE_KEY = 'adminTheme';
const theme = ref(localStorage.getItem(STORAGE_KEY) ?? 'system');

function applyTheme(value) {
    const isDark = value === 'dark'
        || (value === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
}

function setTheme(value) {
    theme.value = value;
    localStorage.setItem(STORAGE_KEY, value);
    applyTheme(value);
}

function cycleTheme() {
    const order = ['light', 'dark', 'system'];
    const next = order[(order.indexOf(theme.value) + 1) % order.length];
    setTheme(next);
}

export function useAdminTheme() {
    let mediaQuery = null;
    let listener = null;

    onMounted(() => {
        applyTheme(theme.value);
        mediaQuery = matchMedia('(prefers-color-scheme: dark)');
        listener = () => { if (theme.value === 'system') applyTheme('system'); };
        mediaQuery.addEventListener('change', listener);
    });

    onUnmounted(() => {
        if (mediaQuery && listener) mediaQuery.removeEventListener('change', listener);
    });

    return { theme, setTheme, cycleTheme };
}
