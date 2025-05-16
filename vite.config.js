import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS
                'resources/css/app.css',

                // TS
                'resources/ts/app.ts',
                'resources/ts/components/pin-input.ts',
            ],
            refresh: [`resources/views/**/*`],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
    resolve: {
        alias: {
            '@': '/resources/ts',
            '@components': '/resources/ts/components',
            '@types': '/resources/ts/types',
        },
    },
});
