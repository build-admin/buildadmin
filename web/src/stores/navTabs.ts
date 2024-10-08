import { isEmpty } from 'lodash-es'
import { defineStore } from 'pinia'
import { reactive } from 'vue'
import type { RouteLocationNormalized, RouteRecordRaw } from 'vue-router'
import { i18n } from '../lang'
import { adminBaseRoutePath } from '/@/router/static/adminBase'
import { STORE_TAB_VIEW_CONFIG } from '/@/stores/constant/cacheKey'
import type { NavTabs } from '/@/stores/interface/index'
import { layoutNavTabsRef } from '/@/stores/refs'

export const useNavTabs = defineStore(
    'navTabs',
    () => {
        const state: NavTabs = reactive({
            activeIndex: 0,
            activeRoute: null,
            tabsView: [],
            tabFullScreen: false,
            tabsViewRoutes: [],
            authNode: new Map(),
        })

        /**
         * 通过路由路径关闭tab
         * @param fullPath 需要关闭的 tab 的路径
         */
        const closeTabByPath = (fullPath: string) => {
            layoutNavTabsRef.value?.closeTabByPath(fullPath)
        }

        /**
         * 关闭所有tab
         * @param menu 需要保留的标签，否则关闭全部标签并打开第一个路由
         */
        const closeAllTab = (menu?: RouteLocationNormalized) => {
            layoutNavTabsRef.value?.closeAllTab(menu)
        }

        /**
         * 修改 tab 标题
         * @param fullPath 需要修改标题的 tab 的路径
         * @param title 新的标题
         */
        const updateTabTitle = (fullPath: string, title: string) => {
            layoutNavTabsRef.value?.updateTabTitle(fullPath, title)
        }

        /**
         * 添加 tab（内部）
         * ps: router.push 时可自动完成 tab 添加，无需调用此方法
         */
        function _addTab(route: RouteLocationNormalized) {
            const tabView = { ...route, matched: [], meta: { ...route.meta } }
            if (!tabView.meta.addtab) return

            // 通过路由寻找菜单的原始数据
            const tabViewRoute = getTabsViewDataByRoute(tabView)
            if (tabViewRoute && tabViewRoute.meta) {
                tabView.name = tabViewRoute.name
                tabView.meta.id = tabViewRoute.meta.id
                tabView.meta.title = tabViewRoute.meta.title
            }

            for (const key in state.tabsView) {
                // 菜单已在 tabs 存在，更新 params 和 query
                if (state.tabsView[key].meta.id === tabView.meta.id || state.tabsView[key].fullPath == tabView.fullPath) {
                    state.tabsView[key].fullPath = tabView.fullPath
                    state.tabsView[key].params = !isEmpty(tabView.params) ? tabView.params : state.tabsView[key].params
                    state.tabsView[key].query = !isEmpty(tabView.query) ? tabView.query : state.tabsView[key].query
                    return
                }
            }
            if (typeof tabView.meta.title == 'string') {
                tabView.meta.title = i18n.global.te(tabView.meta.title) ? i18n.global.t(tabView.meta.title) : tabView.meta.title
            }
            state.tabsView.push(tabView)
        }

        /**
         * 设置激活 tab（内部）
         * ps: router.push 时可自动完成 tab 激活，无需调用此方法
         */
        const _setActiveRoute = (route: RouteLocationNormalized): void => {
            const currentRouteIndex: number = state.tabsView.findIndex((item: RouteLocationNormalized) => {
                return item.fullPath === route.fullPath
            })
            if (currentRouteIndex === -1) return
            state.activeRoute = route
            state.activeIndex = currentRouteIndex
        }

        /**
         * 关闭 tab（内部）
         * ps: 使用 closeTabByPath 代替
         */
        function _closeTab(route: RouteLocationNormalized) {
            state.tabsView.map((v, k) => {
                if (v.fullPath == route.fullPath) {
                    state.tabsView.splice(k, 1)
                    return
                }
            })
        }

        /**
         * 关闭多个标签（内部）
         * ps：使用 closeAllTab 代替
         */
        const _closeTabs = (retainMenu: RouteLocationNormalized | false = false) => {
            if (retainMenu) {
                state.tabsView = [retainMenu]
            } else {
                state.tabsView = []
            }
        }

        /**
         * 更新标签标题（内部）
         * ps: 使用 updateTabTitle 代替
         */
        const _updateTabTitle = (fullPath: string, title: string) => {
            for (const key in state.tabsView) {
                if (state.tabsView[key].fullPath == fullPath) {
                    state.tabsView[key].meta.title = title
                    break
                }
            }
        }

        /**
         * 设置从后台加载到的菜单路由列表
         */
        const setTabsViewRoutes = (data: RouteRecordRaw[]): void => {
            state.tabsViewRoutes = encodeRoutesURI(data)
        }

        /**
         * 以key设置权限节点
         */
        const setAuthNode = (key: string, data: string[]) => {
            state.authNode.set(key, data)
        }

        /**
         * 覆盖设置权限节点
         */
        const fillAuthNode = (data: Map<string, string[]>) => {
            state.authNode = data
        }

        /**
         * 设置当前 tab 是否全屏
         * @param status 全屏状态
         */
        const setFullScreen = (status: boolean): void => {
            state.tabFullScreen = status
        }

        /**
         * 寻找路由在菜单中的数据
         * @param route 路由
         * @param returnType 返回值要求:normal=返回被搜索的路径对应的菜单数据,above=返回被搜索的路径对应的上一级菜单数组
         */
        const getTabsViewDataByRoute = (route: RouteLocationNormalized, returnType: 'normal' | 'above' = 'normal'): RouteRecordRaw | false => {
            // 以完整路径寻找
            let found = getTabsViewDataByPath(route.fullPath, state.tabsViewRoutes, returnType)
            if (found) {
                found.meta!.matched = route.fullPath
                return found
            }

            // 以路径寻找
            found = getTabsViewDataByPath(route.path, state.tabsViewRoutes, returnType)
            if (found) {
                found.meta!.matched = route.path
                return found
            }

            return false
        }

        /**
         * 递归的寻找路由路径在菜单中的数据
         * @param path 路由路径
         * @param menus 菜单数据（只有 path 代表完整 url，没有 fullPath）
         * @param returnType 返回值要求:normal=返回被搜索的路径对应的菜单数据,above=返回被搜索的路径对应的上一级菜单数组
         */
        const getTabsViewDataByPath = (path: string, menus: RouteRecordRaw[], returnType: 'normal' | 'above'): RouteRecordRaw | false => {
            for (const key in menus) {
                // 找到目标
                if (menus[key].path === path) {
                    return menus[key]
                }
                // 从子级继续寻找
                if (menus[key].children && menus[key].children.length) {
                    const find = getTabsViewDataByPath(path, menus[key].children, returnType)
                    if (find) {
                        return returnType == 'above' ? menus[key] : find
                    }
                }
            }
            return false
        }

        return {
            state,
            closeAllTab,
            closeTabByPath,
            updateTabTitle,
            setTabsViewRoutes,
            setAuthNode,
            fillAuthNode,
            setFullScreen,
            getTabsViewDataByPath,
            getTabsViewDataByRoute,
            _addTab,
            _closeTab,
            _closeTabs,
            _setActiveRoute,
            _updateTabTitle,
        }
    },
    {
        persist: {
            key: STORE_TAB_VIEW_CONFIG,
            pick: ['state.tabFullScreen'],
        },
    }
)

/**
 * 对iframe的url进行编码
 */
function encodeRoutesURI(data: RouteRecordRaw[]) {
    data.forEach((item) => {
        if (item.meta?.menu_type == 'iframe') {
            item.path = adminBaseRoutePath + '/iframe/' + encodeURIComponent(item.path)
        }

        if (item.children && item.children.length) {
            item.children = encodeRoutesURI(item.children)
        }
    })
    return data
}
