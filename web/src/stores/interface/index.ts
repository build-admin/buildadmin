// 变量名对应含义请在 /stores/* 里边找
import type { Component } from 'vue'

export interface Layout {
    showDrawer: boolean
    shrink: boolean
    layoutMode: string
    mainAnimation: string
    menuWidth: number
    menuDefaultIcon: string
    menuCollapse: boolean
    menuUniqueOpened: boolean
    menuShowTopBar: boolean
    menuBackground: string
    menuColor: string
    menuActiveBackground: string
    menuActiveColor: string
    menuTopBarBackground: string
    headerBarTabColor: string
    headerBarBackground: string
    headerBarHoverBackground: string
    headerBarTabActiveBackground: string
    headerBarTabActiveColor: string
}

export interface viewMenu {
    title: string
    path: string
    name?: string
    type?: string
    icon?: string
    keepAlive?: string
    query?: anyObj
    params?: anyObj
    children?: viewMenu[]
    component?: Component
}

export interface NavTabs {
    activeIndex: number
    activeRoute: viewMenu | null
    tabsView: Array<viewMenu>
    tabFullScreen: Boolean
    tabsViewRoutes: Array<viewMenu>
    authNode: Map<string, string[]>
}

export interface AdminInfo {
    id: number
    username: string
    nickname: string
    avatar: string
    lastlogintime: string
    token: string
    refreshToken: string
}

export interface TaskItem {
    // 任务唯一标识
    uuid: string
    // 创建时间
    createtime: string
    // 状态
    status: number
    // 命令
    command: string
    // 命令执行日志
    message: string[]
    // 显示命令执行日志
    showMessage: boolean
    // 失败阻断后续命令执行
    blockOnFailure: boolean
    // 执行结果回调
    callback: Function
}

export interface Terminal {
    show: boolean
    showDot: boolean
    taskList: TaskItem[]
    packageManager: string
    showPackageManagerDialog: boolean
    showConfig: boolean
    automaticCleanupTask: string
    port: string
}

export interface SiteConfig {
    site_name: string
    version: string
}
