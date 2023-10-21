import type { RouteRecordRaw } from 'vue-router'

/**
 * 寻找当前路由在顶栏菜单中的数据
 */
export const currentRouteTopActivity = (path: string, menus: RouteRecordRaw[]): RouteRecordRaw | false => {
    for (let i = 0; i < menus.length; i++) {
        const item: RouteRecordRaw = menus[i]
        // 找到目标
        if (item.path == path) return item
        // 从子级继续寻找
        if (item.children && item.children.length > 0) {
            const find = currentRouteTopActivity(path, item.children)
            if (find) return item
        }
    }
    return false
}
