import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                /*
                | Paleta WAYNA — principal #f07e26, base neutra #E8E6E0
                | Valores alineados con variables en resources/css/app.css
                */
                wayna: {
                    50: '#fff6ee',
                    100: '#fde9d6',
                    200: '#facfaa',
                    300: '#f7b07d',
                    400: '#f3924f',
                    500: '#f07e26',
                    600: '#d96d1c',
                    700: '#b55916',
                    800: '#914512',
                    900: '#76380f',
                    950: '#4a2309',
                },
                surface: {
                    DEFAULT: '#E8E6E0',
                    50: '#f7f6f4',
                    100: '#E8E6E0',
                    200: '#dedcd6',
                    300: '#d0cec8',
                    muted: '#f0efec',
                    card: '#ffffff',
                },
            },
            backgroundColor: {
                page: '#E8E6E0',
            },
        },
    },

    plugins: [forms],
};
