import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                dark: {
                    base: '#0F0F14',
                    surface: '#1A1A24',
                    deeper: '#0D0D12',
                },
                accent: {
                    pink: '#E8356D',
                    purple: '#7C3AED',
                    green: '#10B981',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                cairo: ['Cairo', 'sans-serif'],
                outfit: ['Outfit', 'sans-serif'],
            },
            boxShadow: {
                'glow-pink': '0 0 24px rgba(232, 53, 109, 0.15)',
                'glow-purple': '0 0 24px rgba(124, 58, 237, 0.15)',
            },
        },
    },

    plugins: [forms],
};
