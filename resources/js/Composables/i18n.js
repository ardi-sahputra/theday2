import { createI18n } from 'vue-i18n';

const STORED = (typeof localStorage !== 'undefined' && localStorage.getItem('theday_lang')) || null;
const initialLocale = STORED && ['id', 'en'].includes(STORED) ? STORED : 'id';

// Resolve translation keys that may be stored either as flat strings
// (e.g. "a.b.c": "value") or as nested objects ({a:{b:{c:"value"}}}).
// Try exact key first, then fall back to dot-path traversal.
function flatOrNestedResolver(obj, path) {
    if (obj == null || typeof obj !== 'object') return null;
    if (Object.prototype.hasOwnProperty.call(obj, path)) return obj[path];
    const parts = path.split('.');
    let cur = obj;
    for (const p of parts) {
        if (cur == null || typeof cur !== 'object') return null;
        cur = cur[p];
    }
    return cur ?? null;
}

export const i18n = createI18n({
    legacy: false,
    globalInjection: true,
    locale: initialLocale,
    fallbackLocale: 'id',
    messages: { id: {}, en: {} },
    missingWarn: false,
    fallbackWarn: false,
    messageResolver: flatOrNestedResolver,
});

export function setI18nMessages(locale, messages) {
    if (!messages || typeof messages !== 'object') return;
    i18n.global.setLocaleMessage(locale, messages);
}
