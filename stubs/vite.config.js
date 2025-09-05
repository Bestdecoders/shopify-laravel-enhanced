import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    define: {
        global: 'globalThis',
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['react', 'react-dom'],
                    shopify: ['@shopify/polaris', '@shopify/app-bridge', '@shopify/app-bridge-react'],
                    inertia: ['@inertiajs/react'],
                },
            },
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            '~': '/resources',
        },
    },
    optimizeDeps: {
        include: [
            'react',
            'react-dom',
            '@inertiajs/react',
            '@shopify/polaris',
            '@shopify/app-bridge',
            '@shopify/app-bridge-react',
        ],
    },
});