import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { i18n } from './i18n';

const LANG_KEY = 'theday_lang';

export function useLocale() {
    const { t } = useI18n();
    const locale = i18n.global.locale;

    function setLocale(lang) {
        if (!['id', 'en'].includes(lang)) return;
        locale.value = lang;
        try { localStorage.setItem(LANG_KEY, lang); } catch (_) {}
        axios.defaults.headers.common['X-Locale'] = lang;
        // Persist as a cookie so the server reads it on full page loads + OAuth
        // redirects (where the X-Locale header is absent). 1 year, site-wide.
        try { document.cookie = `locale=${lang};path=/;max-age=31536000;SameSite=Lax`; } catch (_) {}
        // Refresh translations prop from server for the new locale
        router.reload({ only: ['translations', 'locale'] });
    }

    function toggleLocale() {
        setLocale(locale.value === 'id' ? 'en' : 'id');
    }

    // Deprecated — for callers not yet migrated to t('key')
    function tLegacy(id, en) {
        return locale.value === 'en' ? en : id;
    }

    return { locale, t, setLocale, toggleLocale, tLegacy };
}
