import { ElNotification } from 'element-plus'
import { compact, isEmpty, reverse } from 'lodash-es'
import type { RouteLocationRaw, RouteRecordRaw } from 'vue-router'
import { isNavigationFailure, NavigationFailureType } from 'vue-router'
import { i18n } from '/@/lang/index'
import router from '/@/router/index'
import adminBaseRoute from '/@/router/static/adminBase'
import memberCenterBaseRoute from '/@/router/static/memberCenterBase'
import { useConfig } from '/@/stores/config'
import { useMemberCenter } from '/@/stores/memberCenter'
import { useNavTabs } from '/@/stores/navTabs'
import { useSiteConfig } from '/@/stores/siteConfig'
import { isAdminApp } from '/@/utils/common'
import { closeShade } from '/@/utils/pageShade'

/**
 * 将传入的地址转换为安全可跳转的 URL
 * 仅允许 http/https 协议，可选的同源限制用于避免开放跳转
 * @param url 待校验的地址
 * @param allowExternal 是否允许站外地址，默认 false 即仅允许同源
 * @returns 安全的绝对地址，校验不通过时返回 null
 */
export const safeURL = (url?: string | null, allowExternal = false): string | null => {
    if (!url) return null

    let target: URL
    try {
        target = new URL(url, location.origin)
    } catch {
        return null
    }

    // 协议白名单，仅允许 http/https
    if (target.protocol !== 'http:' && target.protocol !== 'https:') return null

    // 是否仅允许同源
    if (!allowExternal && target.origin !== location.origin) return null

    return target.href
}

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
        if (routes[key].meta?.type == 'menu' && routerPaths.indexOf(routes[key].path) !== -1) {
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
    switch (menu.meta?.menu_type) {
        case 'iframe':
        case 'tab':
            routePush(menu.path)
            break
        case 'link': {
            const url = safeURL(menu.path, true)
            if (url) {
                window.open(url, '_blank', 'noopener,noreferrer')
            } else {
                ElNotification({
                    message: i18n.global.t('utils.Navigation failed, the menu URL is invalid!'),
                    type: 'error',
                })
            }
            break
        }

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
 * 处理前台的路由
 * @param routes 路由规则
 * @param menus 会员中心菜单路由规则
 */
export const handleFrontendRoute = (routes: any, menus: any) => {
    const siteConfig = useSiteConfig()
    const memberCenter = useMemberCenter()
    const viewsComponent = import.meta.glob('/src/views/frontend/**/*.vue')

    if (routes.length) {
        addRouteAll(viewsComponent, routes, '', true)
        memberCenter.mergeAuthNode(handleAuthNode(routes, '/'))
        siteConfig.setHeadNav(handleMenuRule(routes, '/', ['nav']))
        memberCenter.mergeNavUserMenus(handleMenuRule(routes, '/', ['nav_user_menu']))
    }
    if (menus.length && isEmpty(memberCenter.state.viewRoutes)) {
        addRouteAll(viewsComponent, menus, memberCenterBaseRoute.name as string)
        const menuMemberCenterBaseRoute = (memberCenterBaseRoute.path as string) + '/'
        memberCenter.mergeAuthNode(handleAuthNode(menus, menuMemberCenterBaseRoute))

        memberCenter.mergeNavUserMenus(handleMenuRule(menus, '/', ['nav_user_menu']))
        memberCenter.setShowHeadline(menus.length > 1)
        memberCenter.setViewRoutes(handleMenuRule(menus, menuMemberCenterBaseRoute))
    }
}

/**
 * 处理后台的路由
 */
export const handleAdminRoute = (routes: any) => {
    const viewsComponent = import.meta.glob('/src/views/backend/**/*.vue')
    addRouteAll(viewsComponent, routes, adminBaseRoute.name as string)
    const menuAdminBaseRoute = (adminBaseRoute.path as string) + '/'

    // 更新stores中的路由菜单数据
    const navTabs = useNavTabs()
    navTabs.setTabsViewRoutes(handleMenuRule(routes, menuAdminBaseRoute))
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
 * 获取菜单唯一标识
 * @param menu 菜单数据
 * @param prefix 前缀
 */
export const getMenuKey = (menu: RouteRecordRaw, prefix = '') => {
    if (prefix === '') {
        prefix = menu.path
    }
    return `${prefix}-${menu.name as string}-${menu.meta && menu.meta.id ? menu.meta.id : ''}`
}

/**
 * 会员中心和后台的菜单处理
 */
const handleMenuRule = (routes: any, pathPrefix = '/', type = ['menu', 'menu_dir']) => {
    const menuRule: RouteRecordRaw[] = []
    for (const key in routes) {
        if (routes[key].extend == 'add_rules_only') {
            continue
        }
        if (!type.includes(routes[key].type)) {
            continue
        }
        if (routes[key].type == 'menu_dir' && routes[key].children && !routes[key].children.length) {
            continue
        }
        if (
            ['route', 'menu', 'nav_user_menu', 'nav'].includes(routes[key].type) &&
            ((routes[key].menu_type == 'tab' && !routes[key].component) || (['link', 'iframe'].includes(routes[key].menu_type) && !routes[key].url))
        ) {
            continue
        }
        const currentPath = ['link', 'iframe'].includes(routes[key].menu_type) ? routes[key].url : pathPrefix + routes[key].path
        let children: RouteRecordRaw[] = []
        if (routes[key].children && routes[key].children.length > 0) {
            children = handleMenuRule(routes[key].children, pathPrefix, type)
        }
        menuRule.push({
            path: currentPath,
            name: routes[key].name,
            component: routes[key].component,
            meta: {
                id: routes[key].id,
                title: routes[key].title,
                icon: routes[key].icon,
                keepalive: routes[key].keepalive,
                menu_type: routes[key].menu_type,
                type: routes[key].type,
            },
            children: children,
        })
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
 * @param viewsComponent
 * @param routes
 * @param parentName
 * @param analyticRelation 根据 name 从已注册路由分析父级路由
 */
export const addRouteAll = (viewsComponent: Record<string, any>, routes: any, parentName: string, analyticRelation = false) => {
    for (const idx in routes) {
        if (routes[idx].extend == 'add_menu_only') {
            continue
        }
        if ((routes[idx].menu_type == 'tab' && viewsComponent[routes[idx].component]) || routes[idx].menu_type == 'iframe') {
            addRouteItem(viewsComponent, routes[idx], parentName, analyticRelation)
        }

        if (routes[idx].children && routes[idx].children.length > 0) {
            addRouteAll(viewsComponent, routes[idx].children, parentName, analyticRelation)
        }
    }
}

/**
 * 动态添加路由
 * @param viewsComponent
 * @param route
 * @param parentName
 * @param analyticRelation 根据 name 从已注册路由分析父级路由
 */
export const addRouteItem = (viewsComponent: Record<string, any>, route: any, parentName: string, analyticRelation: boolean) => {
    let path = '',
        component
    if (route.menu_type == 'iframe') {
        path = (isAdminApp() ? adminBaseRoute.path : memberCenterBaseRoute.path) + '/iframe/' + encodeURIComponent(route.url)
        component = () => import('/@/layouts/common/router-view/iframe.vue')
    } else {
        path = parentName ? route.path : '/' + route.path
        component = viewsComponent[route.component]
    }

    if (route.menu_type == 'tab' && analyticRelation) {
        const parentNames = getParentNames(route.name)
        if (parentNames.length) {
            for (const key in parentNames) {
                if (router.hasRoute(parentNames[key])) {
                    parentName = parentNames[key]
                    break
                }
            }
        }
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

/**
 * 根据name字符串，获取父级name组合的数组
 * @param name
 */
const getParentNames = (name: string) => {
    const names = compact(name.split('/'))
    const tempNames = []
    const parentNames = []
    for (const key in names) {
        tempNames.push(names[key])
        if (parseInt(key) != names.length - 1) {
            parentNames.push(tempNames.join('/'))
        }
    }
    return reverse(parentNames)
}
