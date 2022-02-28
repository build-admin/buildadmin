import { nextTick } from 'vue'
import type { App } from 'vue'
import * as elIcons from '@element-plus/icons-vue'
import router from '/@/router/index'
import Icon from '/@/components/icon/index.vue'
import { store } from '/@/store'
import { Local } from '/@/utils/storage'
import { ADMIN_TOKEN } from '/@/store/constant/cacheKey'
import { adminBaseRoute } from '/@/router/static'

export function registerIcons(app: App) {
    /*
     * 全局注册 Icon
     * 使用方式: <Icon name="name" size="size" color="color" />
     * 详见<待完善>
     */
    app.component('Icon', Icon)

    /*
     * 全局注册element Plus的icon
     */
    const icons = elIcons as any
    for (const i in icons) {
        app.component(`el-icon-${icons[i].name}`, icons[i])
    }
}

/* 加载网络css文件 */
export function loadCss(url: string): void {
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = url
    link.crossOrigin = 'anonymous'
    document.getElementsByTagName('head')[0].appendChild(link)
}

/* 加载网络js文件 */
export function loadJs(url: string): void {
    let link = document.createElement('script')
    link.src = url
    document.body.appendChild(link)
}

/**
 * 设置浏览器标题
 */
export function setTitle(t: any = null) {
    nextTick(() => {
        var webTitle: string = ''
        if (store.state.navTabs.activeRoute) {
            webTitle = store.state.navTabs.activeRoute.title
        } else {
            webTitle =
                t && router.currentRoute.value.meta.title ? t(router.currentRoute.value.meta.title) : (router.currentRoute.value.meta.title as string)
        }
        document.title = `${webTitle}`
    })
}

/**
 * @param {string} path
 * @return {Boolean}
 */
export function isExternal(path: string) {
    return /^(https?|ftp|mailto|tel):/.test(path)
}

export function randomNum(min: number, max: number) {
    switch (arguments.length) {
        case 1:
            return parseInt((Math.random() * min + 1).toString(), 10)
            break
        case 2:
            return parseInt((Math.random() * (max - min + 1) + min).toString(), 10)
            break
        default:
            return 0
            break
    }
}

/**
 * 生成随机位数的随机字符串
 */
export function randomStr() {
    return new Date().getTime().toString() + randomNum(1, 50000).toString()
}

export function setAdminToken(value: string) {
    Local.set(ADMIN_TOKEN, value)
}

export function getAdminToken() {
    return Local.get(ADMIN_TOKEN) || ''
}

export const pageTitle = (name: string): string => {
    return `pagesTitle.${name}`
}

/*
 * 处理后台的路由
 */
export const handleAdminRoute = (routes: any) => {
    const viewsComponent = import.meta.globEager('/src/views/backend/**/*.vue')
    addRouteAll(viewsComponent, routes, adminBaseRoute.name as string)
    return handleMenuRule(routes, '/' + (adminBaseRoute.name as string) + '/')
}

const handleMenuRule = (routes: any, pathPrefix = '/') => {
    let menuRule = []
    for (const key in routes) {
        if (routes[key].extend == 'add_rules_only') {
            continue
        }
        if (routes[key].type == 'menu' || routes[key].type == 'menu_dir') {
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

export const addRouteAll = (viewsComponent: Record<string, { [key: string]: any }>, routes: any, parentName: string) => {
    for (const idx in routes) {
        if (routes[idx].extend == 'add_menu_only') {
            continue
        }
        if (routes[idx].type == 'menu' && routes[idx].menu_type == 'tab' && viewsComponent[routes[idx].component].default) {
            addRouteItem(viewsComponent, routes[idx], parentName)
        }

        if (routes[idx].children && routes[idx].children.length > 0) {
            addRouteAll(viewsComponent, routes[idx].children, parentName)
        }
    }
}

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
