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
 * 自定义热更新处理插件
 */
export const customHotUpdate = (): Plugin => {
    const closeHmr = new Map<string, boolean>()

    return {
        name: 'vite-plugin-custom-hot-update',
        configureServer(server) {
            server.ws.on('custom:close-hot', (data) => {
                closeHmr.set(data.type, true)

                // 关闭文件添加和删除的监听
                server.watcher.removeAllListeners('add')
                server.watcher.removeAllListeners('unlink')
            })
            server.ws.on('custom:open-hot', (data) => {
                closeHmr.set(data.type, false)

                server.watcher.on('add', () => {
                    server.restart()
                })
                server.watcher.on('unlink', () => {
                    server.restart()
                })
            })
            server.ws.on('custom:reload-hot', () => {
                server.restart()
            })
        },
        handleHotUpdate() {
            const closeHmrs = Array.from(closeHmr.values())
            let closeHmrsBool = false
            for (const key in closeHmrs) {
                closeHmrsBool = closeHmrsBool || closeHmrs[key]
            }
            if (closeHmrsBool) return []
        },
    }
}
