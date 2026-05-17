import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import axios from 'axios';
import { i18n, setI18nMessages } from './Composables/i18n';
import VueApexCharts from 'vue3-apexcharts';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

axios.defaults.headers.common['X-Locale'] = i18n.global.locale.value;

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const initial = props.initialPage?.props ?? {};
        if (initial.translations && initial.locale) {
            setI18nMessages(initial.locale, initial.translations);
        }

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .component('VueApexCharts', VueApexCharts)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Refresh messages whenever Inertia navigates / partial-reloads
router.on('success', (event) => {
    const props = event.detail?.page?.props ?? {};
    if (props.translations && props.locale) {
        setI18nMessages(props.locale, props.translations);
    }
});
