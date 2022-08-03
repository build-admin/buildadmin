import { defineStore } from 'pinia'
import { SiteConfig } from '/@/stores/interface'

export const useSiteConfig = defineStore('siteConfig', {
    state: (): SiteConfig => {
        return {
            site_name: '',
            record_number: '',
            version: '',
            cdn_url: '',
        }
    },
})
