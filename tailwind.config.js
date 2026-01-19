const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                tenant: {
                    50: 'var(--tenant-color-50)',
                    100: 'var(--tenant-color-100)',
                    200: 'var(--tenant-color-200)',
                    300: 'var(--tenant-color-300)',
                    400: 'var(--tenant-color-400)',
                    500: 'var(--tenant-color-500)',
                    600: 'var(--tenant-color-600)',
                    700: 'var(--tenant-color-700)',
                    800: 'var(--tenant-color-800)',
                    900: 'var(--tenant-color-900)'
                }
            }
        },
    },

    variants: {
        extend: {
            opacity: ['disabled'],
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
