import router from '/@/router/index'
import { viewMenu } from '../stores/interface'
import { isNavigationFailure, NavigationFailureType } from 'vue-router'
import { ElNotification } from 'element-plus'
import { useNavTabs } from '../stores/navTabs'
import { adminBaseRoute } from '/@/router/static'

export const clickMenu = (menu: viewMenu) => {
    switch (menu.type) {
        case 'tab':
            routePush(menu.name)
            break
        case 'link':
            window.open(menu.path, '_blank')
            break
        case 'iframe':
            routePush('', {}, menu.path)
            break

        default:
            ElNotification({
                message: '导航失败，菜单类型无法识别！',
                type: 'error',
            })
            break
    }
}

/**
 * 导航失败有错误消息的路由push
 * @param name 路由name
 * @param params 路由参数
 * @param path 路由path,通过path导航无法传递@param params
 */
export const routePush = async (name: string = '', params: anyObj = {}, path: string = '') => {
    try {
        const failure = await router.push(name ? { name: name, params: params } : { path: path })
        if (isNavigationFailure(failure, NavigationFailureType.aborted)) {
            ElNotification({
                message: '导航失败，导航守卫拦截！',
                type: 'error',
            })
        } else if (isNavigationFailure(failure, NavigationFailureType.duplicated)) {
            ElNotification({
                message: '导航失败，已在导航目标位置！',
                type: 'warning',
            })
        }
    } catch (error) {
        ElNotification({
            message: '导航失败，路由无效！',
            type: 'error',
        })
        console.error(error)
    }
}

export const pushFirstRoute = () => {
    const navTabs = useNavTabs()
    let routerNames = []
    let routers = router.getRoutes()
    for (const key in routers) {
        if (routers[key].name) routerNames.push(routers[key].name)
    }
    for (const key in navTabs.state.tabsViewRoutes) {
        if (navTabs.state.tabsViewRoutes[key].type != 'menu_dir' && routerNames.indexOf(navTabs.state.tabsViewRoutes[key].name) !== -1) {
            router.push({ name: navTabs.state.tabsViewRoutes[key].name })
            return true
        }
    }
    router.go(0)
}

/**
 * 处理后台的路由
 */
export const handleAdminRoute = (routes: any) => {
    const viewsComponent = import.meta.globEager('/src/views/backend/**/*.vue')
    addRouteAll(viewsComponent, routes, adminBaseRoute.name as string)
    return handleMenuRule(routes, '/' + (adminBaseRoute.name as string) + '/')
}

/**
 * 获取后台菜单的paths
 */
export const getMenuPaths = (menus: any): any[] => {
    let menuPaths = []
    for (const key in menus) {
        if (menus[key].extend == 'add_rules_only') {
            continue
        }
        menuPaths.push(menus[key].path)
        if (menus[key].children && menus[key].children.length > 0) {
            menuPaths = menuPaths.concat(getMenuPaths(menus[key].children))
        }
    }
    return menuPaths
}

/**
 * 后台菜单处理
 */
const handleMenuRule = (routes: any, pathPrefix = '/') => {
    let menuRule = []
    for (const key in routes) {
        if (routes[key].extend == 'add_rules_only') {
            continue
        }
        if (routes[key].type == 'menu' || routes[key].type == 'menu_dir') {
            if (routes[key].type == 'menu_dir' && !routes[key].children) {
                continue
            }

            routes[key].type = routes[key].menu_type
            routes[key].keepAlive = routes[key].name
            if (routes[key].type == 'tab') {
                routes[key].path = pathPrefix + routes[key].path
            } else {
                routes[key].path = routes[key].url
            }
            delete routes[key].url
            delete routes[key].menu_type
            if (routes[key].children && routes[key].children.length > 0) {
                routes[key].children = handleMenuRule(routes[key].children, pathPrefix)
            }
            menuRule.push(routes[key])
        }
    }
    return menuRule
}

/**
 * 动态添加路由-带子路由
 */
export const addRouteAll = (viewsComponent: Record<string, { [key: string]: any }>, routes: any, parentName: string) => {
    for (const idx in routes) {
        if (routes[idx].extend == 'add_menu_only') {
            continue
        }
        if (routes[idx].type == 'menu' && routes[idx].menu_type == 'tab' && viewsComponent[routes[idx].component]) {
            addRouteItem(viewsComponent, routes[idx], parentName)
        }

        if (routes[idx].children && routes[idx].children.length > 0) {
            addRouteAll(viewsComponent, routes[idx].children, parentName)
        }
    }
}

/**
 * 动态添加路由
 */
export const addRouteItem = (viewsComponent: Record<string, { [key: string]: any }>, route: any, parentName: string) => {
    if (parentName) {
        router.addRoute(parentName, {
            path: route.path,
            name: route.name,
            component: viewsComponent[route.component].default,
            meta: {
                title: route.title,
            },
        })
    } else {
        router.addRoute({
            path: '/' + route.path,
            name: route.name,
            component: viewsComponent[route.component].default,
            meta: {
                title: route.title,
            },
        })
    }
}
