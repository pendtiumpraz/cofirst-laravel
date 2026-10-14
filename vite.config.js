import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Separate landing entries per template (performance budget).
                'resources/css/landing-a.css',
                'resources/css/landing-b.css',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Fonts are referenced from CSS and must stay separate files.
        assetsInlineLimit: 0,
    },
});
