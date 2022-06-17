import { defineStore } from 'pinia'
import { USER_INFO } from '/@/stores/constant/cacheKey'
import { UserInfo } from '/@/stores/interface'

export const useUserInfo = defineStore('userInfo', {
    state: (): UserInfo => {
        return {
            id: 0,
            username: '',
            nickname: '',
            avatar: '',
            lastlogintime: '',
            lastloginip: '',
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
        key: USER_INFO,
    },
})
