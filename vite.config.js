import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Allow external connections
        port: 5173,
        cors: true, // Enable CORS
        hmr: {
            host: '192.168.100.126' // Replace with your actual network IP
        }
    },
    optimizeDeps: {
        include: ['chart.js']
    },
    build: {
        rollupOptions: {
            external: [],
            output: {
                globals: {
                    'chart.js': 'Chart'
                }
            }
        }
    }
});