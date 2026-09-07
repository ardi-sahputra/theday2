import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/app-mobile/main.js',
                ],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
        server: {
            // 0.0.0.0 so a phone on the LAN can still reach the dev server, but the
            // HMR socket defaults to 127.0.0.1 to match APP_URL and the Google OAuth
            // redirect. Set VITE_HMR_HOST to the LAN IP when testing on a device.
            host: '0.0.0.0',
            hmr: {
                host: env.VITE_HMR_HOST || '127.0.0.1',
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
