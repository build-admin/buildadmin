import { defineStore } from 'pinia'
import { reactive } from 'vue'
import type { RouteLocationNormalized, RouteRecordRaw } from 'vue-router'
import type { MemberCenter } from '/@/stores/interface/index'

export const useMemberCenter = defineStore('memberCenter', () => {
    const state: MemberCenter = reactive({
        open: false,
        layoutMode: 'Default',
        activeRoute: null,
        viewRoutes: [],
        showHeadline: false,
        authNode: new Map(),
        shrink: false,
        menuExpand: false,
        navUserMenus: [],
    })

    const setNavUserMenus = (menus: RouteRecordRaw[]) => {
        state.navUserMenus = menus
    }

    const mergeNavUserMenus = (menus: RouteRecordRaw[]) => {
        state.navUserMenus = [...state.navUserMenus, ...menus]
    }

    const setAuthNode = (key: string, data: string[]) => {
        state.authNode.set(key, data)
    }

    const mergeAuthNode = (authNode: Map<string, string[]>) => {
        state.authNode = new Map([...state.authNode, ...authNode])
    }

    const setViewRoutes = (data: RouteRecordRaw[]): void => {
        state.viewRoutes = encodeRoutesURI(data)
    }

    const setShowHeadline = (show: boolean): void => {
        state.showHeadline = show
    }

    const setActiveRoute = (route: RouteLocationNormalized | RouteRecordRaw) => {
        state.activeRoute = route
    }

    const setShrink = (shrink: boolean) => {
        state.shrink = shrink
    }

    const setStatus = (status: boolean) => {
        state.open = status
    }

    const setLayoutMode = (mode: string) => {
        state.layoutMode = mode
    }

    const toggleMenuExpand = (expand = !state.menuExpand) => {
        state.menuExpand = expand
    }

    return {
        state,
        setNavUserMenus,
        mergeNavUserMenus,
        setAuthNode,
        mergeAuthNode,
        setViewRoutes,
        setShowHeadline,
        setActiveRoute,
        setShrink,
        setStatus,
        setLayoutMode,
        toggleMenuExpand,
    }
})

function encodeRoutesURI(data: RouteRecordRaw[]) {
    data.forEach((item) => {
        if (item.meta?.menu_type == 'iframe') {
            item.path = '/user/iframe/' + encodeURIComponent(item.path)
        }

        if (item.children && item.children.length) {
            item.children = encodeRoutesURI(item.children)
        }
    })
    return data
}
