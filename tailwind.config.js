import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

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
                // --- NEW BRAND PALETTE ---
                primary: {
                    DEFAULT: '#0B41CB',
                    50: '#F2F5FD',
                    100: '#E5EBFA',
                    200: '#C3D0F4',
                    300: '#91ADF2',
                    400: '#5A83EC',
                    500: '#0E4EF1',
                    600: '#0B41CB',
                    700: '#0936A9',
                    800: '#0B2B7F',
                    900: '#092367',
                    950: '#09183E',
                },
                secondary: {
                    DEFAULT: '#F1B10E',
                    50: '#FDFAF2',
                    100: '#FAF4E5',
                    200: '#F4E6C3',
                    300: '#F2D791',
                    400: '#ECC35A',
                    500: '#F1B10E',
                    600: '#CB950B',
                    700: '#A97C09',
                    800: '#7F5E0B',
                    900: '#674D09',
                    950: '#3E2F09',
                },
                neutral: {
                    DEFAULT: '#646E87',
                    50: '#F7F7F8',
                    100: '#F1F2F4',
                    200: '#E3E4E8',
                    300: '#CDCFD6',
                    400: '#9A9FAC',
                    500: '#646E87',
                    600: '#4E566A',
                    700: '#3B404F',
                    800: '#272B35',
                    900: '#181A20',
                    950: '#0D0E12',
                },
                success: {
                    ...colors.emerald,
                    DEFAULT: colors.emerald[500],
                    light: colors.emerald[100],
                    dark: colors.emerald[800],
                },
                warning: {
                    ...colors.amber,
                    DEFAULT: colors.amber[500],
                    light: colors.amber[100],
                    dark: colors.amber[800],
                },
                error: {
                    ...colors.red,
                    DEFAULT: colors.red[500],
                    light: colors.red[100],
                    dark: colors.red[800],
                },
                info: {
                    ...colors.blue,
                    DEFAULT: colors.blue[500],
                    light: colors.blue[100],
                    dark: colors.blue[800],
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                'marquee': 'marquee 40s linear infinite',
                'float': 'float 6s ease-in-out infinite',
            },
            keyframes: {
                marquee: {
                    '0%': { transform: 'translateX(0%)' },
                    '100%': { transform: 'translateX(-100%)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                }
            }
        },
    },

    plugins: [forms],
};
