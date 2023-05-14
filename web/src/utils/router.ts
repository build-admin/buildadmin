import router from '/@/router/index'
import { isNavigationFailure, NavigationFailureType, RouteRecordRaw, RouteLocationRaw } from 'vue-router'
import { ElNotification } from 'element-plus'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { useMemberCenter } from '/@/stores/memberCenter'
import { closeShade } from '/@/utils/pageShade'
import { adminBaseRoute, memberCenterBaseRoute } from '/@/router/static'
import { i18n } from '/@/lang/index'
import { isAdminApp } from '/@/utils/common'
import { HeadNav } from '/@/stores/interface'

/**
 * 导航失败有错误消息的路由push
 * @param to — 导航位置，同 router.push
 */
export const routePush = async (to: RouteLocationRaw) => {
    try {
        const failure = await router.push(to)
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

/**
 * 获取第一个菜单
 */
export const getFirstRoute = (routes: RouteRecordRaw[]): false | RouteRecordRaw => {
    const routerPaths: string[] = []
    const routers = router.getRoutes()
    routers.forEach((item) => {
        if (item.path) routerPaths.push(item.path)
    })
    let find: boolean | RouteRecordRaw = false
    for (const key in routes) {
        if (routes[key].meta?.type != 'menu_dir' && routerPaths.indexOf(routes[key].path) !== -1) {
            return routes[key]
        } else if (routes[key].children && routes[key].children?.length) {
            find = getFirstRoute(routes[key].children!)
            if (find) return find
        }
    }
    return find
}

/**
 * 打开侧边菜单
 * @param menu 菜单数据
 */
export const onClickMenu = (menu: RouteRecordRaw) => {
    switch (menu.meta?.type) {
        case 'iframe':
        case 'tab':
            routePush({ path: menu.path })
            break
        case 'link':
            window.open(menu.path, '_blank')
            break

        default:
            ElNotification({
                message: i18n.global.t('utils.Navigation failed, the menu type is unrecognized!'),
                type: 'error',
            })
            break
    }

    const config = useConfig()
    if (config.layout.shrink) {
        closeShade(() => {
            config.setLayout('menuCollapse', true)
        })
    }
}

/**
 * 处理会员中心的路由
 * 会员中心虽然也是前台的路由，但需要动态注册的路由是根据登录会员的权限来的，所以需要单独处理
 */
export const handleMemberCenterRoute = (routes: any) => {
    const viewsComponent = import.meta.glob('/src/views/frontend/**/*.vue')
    addRouteAll(viewsComponent, routes, memberCenterBaseRoute.name as string)
    const menuMemberCenterBaseRoute = '/' + (memberCenterBaseRoute.name as string) + '/'
    const menuRule = handleMenuRule(routes, menuMemberCenterBaseRoute, 'user')

    const memberCenter = useMemberCenter()
    memberCenter.setViewRoutes(menuRule)
    memberCenter.setShowHeadline(routes.length > 1 ? true : false)
    memberCenter.mergeAuthNode(handleAuthNode(routes, menuMemberCenterBaseRoute))
}

/**
 * 处理前台的路由
 */
export const handleFrontendRoute = (routes: any) => {
    const viewsComponent = import.meta.glob('/src/views/frontend/**/*.vue')
    addRouteAll(viewsComponent, routes, '')

    const memberCenter = useMemberCenter()
    memberCenter.mergeAuthNode(handleAuthNode(routes, '/'))
}

/**
 * 处理后台的路由
 */
export const handleAdminRoute = (routes: any) => {
    const viewsComponent = import.meta.glob('/src/views/backend/**/*.vue')
    addRouteAll(viewsComponent, routes, adminBaseRoute.name as string)
    const menuAdminBaseRoute = '/' + (adminBaseRoute.name as string) + '/'
    const menuRule = handleMenuRule(routes, menuAdminBaseRoute, 'admin')

    // 更新stores中的路由菜单数据
    const navTabs = useNavTabs()
    navTabs.setTabsViewRoutes(menuRule)
    navTabs.fillAuthNode(handleAuthNode(routes, menuAdminBaseRoute))
}

/**
 * 获取菜单的paths
 */
export const getMenuPaths = (menus: RouteRecordRaw[]): string[] => {
    let menuPaths: string[] = []
    menus.forEach((item) => {
        menuPaths.push(item.path)
        if (item.children && item.children.length > 0) {
            menuPaths = menuPaths.concat(getMenuPaths(item.children))
        }
    })
    return menuPaths
}

/**
 * 会员中心和后台的菜单处理
 */
const handleMenuRule = (routes: any, pathPrefix = '/', module = 'admin') => {
    const menuRule: RouteRecordRaw[] = []
    for (const key in routes) {
        if (routes[key].extend == 'add_rules_only') {
            continue
        }
        if (routes[key].type == 'menu' || routes[key].type == 'menu_dir') {
            if (routes[key].type == 'menu_dir' && routes[key].children && !routes[key].children.length) {
                continue
            }
            const currentPath = routes[key].menu_type == 'link' || routes[key].menu_type == 'iframe' ? routes[key].url : pathPrefix + routes[key].path
            let children: RouteRecordRaw[] = []
            if (routes[key].children && routes[key].children.length > 0) {
                children = handleMenuRule(routes[key].children, pathPrefix, module)
            }
            menuRule.push({
                path: currentPath,
                name: routes[key].name,
                component: routes[key].component,
                meta: {
                    title: routes[key].title,
                    icon: routes[key].icon,
                    keepalive: routes[key].keepalive,
                    type: routes[key].menu_type,
                },
                children: children,
            })
        }
    }
    return menuRule
}

/**
 * 处理权限节点
 * @param routes 路由数据
 * @param prefix 节点前缀
 * @returns 组装好的权限节点
 */
const handleAuthNode = (routes: any, prefix = '/') => {
    const authNode: Map<string, string[]> = new Map([])
    assembleAuthNode(routes, authNode, prefix, prefix)
    return authNode
}
const assembleAuthNode = (routes: any, authNode: Map<string, string[]>, prefix = '/', parent = '/') => {
    const authNodeTemp = []
    for (const key in routes) {
        if (routes[key].type == 'button') authNodeTemp.push(prefix + routes[key].name)
        if (routes[key].children && routes[key].children.length > 0) {
            assembleAuthNode(routes[key].children, authNode, prefix, prefix + routes[key].name)
        }
    }
    if (authNodeTemp && authNodeTemp.length > 0) {
        authNode.set(parent, authNodeTemp)
    }
}

/**
 * 动态添加路由-带子路由
 */
export const addRouteAll = (viewsComponent: Record<string, any>, routes: any, parentName: string) => {
    for (const idx in routes) {
        if (routes[idx].extend == 'add_menu_only') {
            continue
        }
        if ((routes[idx].menu_type == 'tab' && viewsComponent[routes[idx].component]) || routes[idx].menu_type == 'iframe') {
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
export const addRouteItem = (viewsComponent: Record<string, any>, route: any, parentName: string) => {
    let path = '',
        component
    if (route.menu_type == 'iframe') {
        path = (isAdminApp() ? '/admin' : '/user') + '/iframe/' + encodeURIComponent(route.url)
        component = () => import('/@/layouts/common/router-view/iframe.vue')
    } else {
        path = parentName ? route.path : '/' + route.path
        component = viewsComponent[route.component]
    }
    const routeBaseInfo: RouteRecordRaw = {
        path: path,
        name: route.name,
        component: component,
        meta: {
            title: route.title,
            extend: route.extend,
            icon: route.icon,
            keepalive: route.keepalive,
            menu_type: route.menu_type,
            type: route.type,
            url: route.url,
            addtab: true,
        },
    }
    if (parentName) {
        router.addRoute(parentName, routeBaseInfo)
    } else {
        router.addRoute(routeBaseInfo)
    }
}

export const handleHeadNav = (rules: anyObj, prefix = '/') => {
    const headNav: HeadNav[] = []
    for (const key in rules) {
        let children: HeadNav[] = []
        if (rules[key].children && rules[key].children.length > 0) {
            children = handleHeadNav(rules[key].children, prefix)
        }

        if (rules[key].type == 'nav') {
            if ('link' == rules[key].menu_type) {
                rules[key].path = rules[key].url
            } else if ('iframe' == rules[key].menu_type) {
                rules[key].path = '/user/iframe/' + encodeURIComponent(rules[key].url)
            } else {
                rules[key].path = prefix + rules[key].path
            }
            headNav.push({
                ...rules[key],
                meta: {
                    type: rules[key].menu_type,
                },
                path: rules[key].path,
                children: children,
            })
        }
    }
    return headNav
}
