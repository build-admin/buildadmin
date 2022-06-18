import { defineStore } from 'pinia'
import { SiteConfig } from '/@/stores/interface'
import { SITE_CONFIG } from '/@/stores/constant/cacheKey'

export const useSiteConfig = defineStore('siteConfig', {
    state: (): SiteConfig => {
        return {
            site_name: '',
            record_number: '',
            version: '',
        }
    },
    actions: {},
    persist: {
        key: SITE_CONFIG,
    },
})
