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

export interface AdminInfo {
    id: number
    username: string
    nickname: string
    avatar: string
    lastlogintime: string
    token: string
    refreshToken: string
}
