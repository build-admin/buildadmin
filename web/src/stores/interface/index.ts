import type { RouteLocationNormalized, RouteRecordRaw } from 'vue-router'

export interface Layout {
    /* 全局 - s */
    // 是否显示布局配置抽屉
    showDrawer: boolean
    // 是否收缩布局（小屏设备）
    shrink: boolean
    // 后台布局方式，可选值<Default|Classic|Streamline|Double>
    layoutMode: string
    // 后台主页面切换动画，可选值<slide-right|slide-left|el-fade-in-linear|el-fade-in|el-zoom-in-center|el-zoom-in-top|el-zoom-in-bottom>
    mainAnimation: string
    // 是否暗黑模式
    isDark: boolean
    /* 全局 - e */

    /* 侧边栏 - s */
    // 侧边菜单宽度（展开时），单位px
    menuWidth: number
    // 侧边菜单项默认图标
    menuDefaultIcon: string
    // 是否水平折叠收起菜单
    menuCollapse: boolean
    // 是否只保持一个子菜单的展开（手风琴）
    menuUniqueOpened: boolean
    // 显示菜单栏顶栏（LOGO）
    menuShowTopBar: boolean
    // 侧边菜单背景色
    menuBackground: string[]
    // 侧边菜单文字颜色
    menuColor: string[]
    // 侧边菜单激活项背景色
    menuActiveBackground: string[]
    // 侧边菜单激活项文字色
    menuActiveColor: string[]
    // 侧边菜单顶栏背景色
    menuTopBarBackground: string[]
    /* 侧边栏 - e */

    /* 顶栏 - s */
    // 顶栏文字色
    headerBarTabColor: string[]
    // 顶栏背景色
    headerBarBackground: string[]
    // 顶栏悬停时背景色
    headerBarHoverBackground: string[]
    // 顶栏激活项背景色
    headerBarTabActiveBackground: string[]
    // 顶栏激活项文字色
    headerBarTabActiveColor: string[]
    /* 顶栏 - e */
}

export interface Lang {
    // 默认语言，可选值<zh-cn|en>
    defaultLang: string
    // 当在默认语言包找不到翻译时，继续在 fallbackLang 语言包内查找翻译
    fallbackLang: string
    // 支持的语言列表
    langArray: { name: string; value: string }[]
}

export interface NavTabs {
    // 激活 tab 的 index
    activeIndex: number
    // 激活的 tab
    activeRoute: RouteLocationNormalized | null
    // tab 列表
    tabsView: RouteLocationNormalized[]
    // 当前 tab 是否全屏
    tabFullScreen: boolean
    // 从后台加载到的菜单路由列表
    tabsViewRoutes: RouteRecordRaw[]
    // 权限节点
    authNode: Map<string, string[]>
}

export interface MemberCenter {
    // 是否开启会员中心
    open: boolean
    // 布局模式
    layoutMode: string
    // 当前激活菜单
    activeRoute: RouteRecordRaw | RouteLocationNormalized | null
    // 从后台加载到的菜单
    viewRoutes: RouteRecordRaw[]
    // 是否显示一级菜单标题（当有多个一级菜单分组时显示）
    showHeadline: boolean
    // 权限节点
    authNode: Map<string, string[]>
    // 收缩布局（小屏设备）
    shrink: boolean
    // 菜单展开状态（小屏设备）
    menuExpand: boolean
    // 顶栏会员菜单下拉项
    navUserMenus: RouteRecordRaw[]
}

export interface AdminInfo {
    id: number
    username: string
    nickname: string
    avatar: string
    last_login_time: string
    token: string
    refresh_token: string
    // 是否是 superAdmin，用于判定是否显示终端按钮等，不做任何权限判断
    super: boolean
}

export interface UserInfo {
    id: number
    username: string
    nickname: string
    email: string
    mobile: string
    gender: number
    birthday: string
    money: number
    score: number
    avatar: string
    last_login_time: string
    last_login_ip: string
    join_time: string
    motto: string
    token: string
    refresh_token: string
}

export interface TaskItem {
    // 任务唯一标识
    uuid: string
    // 创建时间
    createTime: string
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
    // 扩展信息，自动发送到后台
    extend: string
    // 执行结果回调
    callback: Function
}

export interface Terminal {
    // 显示终端窗口
    show: boolean
    // 在后台终端按钮上显示一个红点
    showDot: boolean
    // 任务列表
    taskList: TaskItem[]
    // 包管理器
    packageManager: string
    // 显示终端设置窗口
    showConfig: boolean
    // 开始任务时自动清理已完成任务
    automaticCleanupTask: string
    // PHP 开发服务环境
    phpDevelopmentServer: boolean
    // NPM 源
    npmRegistry: string
    // composer 源
    composerRegistry: string
}

export interface SiteConfig {
    siteName: string
    recordNumber?: string
    version: string
    cdnUrl: string
    apiUrl: string
    upload: {
        mode: string
        [key: string]: any
    }
    headNav: RouteRecordRaw[]
    initialize: boolean
    userInitialize: boolean
}
