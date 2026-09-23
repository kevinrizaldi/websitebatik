import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        // Tab filter status aktif/non-aktif (orders & produk index)
        'bg-gray-900', 'text-white', 'font-semibold', 'shadow-sm',
        'bg-gray-100', 'text-gray-700', 'hover:bg-gray-200',
        'bg-white/20',
        'bg-white', 'text-gray-600', 'border', 'border-gray-200',
        // Status badge orders ($statusStyle)
        'bg-amber-50',  'text-amber-800',  'border-amber-200',  'bg-amber-500',
        'bg-blue-50',   'text-blue-800',   'border-blue-200',   'bg-blue-500',
        'bg-indigo-50', 'text-indigo-800', 'border-indigo-200', 'bg-indigo-500',
        'bg-sky-50',    'text-sky-800',    'border-sky-200',    'bg-sky-500',
        'bg-emerald-50','text-emerald-800','border-emerald-200','bg-emerald-500',
        'bg-rose-50',   'text-rose-800',   'border-rose-200',   'bg-rose-500',
        'bg-gray-50',   'text-gray-800',   'bg-gray-500',
        // hover row
        'hover:bg-gray-50/70', 'transition-colors',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
