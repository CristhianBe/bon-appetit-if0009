import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/api-demo.js',
                'resources/css/panel.css',
                'resources/js/panel.js',
                'resources/js/menu.js',
                'resources/js/pages/mesas.js',
                'resources/js/pages/pedidos.js',
                'resources/js/pages/reservas.js',
                'resources/js/pages/usuarios.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
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
