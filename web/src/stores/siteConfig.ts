import { defineStore } from 'pinia'
import type { RouteRecordRaw } from 'vue-router'
import type { SiteConfig } from '/@/stores/interface'

export const useSiteConfig = defineStore('siteConfig', {
    state: (): SiteConfig => {
        return {
            siteName: '',
            recordNumber: '',
            version: '',
            cdnUrl: '',
            apiUrl: '',
            upload: {
                mode: 'local',
                maxsize: 0,
                mimetype: '',
                savename: '',
            },
            headNav: [],
            initialize: false,
            userInitialize: false,
        }
    },
    actions: {
        dataFill(state: SiteConfig) {
            this.$state = state
        },
        setHeadNav(headNav: RouteRecordRaw[]) {
            this.headNav = headNav
        },
        setInitialize(initialize: boolean) {
            this.initialize = initialize
        },
        setUserInitialize(userInitialize: boolean) {
            this.userInitialize = userInitialize
        },
    },
})
