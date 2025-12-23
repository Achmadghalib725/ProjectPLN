import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        https: fs.existsSync(path.resolve(__dirname, 'certs/localhost-key.pem')) ? {
            key: fs.readFileSync(path.resolve(__dirname, 'certs/localhost-key.pem')),
            cert: fs.readFileSync(path.resolve(__dirname, 'certs/localhost.pem')),
        } : false,
        hmr: {
            host: '172.20.10.2',
        },
    },
});