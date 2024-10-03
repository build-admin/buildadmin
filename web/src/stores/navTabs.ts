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
         * @param path 需要关闭的 tab 的路径
         */
        const closeTabByPath = (path: string) => {
            layoutNavTabsRef.value?.closeTabByPath(path)
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
         * @param path 需要修改标题的 tab 的路径
         * @param title 新的标题
         */
        const updateTabTitle = (path: string, title: string) => {
            layoutNavTabsRef.value?.updateTabTitle(path, title)
        }

        /**
         * 添加 tab（内部）
         * ps: router.push 时可自动完成 tab 添加，无需调用此方法
         */
        function _addTab(route: RouteLocationNormalized) {
            if (!route.meta.addtab) return
            for (const key in state.tabsView) {
                if (state.tabsView[key].path === route.path) {
                    state.tabsView[key].params = !isEmpty(route.params) ? route.params : state.tabsView[key].params
                    state.tabsView[key].query = !isEmpty(route.query) ? route.query : state.tabsView[key].query
                    return
                }
            }
            if (typeof route.meta.title == 'string') {
                route.meta.title = route.meta.title.indexOf('pagesTitle.') === -1 ? route.meta.title : i18n.global.t(route.meta.title)
            }
            state.tabsView.push(route)
        }

        /**
         * 设置激活 tab（内部）
         * ps: router.push 时可自动完成 tab 激活，无需调用此方法
         */
        const _setActiveRoute = (route: RouteLocationNormalized): void => {
            const currentRouteIndex: number = state.tabsView.findIndex((item: RouteLocationNormalized) => {
                return item.path === route.path
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
                if (v.path == route.path) {
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
        const _updateTabTitle = (path: string, title: string) => {
            for (const key in state.tabsView) {
                if (state.tabsView[key].path == path) {
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

        return {
            state,
            closeAllTab,
            closeTabByPath,
            updateTabTitle,
            setTabsViewRoutes,
            setAuthNode,
            fillAuthNode,
            setFullScreen,
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
