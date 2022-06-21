import { defineStore } from 'pinia'
import { USER_INFO } from '/@/stores/constant/cacheKey'
import { UserInfo } from '/@/stores/interface'

export const useUserInfo = defineStore('userInfo', {
    state: (): UserInfo => {
        return {
            id: 0,
            username: '',
            nickname: '',
            email: '',
            mobile: '',
            avatar: '',
            gender: 0,
            birthday: '',
            money: 0,
            score: 0,
            lastlogintime: '',
            lastloginip: '',
            jointime: '',
            motto: '',
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
