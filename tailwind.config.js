import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                admin: ['Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
                brand: {
                    primary: '#92A89C',
                    'primary-hover': '#73877C',
                    'primary-soft': '#B8C7BF',
                    premium: '#C8A26B',
                    'premium-hover': '#B8905A',
                    text: '#2C2417',
                    bg: '#FFFCF7',
                    // ── dashboard redesign additions ──
                    ink: '#1F2A2E',
                    'ink-2': '#3D4A4D',
                    muted: '#6C7A75',
                    'sage-dark': '#6F8270',
                    'sage-deep': '#4A5A4C',
                    'sage-tint': '#C7D3BC',
                    'sage-soft': '#DCE4D3',
                    cream: '#F4EDDC',
                    'cream-2': '#E9DFC4',
                    blush: '#D9B5B0',
                    'blush-deep': '#C19089',
                    gold: '#C9A45B',
                    amber: '#D9A24A',
                    line: '#D8DFD2',
                    'line-2': '#C7D0BE',
                    'page-bg': '#EEF2EA',
                    accent: '#2563EB',
                    'accent-hover': '#1D4ED8',
                },
                // Admin data accent — blue per ui-ux-pro-max recommendation
                admin: {
                    accent: '#2563EB',
                    'accent-hover': '#1D4ED8',
                },
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            // Admin-tuned transition timing (ui-ux-pro-max: 150-300ms ease-out)
            transitionTimingFunction: {
                'admin': 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
            transitionDuration: {
                '180': '180ms',
            },
        },
    },

    plugins: [forms],
};
