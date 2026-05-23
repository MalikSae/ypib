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
                'primary': '#082e8f',
                'primary-deep': '#052066',
                'primary-soft': '#4b6ecc',
                'on-primary': '#ffffff',
                'ink-button': '#000000',
                'on-ink-button': '#ffffff',
                'focus-blue': '#1a43a8',
                'link-blue': '#20449e',
                'oculus-purple': '#a121ce',
                'success': '#31a24c',
                'success-bg': '#24e400',
                'attention': '#f2a918',
                'warning': '#f7b928',
                'warning-bg': '#ffe200',
                'critical': '#e41e3f',
                'critical-strong': '#f0284a',
                'canvas': '#ffffff',
                'surface-soft': '#f1f4f7',
                'ink-deep': '#0a1317',
                'ink': '#1c1e21',
                'charcoal': '#444950',
                'slate': '#4b4c4f',
                'steel': '#5d6c7b',
                'stone': '#8595a4',
                'hairline': '#ced0d4',
                'hairline-soft': '#dee3e9',
                'disabled-text': '#bcc0c4',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
