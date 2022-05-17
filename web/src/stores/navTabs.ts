import { reactive } from 'vue'
import { defineStore } from 'pinia'
import { STORE_TAB_VIEW_CONFIG } from '/@/stores/constant/cacheKey'
import { viewMenu, NavTabs } from '/@/stores/interface/index'
import { RouteLocationNormalized } from 'vue-router'

export const useNavTabs = defineStore(
    'navTabs',
    () => {
        const state: NavTabs = reactive({
            // 激活tab的index
            activeIndex: 0,
            // 激活的tab
            activeRoute: null,
            // tab列表
            tabsView: [],
            // 当前tab是否全屏
            tabFullScreen: false,
            // 从后台加载到的菜单路由列表
            tabsViewRoutes: [],
            // 按钮权限节点
            authNode: new Map(),
        })

        function addTab(route: RouteLocationNormalized | viewMenu) {
            for (const key in state.tabsView) {
                if (state.tabsView[key].path === route.path) {
                    state.tabsView[key].params = route.params ?? {}
                    state.tabsView[key].query = route.query ?? {}
                    return
                }
            }

            const currentRoute = findMenu(state.tabsViewRoutes, route.path)
            if (!currentRoute) return
            currentRoute!.params = route.params ?? {}
            currentRoute!.query = route.query ?? {}
            state.tabsView.push(
                Object.assign({}, currentRoute, {
                    title: currentRoute.title || 'pagesTitle.noTitle',
                })
            )
        }

        function closeTab(route: viewMenu) {
            state.tabsView.map((v, k) => {
                if (v.path == route.path) {
                    state.tabsView.splice(k, 1)
                }
            })
        }

        // 关闭多个标签, retainMenu 代表需要保留的标签,否则关闭全部标签
        const closeTabs = (retainMenu: viewMenu | false = false): void => {
            if (retainMenu) {
                state.tabsView = [retainMenu]
            } else {
                state.tabsView = []
            }
        }

        const setActiveRoute = (route: RouteLocationNormalized | viewMenu): void => {
            const currentRoute = findMenu(state.tabsViewRoutes, route.path)
            if (!currentRoute) return
            const currentRouteIndex: number = state.tabsView.findIndex((item: viewMenu) => {
                return item.path === route.path
            })
            if (currentRouteIndex === -1) return
            state.activeRoute = currentRoute
            state.activeIndex = currentRouteIndex
        }

        const setTabsViewRoutes = (data: viewMenu[]): void => {
            state.tabsViewRoutes = encodeRoutesURI(data)
        }

        const setAuthNode = (key: string, data: string[]) => {
            state.authNode.set(key, data)
        }

        const setFullScreen = (fullScreen: boolean): void => {
            state.tabFullScreen = fullScreen
        }

        return { state, addTab, closeTab, closeTabs, setActiveRoute, setTabsViewRoutes, setAuthNode, setFullScreen }
    },
    {
        persist: {
            key: STORE_TAB_VIEW_CONFIG,
            paths: ['tabFullScreen'],
        },
    }
)

// 在菜单集合中递归查找 path 的数据
export function findMenu(tabsViewRoutes: viewMenu[], path: string): viewMenu | undefined {
    for (const key in tabsViewRoutes) {
        if (tabsViewRoutes[key].path == path) {
            return tabsViewRoutes[key]
        } else if (tabsViewRoutes[key].children) {
            const done = findMenu(tabsViewRoutes[key].children as viewMenu[], path)
            if (done) return done
        }
    }
}

// 对iframe的url进行编码
function encodeRoutesURI(data: viewMenu[]): viewMenu[] {
    for (const key in data) {
        if (data[key].type == 'iframe') {
            data[key].path = '/admin/iframe/' + encodeURIComponent(data[key].path)
        }

        if (data[key].children) {
            data[key].children = encodeRoutesURI(data[key].children as viewMenu[])
        }
    }
    return data
}
