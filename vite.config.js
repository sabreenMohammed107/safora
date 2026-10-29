import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Cairo', {
                    weights: [400, 500, 600, 700, 800],
                    subsets: ['arabic', 'latin'],
                }),
                bunny('Inter', {
                    weights: [400, 500, 600, 700, 800],
                }),
                bunny('Montserrat', {
                    weights: [800],
                    preload: false,
                }),
                bunny('JetBrains Mono', {
                    weights: [400, 600],
                    preload: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
