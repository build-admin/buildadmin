import router from '/@/router/index'
import { viewMenu } from '../stores/interface'
import { isNavigationFailure, NavigationFailureType } from 'vue-router'
import { ElNotification } from 'element-plus'
import { useNavTabs } from '../stores/navTabs'
import { useMemberCenter } from '../stores/memberCenter'
import { adminBaseRoute, memberCenterBaseRoute } from '/@/router/static'
import _ from 'lodash'
import { i18n } from '/@/lang/index'

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
                message: i18n.global.t('utils.Navigation failed, the menu type is unrecognized!'),
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
export const routePush = async (name = '', params: anyObj = {}, path = '') => {
    try {
        const failure = await router.push(name ? { name: name, params: params } : { path: path })
        if (isNavigationFailure(failure, NavigationFailureType.aborted)) {
            ElNotification({
                message: i18n.global.t('utils.Navigation failed, navigation guard intercepted!'),
                type: 'error',
            })
        } else if (isNavigationFailure(failure, NavigationFailureType.duplicated)) {
            ElNotification({
                message: i18n.global.t('utils.Navigation failed, it is at the navigation target position!'),
                type: 'warning',
            })
        }
    } catch (error) {
        ElNotification({
            message: i18n.global.t('utils.Navigation failed, invalid route!'),
            type: 'error',
        })
        console.error(error)
    }
}

export const getFirstRoute = (viewRoutes: viewMenu[]): false | viewMenu => {
    const routerPaths = []
    const routers = router.getRoutes()
    for (const key in routers) {
        if (routers[key].path) routerPaths.push(routers[key].path)
    }
    let find: boolean | viewMenu = false
    for (const key in viewRoutes) {
        if (viewRoutes[key].type != 'menu_dir' && routerPaths.indexOf(viewRoutes[key].path) !== -1) {
            return viewRoutes[key]
        } else if (viewRoutes[key].children?.length) {
            find = getFirstRoute(viewRoutes[key].children!)
            if (find) return find
        }
    }
    return find
}

/**
 * 处理会员中心的路由
 */
export const handleMemberCenterRoute = (routes: any) => {
    const viewsComponent = import.meta.globEager('/src/views/frontend/**/*.vue')
    addRouteAll(viewsComponent, routes, memberCenterBaseRoute.name as string)
    const menuMemberCenterBaseRoute = '/' + (memberCenterBaseRoute.name as string) + '/'
    return handleMenuRule(_.cloneDeep(routes), menuMemberCenterBaseRoute, menuMemberCenterBaseRoute)
}

/**
 * 处理后台的路由
 */
export const handleAdminRoute = (routes: any) => {
    const viewsComponent = import.meta.globEager('/src/views/backend/**/*.vue')
    addRouteAll(viewsComponent, routes, adminBaseRoute.name as string)
    const menuAdminBaseRoute = '/' + (adminBaseRoute.name as string) + '/'
    return handleMenuRule(_.cloneDeep(routes), menuAdminBaseRoute, menuAdminBaseRoute)
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
const handleMenuRule = (routes: any, pathPrefix = '/', parent = '/', module = 'admin') => {
    const menuRule = []
    const authNode = []
    for (const key in routes) {
        if (routes[key].extend == 'add_rules_only') {
            continue
        }
        if (routes[key].type == 'menu' || routes[key].type == 'menu_dir') {
            if (routes[key].type == 'menu_dir') {
                if (!routes[key].children) {
                    continue
                }
                routes[key].menu_type = 'tab'
            }

            routes[key].type = routes[key].menu_type
            if (routes[key].type == 'tab') {
                routes[key].path = pathPrefix + routes[key].path
            } else {
                routes[key].path = routes[key].url
            }
            delete routes[key].url
            delete routes[key].menu_type
            if (routes[key].children && routes[key].children.length > 0) {
                routes[key].children = handleMenuRule(routes[key].children, pathPrefix, routes[key].path)
            }
            menuRule.push(routes[key])
        } else {
            // 权限节点
            authNode.push(pathPrefix + routes[key].name)
        }
    }
    if (authNode.length) {
        if (module == 'admin') {
            const navTabs = useNavTabs()
            navTabs.setAuthNode(parent, authNode)
        } else if (module == 'user') {
            const memberCenter = useMemberCenter()
            memberCenter.setAuthNode(parent, authNode)
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
