import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Warna latar sidebar. Teks menu aktif memakai warna yang sama, jadi cukup ubah di sini.
const sidebarBg = '#704ef8';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './Modules/**/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#0D9488',
                    hover:   '#0F766E',
                    light:   '#CCFBF1',
                    text:    '#FFFFFF',
                },
                success: {
                    DEFAULT: '#22C55E',
                    light:   '#DCFCE7',
                    text:    '#166534',
                },
                danger: {
                    DEFAULT: '#EF4444',
                    hover:   '#DC2626',
                    light:   '#FEE2E2',
                    text:    '#991B1B',
                },
                warning: {
                    DEFAULT: '#F59E0B',
                    light:   '#FEF3C7',
                    text:    '#92400E',
                },
                info: {
                    DEFAULT: '#3B82F6',
                    light:   '#DBEAFE',
                    text:    '#1E40AF',
                },
                sidebar: {
                    DEFAULT: sidebarBg,
                    hover:   'rgba(255, 255, 255, 0.15)',
                    active:  '#ffffff',
                    text:    '#ffffff',
                    'text-active': sidebarBg,
                },
                page:   '#F8FAFC',
                card:   '#FFFFFF',
                border: '#E2E8F0',
            },
            boxShadow: {
                card:     '0 1px 3px 0 rgb(0 0 0 / 0.07), 0 1px 2px -1px rgb(0 0 0 / 0.07)',
                dropdown: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
            },
        },
    },

    plugins: [forms],
};
