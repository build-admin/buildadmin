import { defineStore } from 'pinia'
import { ADMIN_INFO } from '/@/stores/constant/cacheKey'
import { AdminInfo } from '/@/stores/interface'

export const useAdminInfo = defineStore('adminInfo', {
    state: (): AdminInfo => {
        return {
            id: 0,
            username: '',
            nickname: '',
            avatar: '',
            lastlogintime: '',
            token: '',
            refreshToken: '',
            // 是否是superAdmin，用于判定是否显示终端按钮等，不做任何权限判断
            super: false,
        }
    },
    actions: {
        removeToken() {
            this.token = ''
            this.refreshToken = ''
        },
    },
    persist: {
        key: ADMIN_INFO,
    },
})
