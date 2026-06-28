import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

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
        wayfinder({
            formVariants: true,
        }),
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
        port: vitePort,
        strictPort: true,
        cors: true,
        hmr: {
            host: '127.0.0.1',
            protocol: 'ws',
            clientPort: vitePort,
        },
    },
});
