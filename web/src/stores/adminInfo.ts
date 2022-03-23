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
