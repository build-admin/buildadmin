import { defineStore } from 'pinia'
import { RouteRecordRaw } from 'vue-router'
import { SiteConfig } from '/@/stores/interface'

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
        }
    },
    actions: {
        dataFill(state: SiteConfig) {
            this.$state = state
        },
        setHeadNav(headNav: RouteRecordRaw[]) {
            this.headNav = headNav
        },
    },
})
