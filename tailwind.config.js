import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                display: ['"Baloo 2"', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                soft: '0 18px 45px -20px rgba(245, 158, 11, 0.38)',
            },
        },
    },

    plugins: [forms],
};
