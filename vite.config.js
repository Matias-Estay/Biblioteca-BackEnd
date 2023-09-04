import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';

export default defineConfig({
    https: { 
        key: fs.readFileSync('resources/certificates/ssl.key'), 
        cert: fs.readFileSync('resources/certificates/ssl.csr'), 
    }, 
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
