import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

// Standalone SPA build for the Capacitor app (no Laravel/Inertia).
export default defineConfig({
    root: path.resolve(__dirname, 'resources/js/app-mobile'),
    base: './',
    plugins: [vue()],
    resolve: {
        alias: { '@': path.resolve(__dirname, 'resources/js') },
    },
    build: {
        outDir: path.resolve(__dirname, 'app-dist'),
        emptyOutDir: true,
    },
});
