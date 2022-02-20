import { Module } from 'vuex'
import { viewMenu, NavTabs, RootStateTypes } from '/@/store/interface/index'
import { Local } from '/@/utils/storage'
import { TAB_VIEW_CONFIG } from '/@/store/constant/cacheKey'

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

const tabViewConfig = Local.get(TAB_VIEW_CONFIG) || {}

const NavTabsModule: Module<NavTabs, RootStateTypes> = {
    namespaced: true,
    state: {
        // 激活tab的index
        activeIndex: 0,
        // 激活的tab
        activeRoute: null,
        // tab列表
        tabsView: [],
        // 当前tab是否全屏
        tabFullScreen: typeof tabViewConfig['tabFullScreen'] != 'undefined' ? tabViewConfig['tabFullScreen'] : false,
        // 从后台加载到的菜单路由列表
        tabsViewRoutes: [],
    },
    mutations: {
        // 添加tab
        addTab(state, path: string): void {
            if (state.tabsView.some((v) => v.path === path)) return
            const currentRoute = findMenu(state.tabsViewRoutes, path)
            if (!currentRoute) return
            state.tabsView.push(
                Object.assign({}, currentRoute, {
                    title: currentRoute.title || 'pagesTitle.noTitle',
                })
            )
        },
        closeTab(state, route: viewMenu): void {
            state.tabsView.map((v, k) => {
                if (v.path == route.path) {
                    state.tabsView.splice(k, 1)
                }
            })
        },
        // 关闭多个标签, retainMenu 代表需要保留的标签,否则关闭全部标签
        closeTabs(state, retainMenu: viewMenu | undefined): void {
            if (retainMenu) {
                state.tabsView = [retainMenu]
            } else {
                state.tabsView = []
            }
        },
        setActiveRoute(state, path: string): void {
            const currentRoute = findMenu(state.tabsViewRoutes, path)
            if (!currentRoute) return
            const currentRouteIndex: number = state.tabsView.findIndex((route: viewMenu) => {
                return route.path === path
            })
            if (!currentRoute) return
            state.activeRoute = currentRoute
            state.activeIndex = currentRouteIndex
        },
        setTabsViewRoutes(state, data: viewMenu[]): void {
            state.tabsViewRoutes = data
        },
        setFullScreen(state, fullScreen: boolean): void {
            let tabViewConfig = Local.get(TAB_VIEW_CONFIG) || {}
            state.tabFullScreen = tabViewConfig['tabFullScreen'] = fullScreen
            Local.set(TAB_VIEW_CONFIG, tabViewConfig)
        },
    },
    actions: {
        setTabsViewRoutes({ commit }, data: viewMenu[]) {
            commit('setTabsViewRoutes', encodeRoutesURI(data))
        },
    },
}

export default NavTabsModule
