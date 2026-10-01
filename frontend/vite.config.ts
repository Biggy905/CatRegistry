import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    vue(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 8088,
    strictPort: true,
    host: '0.0.0.0',
    hmr: false,
    proxy: {
      '/api': {
        target: 'http://cat-registry-nginx:7000',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, ''),
      },
    },
    watch: {
      usePolling: true,
    },
  },
  preview: {
    port: 8088,
    strictPort: true,
    host: true,
  },
})
