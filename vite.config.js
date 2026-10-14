import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, lazyPlugins } from 'vite-plus';

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
            ],
            refresh: true,
            fonts: [
                // Kompetent CI: Inter (Light 300, Regular 400, Semibold 600, Bold 700), Noto Sans Arabic for Arabic.
                bunny('Inter', {
                    weights: [300, 400, 500, 600, 700],
                    subsets: ['latin', 'latin-ext', 'cyrillic', 'cyrillic-ext'],
                }),
                bunny('Noto Sans Arabic', {
                    weights: [400, 600],
                    subsets: ['arabic'],
                }),
            ],
        }),
        tailwindcss(),
    ]),
    server: {
        cors: true,
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
});
