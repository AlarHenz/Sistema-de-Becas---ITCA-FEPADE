import { basename, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

const frontendDirectory = fileURLToPath(new URL('.', import.meta.url));
const projectDirectory = basename(resolve(frontendDirectory, '..'));

export default defineConfig({
  base: './',
  plugins: [react()],
  server: {
    port: 5173,
    strictPort: true,
    proxy: {
      '/api': {
        target: 'http://localhost',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, `/${projectDirectory}/public/api`),
      },
    },
  },
  build: {
    outDir: resolve(frontendDirectory, '../public/app'),
    emptyOutDir: true,
  },
});
