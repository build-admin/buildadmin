import { reactive } from 'vue'
import { defineStore } from 'pinia'
import { viewMenu, MemberCenter } from '/@/stores/interface/index'
import { clickMenu } from '/@/utils/router'

export const useMemberCenter = defineStore('memberCenter', () => {
    const state: MemberCenter = reactive({
        // 布局模式
        layoutMode: 'Default',
        // 当前激活菜单
        activeRoute: null,
        // 从后台加载到的菜单
        viewRoutes: [],
        // 是否显示一级菜单标题(当有多个一级菜单分组时显示)
        showHeadline: false,
        // 权限节点
        authNode: new Map(),
    })

    const setAuthNode = (key: string, data: string[]) => {
        state.authNode.set(key, data)
    }

    const setViewRoutes = (data: viewMenu[]): void => {
        state.viewRoutes = encodeRoutesURI(data)
    }

    const setShowHeadline = (show: boolean): void => {
        state.showHeadline = show
    }

    const activateMenu = (menu: viewMenu) => {
        state.activeRoute = menu
        clickMenu(menu)
    }

    return { state, setAuthNode, setViewRoutes, setShowHeadline, activateMenu }
})

function encodeRoutesURI(data: viewMenu[]): viewMenu[] {
    for (const key in data) {
        if (data[key].type == 'iframe') {
            data[key].path = '/user/iframe/' + encodeURIComponent(data[key].path)
        }

        if (data[key].children) {
            data[key].children = encodeRoutesURI(data[key].children as viewMenu[])
        }
    }
    return data
}
