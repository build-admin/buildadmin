import { nextTick } from 'vue'
import type { App } from 'vue'
import * as elIcons from '@element-plus/icons-vue'
import router from '/@/router/index'
import Icon from '/@/components/icon/index.vue'
import { useNavTabs } from '/@/stores/navTabs'
import { useMemberCenter } from '/@/stores/memberCenter'
import { ElForm } from 'element-plus'
import { useSiteConfig } from '../stores/siteConfig'
import { i18n } from '../lang'
import { getUrl } from './axios'

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
    const link = document.createElement('script')
    link.src = url
    document.body.appendChild(link)
}

/**
 * 设置浏览器标题
 */
export function setTitleFromRoute(t: any = null) {
    const navTabs = useNavTabs()
    const memberCenter = useMemberCenter()
    nextTick(() => {
        let webTitle = ''
        if (navTabs.state.activeRoute) {
            webTitle = navTabs.state.activeRoute.title
        } else if (memberCenter.state.activeRoute) {
            webTitle = router.currentRoute.value.meta.title as string
        } else {
            webTitle =
                t && router.currentRoute.value.meta.title ? t(router.currentRoute.value.meta.title) : (router.currentRoute.value.meta.title as string)
        }
        document.title = `${webTitle}`
    })
}

export function setTitle(title: string) {
    document.title = `${title}`
}

/**
 * 是否是外部链接
 * @param {string} path
 * @return {Boolean}
 */
export function isExternal(path: string) {
    return /^(https?|ftp|mailto|tel):/.test(path)
}

/**
 * 防抖
 * @param fn 执行函数
 * @param ms 间隔毫秒数
 */
export const debounce = (fn: Function, ms: number) => {
    return (...args: any[]) => {
        if (window.lazy) {
            clearTimeout(window.lazy)
        }
        window.lazy = setTimeout(() => {
            fn(...args)
        }, ms)
    }
}

/**
 * 根据pk字段的值从数组中获取key
 * @param arr
 * @param pk
 * @param value
 */
export const getArrayKey = (arr: any, pk: string, value: string): any => {
    for (const key in arr) {
        if (arr[key][pk] == value) {
            return key
        }
    }
    return false
}

/**
 * 表单重置
 * @param formEl
 */
export const onResetForm = (formEl: InstanceType<typeof ElForm> | undefined) => {
    if (!formEl) return
    formEl.resetFields()
}

/**
 * 将数据构建为ElTree的data {label:'', children: []}
 * @param data
 */
export const buildJsonToElTreeData = (data: any): ElTreeData[] => {
    if (typeof data == 'object') {
        const childrens = []
        for (const key in data) {
            childrens.push({
                label: key + ': ' + data[key],
                children: buildJsonToElTreeData(data[key]),
            })
        }
        return childrens
    } else {
        return []
    }
}

/**
 * 是否在后台应用内
 */
export const isAdminApp = () => {
    if (/^\/admin/.test(router.currentRoute.value.fullPath) || window.location.hash.indexOf('#/admin') === 0) {
        return true
    }
    return false
}

/**
 * 从一个文件路径中获取文件名
 * @param path 文件路径
 */
export const getFileNameFromPath = (path: string) => {
    const paths = path.split('/')
    return paths[paths.length - 1]
}

/**
 * 页面按钮鉴权
 * @param name
 */
export const auth = (name: string) => {
    const navTabs = useNavTabs()
    if (navTabs.state.authNode.has(router.currentRoute.value.path)) {
        if (navTabs.state.authNode.get(router.currentRoute.value.path)!.some((v: string) => v == router.currentRoute.value.path + '/' + name)) {
            return true
        }
    }
    return false
}

/**
 * 获取资源完整地址
 * @param relativeUrl 资源相对地址
 * @param domain 指定域名
 */
export const fullUrl = (relativeUrl: string, domain = '') => {
    const siteConfig = useSiteConfig()
    if (!domain) {
        domain = siteConfig.cdn_url ? siteConfig.cdn_url : getUrl()
    }
    if (!relativeUrl) return domain

    const regUrl = new RegExp(/^http(s)?:\/\//)
    const regexImg = new RegExp(/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i)
    if (!domain || regUrl.test(relativeUrl) || regexImg.test(relativeUrl)) {
        return relativeUrl
    }
    return domain + relativeUrl
}

export const arrayFullUrl = (relativeUrls: string | string[], domain = '') => {
    if (typeof relativeUrls === 'string') {
        relativeUrls = relativeUrls == '' ? [] : relativeUrls.split(',')
    }
    for (const key in relativeUrls) {
        relativeUrls[key] = fullUrl(relativeUrls[key], domain)
    }
    return relativeUrls
}

export const getGreet = () => {
    const now = new Date()
    const hour = now.getHours()
    let greet = ''

    if (hour < 5) {
        greet = i18n.global.t('utils.Late at night, pay attention to your body!')
    } else if (hour < 9) {
        greet = i18n.global.t('utils.good morning!') + i18n.global.t('utils.welcome back')
    } else if (hour < 12) {
        greet = i18n.global.t('utils.Good morning!') + i18n.global.t('utils.welcome back')
    } else if (hour < 14) {
        greet = i18n.global.t('utils.Good noon!') + i18n.global.t('utils.welcome back')
    } else if (hour < 18) {
        greet = i18n.global.t('utils.good afternoon') + i18n.global.t('utils.welcome back')
    } else if (hour < 24) {
        greet = i18n.global.t('utils.Good evening') + i18n.global.t('utils.welcome back')
    } else {
        greet = i18n.global.t('utils.Hello!') + i18n.global.t('utils.welcome back')
    }
    return greet
}
