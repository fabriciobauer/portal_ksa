import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Barlow', ...defaultTheme.fontFamily.sans],
                condensed: ['Barlow Condensed', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ksa: {
                    navy:    '#17304f',
                    blue:    '#1d4f91',
                    orange:  '#cc6a00',
                    green:   '#1c7c54',
                    red:     '#b42318',
                    bg:      '#f4f6fb',
                    surface: '#ffffff',
                    text:    '#101828',
                    muted:   '#667085',
                    border:  '#d0d5dd',
                },
            },
            screens: {
                xs: '375px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
