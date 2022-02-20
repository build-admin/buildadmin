import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import type { UserConfig } from 'vite'
import { isProd, loadEnv } from '/@/utils/vite'
import { svgBuilder } from '/@/components/icon/svg/index'

const { VITE_PORT, VITE_OPEN, VITE_BASE_PATH, VITE_OUT_DIR } = loadEnv()

const pathResolve = (dir: string): any => {
    return resolve(__dirname, '.', dir)
}

const alias: Record<string, string> = {
    '/@': pathResolve('./src/'),
    assets: pathResolve('./src/assets'),
    'vue-i18n': isProd(process.env.NODE_ENV) ? 'vue-i18n/dist/vue-i18n.cjs.prod.js' : 'vue-i18n/dist/vue-i18n.cjs.js',
}

// https://vitejs.cn/config/
const viteConfig: UserConfig = {
    plugins: [vue(), svgBuilder('./src/assets/icons/')],
    root: process.cwd(),
    resolve: { alias },
    base: VITE_BASE_PATH,
    server: {
        host: '0.0.0.0',
        port: VITE_PORT,
        open: VITE_OPEN,
    },
    build: {
        sourcemap: false,
        outDir: VITE_OUT_DIR,
        emptyOutDir: true,
        chunkSizeWarningLimit: 1500,
    },
    css: {
        postcss: {
            plugins: [
                {
                    postcssPlugin: 'internal:charset-removal',
                    AtRule: {
                        charset: (atRule) => {
                            if (atRule.name === 'charset') {
                                atRule.remove()
                            }
                        },
                    },
                },
            ],
        },
    },
}

export default viteConfig
