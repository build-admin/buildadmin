import { ref } from 'vue'
import { Plugin } from 'vite'

/**
 * 是否在开发环境
 */
export function isDev(mode: string): boolean {
    return mode === 'development'
}

/**
 * 是否在生产环境
 */
export function isProd(mode: string | undefined): boolean {
    return mode === 'production'
}

/**
 * 调试模式下的热更新开关状态
 */
export const hotUpdateSwitch = ref(true)

/**
 * 调试模式下关闭热更新
 */
export const closeHotUpdate = (type: string) => {
    if (!hotUpdateSwitch.value) return
    hotUpdateSwitch.value = false

    if (import.meta.hot) {
        import.meta.hot.send('custom:close-hot', { type })
    }
}

/**
 * 调试模式下开启热更新
 */
export const openHotUpdate = (type: string) => {
    if (hotUpdateSwitch.value) return
    hotUpdateSwitch.value = true

    if (import.meta.hot) {
        import.meta.hot.send('custom:open-hot', { type })
    }
}

/**
 * 调试模式下重启服务并刷新网页
 */
export const reloadServer = (type: string) => {
    hotUpdateSwitch.value = true
    if (import.meta.hot) {
        import.meta.hot.send('custom:reload-server', { type })
    }
}

/**
 * 自定义热更新处理插件
 */
export const customHotUpdate = (): Plugin => {
    let hotUpdateSwitch = true
    return {
        name: 'vite-plugin-custom-hot-update',
        apply: 'serve',
        configureServer(server) {
            server.ws.on('custom:close-hot', () => {
                hotUpdateSwitch = false
                // 关闭文件添加和删除的监听
                server.watcher.removeAllListeners('add')
                server.watcher.removeAllListeners('unlink')
            })
            server.ws.on('custom:open-hot', () => {
                hotUpdateSwitch = true
                server.watcher.on('add', () => {
                    server.restart()
                })
                server.watcher.on('unlink', () => {
                    server.restart()
                })
            })
            server.ws.on('custom:reload-server', () => {
                hotUpdateSwitch = true
                server.restart()
            })
        },
        handleHotUpdate() {
            if (!hotUpdateSwitch) {
                return []
            }
        },
    }
}
