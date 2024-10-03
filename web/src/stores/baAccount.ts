import { defineStore } from 'pinia'
import router from '../router'
import { postLogout } from '/@/api/backend/module'
import { BA_ACCOUNT } from '/@/stores/constant/cacheKey'
import type { UserInfo } from '/@/stores/interface'
import { Local } from '/@/utils/storage'

export const useBaAccount = defineStore('baAccount', {
    state: (): Partial<UserInfo> => {
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
            motto: '',
            token: '',
            refresh_token: '',
        }
    },
    actions: {
        dataFill(state: UserInfo) {
            this.$state = state
        },
        removeToken() {
            this.token = ''
            this.refresh_token = ''
        },
        getGenderIcon() {
            let icon = { name: 'fa fa-transgender-alt', color: 'var(--el-text-color-secondary)' }
            switch (this.gender) {
                case 1:
                    icon = { name: 'fa fa-mars-stroke-v', color: 'var(--el-color-primary)' }
                    break
                case 2:
                    icon = { name: 'fa fa-mars-stroke', color: 'var(--el-color-danger)' }
                    break
            }
            return icon
        },
        setToken(token: string, type: 'auth' | 'refresh') {
            const field = type == 'auth' ? 'token' : 'refresh_token'
            this[field] = token
        },
        getToken(type: 'auth' | 'refresh' = 'auth') {
            return type === 'auth' ? this.token : this.refresh_token
        },
        logout() {
            postLogout().then((res) => {
                if (res.code == 1) {
                    Local.remove(BA_ACCOUNT)
                    router.go(0)
                }
            })
        },
    },
    persist: {
        key: BA_ACCOUNT,
    },
})
