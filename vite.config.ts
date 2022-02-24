import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import type { UserConfig } from 'vite'
import { loadEnv } from '/@/utils/vite'

const { VITE_PORT, VITE_OPEN, VITE_BASE_PATH } = loadEnv()

const pathResolve = (dir: string): any => {
    return resolve(__dirname, '.', dir)
}

const alias: Record<string, string> = {
    '/@': pathResolve('./src/'),
    assets: pathResolve('./src/assets'),
    'vue-i18n': 'vue-i18n/dist/vue-i18n.cjs.prod.js',
}

// https://vitejs.cn/config/
const viteConfig: UserConfig = {
    plugins: [vue()],
    root: process.cwd(),
    resolve: { alias },
    base: VITE_BASE_PATH,
    build: {
        sourcemap: false,
        outDir: '../public/install/',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                entryFileNames: `assets/[name].js`,
                chunkFileNames: `assets/[name].js`,
                assetFileNames: `assets/[name].[ext]`,
            },
        },
    },
    server: {
        host: '0.0.0.0',
        port: VITE_PORT,
        open: VITE_OPEN,
    },
}

export default viteConfig
