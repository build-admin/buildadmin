import { reactive } from 'vue'
import { defineStore } from 'pinia'
import { viewMenu, MemberCenter } from '/@/stores/interface/index'

export const useMemberCenter = defineStore('memberCenter', () => {
    const state: MemberCenter = reactive({
        // 当前激活菜单
        activeRoute: null,
        // 从后台加载到的菜单
        viewRoutes: [],
        // 是否显示一级菜单标题(当有多个一级菜单分组时显示)
        showHeadline: false,
    })

    return { state }
})
