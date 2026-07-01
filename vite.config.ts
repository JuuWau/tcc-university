import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

const isSail = process.env.LARAVEL_SAIL === '1';
const vitePort = Number(process.env.VITE_PORT ?? 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        {
            name: 'force-sail-hot-file',
            configureServer(server) {
                import('fs').then((fs) => {
                    const hotPath = 'public/hot';
                    fs.writeFileSync(hotPath, `http://localhost:${vitePort}`);
                });
            }
        }
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            usePolling: true,
            interval: 1000,
            binaryInterval: 3000,
        },
        fs: {
            strict: false,
        },
    },
    optimizeDeps: {
        include: ['vue', 'inertia', '@inertiajs/vue3'],
        exclude: ['@tailwindcss/vite'],
    },
    build: {
        sourcemap: false,
        minify: false,
    },
});
