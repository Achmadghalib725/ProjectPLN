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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Update Warna Sesuai Request
            colors: {
                pln: {
                    primary: '#035b71',    // "Biru Profesional" (Warna dasar gelap)
                    light: '#00aff0',      // "Merah" di request Anda (Hex ini adalah Biru Laut/Cyan PLN)
                    red: '#ff0000',        // "Biru" di request Anda (Hex ini adalah Merah)
                    yellow: '#ffff00',     // Kuning terang
                }
            }
        },
    },

    plugins: [forms],
};