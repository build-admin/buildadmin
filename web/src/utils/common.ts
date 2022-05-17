import { nextTick } from 'vue'
import type { App } from 'vue'
import * as elIcons from '@element-plus/icons-vue'
import router from '/@/router/index'
import Icon from '/@/components/icon/index.vue'
import { useNavTabs } from '/@/stores/navTabs'
import { ElForm } from 'element-plus'
import { useAdminInfo } from '/@/stores/adminInfo'

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
    const navTabs = useNavTabs()
    nextTick(() => {
        var webTitle: string = ''
        if (navTabs.state.activeRoute) {
            webTitle = navTabs.state.activeRoute.title
        } else {
            webTitle =
                t && router.currentRoute.value.meta.title ? t(router.currentRoute.value.meta.title) : (router.currentRoute.value.meta.title as string)
        }
        document.title = `${webTitle}`
    })
}

/**
 * 是否是外部链接
 * @param {string} path
 * @return {Boolean}
 */
export function isExternal(path: string) {
    return /^(https?|ftp|mailto|tel):/.test(path)
}

export function getAdminToken(type: 'auth' | 'refresh' = 'auth') {
    const adminInfo = useAdminInfo()
    return type == 'auth' ? adminInfo.token : adminInfo.refreshToken
}

export function removeAdminToken() {
    const adminInfo = useAdminInfo()
    adminInfo.removeToken()
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
        let childrens = []
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

export const isAdminApp = () => {
    if (/^\/admin/.test(router.currentRoute.value.fullPath)) {
        return true
    }
    return false
}

export const getFileNameFromPath = (path: string) => {
    let paths = path.split('/')
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
