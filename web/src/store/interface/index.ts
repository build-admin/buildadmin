// 为 build-admin 提供类型化的 State
// 变量名对应含义请在 ./modules/ 里边找
import type { Component } from 'vue'

export interface ConfigStateTypes {
    defaultLang: string
    fallbackLang: string
    langArray: {
        name: string
        value: string
    }[]
    layout: {
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
}

// 管理员信息
export interface AdminInfoStateTypes {
    adminInfo: object
}

export interface viewMenu {
    title: string
    path: string
    type?: string
    icon?: string
    keepAlive?: string
    children?: viewMenu[]
    component?: Component
}

export interface NavTabs {
    activeIndex: number
    activeRoute: viewMenu | null
    tabsView: Array<viewMenu>
    tabFullScreen: Boolean
    tabsViewRoutes: Array<viewMenu>
}

// 顶级类型声明
export interface RootStateTypes {
    config: ConfigStateTypes
    adminInfo: AdminInfoStateTypes
    navTabs: NavTabs
}
