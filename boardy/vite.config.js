import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/comments.jsx', 'resources/js/auth.js', 'resources/js/pkce.js', 'resources/js/oauth-callback.js'],
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: '0.0.0.0',
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
    },
});