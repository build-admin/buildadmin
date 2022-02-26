import { nextTick } from 'vue'
import type { App } from 'vue'
import * as elIcons from '@element-plus/icons-vue'
import router from '/@/router/index'
import Icon from '/@/components/icon/index.vue'
import { store } from '/@/store'
import { Local } from '/@/utils/storage'
import { ADMIN_TOKEN } from '/@/store/constant/cacheKey'

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
            webTitle = t ? t(router.currentRoute.value.meta.title) : (router.currentRoute.value.meta.title as string)
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
