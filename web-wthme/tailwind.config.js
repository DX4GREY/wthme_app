import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';



/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                navy:  '#002f45',
                teal:  '#bdd1d3',
                sand:  '#d2c296',
                cream: '#e0decd',
            },
            fontFamily: {
                sans:    ['Plus Jakarta Sans', 'sans-serif'],
                display: ['Playfair Display', 'serif'],
            },
        },
    },
    plugins: [],
};