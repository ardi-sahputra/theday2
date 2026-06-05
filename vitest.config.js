import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [vue()],
    resolve: { alias: { '@': path.resolve(__dirname, 'resources/js') } },
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['tests/js/**/*.test.js', 'resources/js/**/*.test.js'],
        exclude: ['**/node_modules/**', '.claude/**', 'public/**', 'vendor/**', 'android/**'],
    },
});
