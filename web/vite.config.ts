import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import type { UserConfig, ConfigEnv } from 'vite'
import { isProd, loadEnv } from '/@/utils/vite'
import { svgBuilder } from '/@/components/icon/svg/index'

const pathResolve = (dir: string): any => {
    return resolve(__dirname, '.', dir)
}

// https://vitejs.cn/config/
const viteConfig = ({ mode }: ConfigEnv): UserConfig => {
    const { VITE_PORT, VITE_OPEN, VITE_BASE_PATH, VITE_OUT_DIR } = loadEnv(mode)
    // 如需启用开发环境下的跨域代理，请解除下面的注释，请注意：跨域代理不会被打包进生产环境！
    // const { VITE_PORT, VITE_OPEN, VITE_BASE_PATH, VITE_OUT_DIR, VITE_PROXY_URL } = loadEnv(mode)

    const alias: Record<string, string> = {
        '/@': pathResolve('./src/'),
        assets: pathResolve('./src/assets'),
        'vue-i18n': isProd(mode) ? 'vue-i18n/dist/vue-i18n.cjs.prod.js' : 'vue-i18n/dist/vue-i18n.cjs.js',
    }

    return {
        plugins: [vue(), svgBuilder('./src/assets/icons/')],
        root: process.cwd(),
        resolve: { alias },
        base: VITE_BASE_PATH,
        server: {
            host: '0.0.0.0',
            port: VITE_PORT,
            open: VITE_OPEN,
            // 如需启用开发环境下的跨域代理，请解除下面的注释，请注意：跨域代理不会被打包进生产环境！
            // proxy: {
            //     '/api': {
            //         target: VITE_PROXY_URL,
            //         changeOrigin: true,
            //         rewrite: path => path.replace(/^\/api/, '')
            //     }
            // }
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
}

export default viteConfig
