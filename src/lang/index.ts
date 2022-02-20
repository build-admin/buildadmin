import { createI18n } from 'vue-i18n'

import langZhcn from '/@/lang/zh-cn'
import langEn from '/@/lang/en'

const messages = {
    'zh-cn': {
        ...langZhcn,
    },
    en: {
        ...langEn,
    },
}

export const i18n = createI18n({
    locale: 'zh-cn',
    legacy: false, // 组合式api
    fallbackLocale: 'en',
    messages,
})
