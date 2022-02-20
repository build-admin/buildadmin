import type { App } from 'vue'
import { createI18n } from 'vue-i18n'
import { store } from '/@/store/index'

/*
 * 默认只引入 element-plus 的中英文语言包
 * 其他语言包请自行在此 import,并添加到 assignLocale 内
 * 动态 import 只支持相对路径，所以无法按需 import element-plus 的语言包
 * 但i18n的 messages 内是按需载入的
 */
import elementZhcnLocale from 'element-plus/lib/locale/lang/zh-cn'
import elementEnLocale from 'element-plus/lib/locale/lang/en'

interface assignLocale {
    [key: string]: any
}
// 准备要合并的语言包
const assignLocale: assignLocale = {
    'zh-cn': [elementZhcnLocale],
    en: [elementEnLocale],
}

export async function loadLang(app: App) {
    const locale = store.state.config.defaultLang

    // 加载框架语言包
    const lang = await import(`./frame/${locale}.ts`)
    const message = lang.default ?? {}

    /*
     * 0、加载页面语言包 import.meta.globEager 的路径不能使用变量
     * 1、vue3 setup 内只能使用 useI18n({messages:{}}) 来动态载入当前页面单独的语言包，不方便使用
     * 2、直接载入所有 /@/lang/pages/语言/*.ts 文件，若某页面有特别大量的语言配置，可在其他位置单独建立语言包文件，并在对应页面赖加载语言包
     */
    if (locale == 'zh-cn') {
        assignLocale[locale].push(getLangFileMessage(import.meta.globEager('./pages/zh-cn/*.ts')))
    } else if (locale == 'en') {
        assignLocale[locale].push(getLangFileMessage(import.meta.globEager('./pages/en/*.ts')))
    }

    const messages = {
        [locale]: {
            ...message,
        },
    }

    // 合并语言包(含element-puls、页面语言包)
    Object.assign(messages[locale], ...assignLocale[locale])

    const i18n = createI18n({
        locale: locale,
        legacy: false, // 组合式api
        globalInjection: true, // 挂载$t,$d等到全局
        fallbackLocale: store.state.config.fallbackLang,
        messages,
    })

    app.use(i18n)
    return i18n
}

function getLangFileMessage(mList: any) {
    interface msg {
        [key: string]: any
    }
    let msg: msg = {}
    for (let path in mList) {
        if (mList[path].default) {
            //  获取文件名
            let pathName = path.slice(path.lastIndexOf('/') + 1, path.lastIndexOf('.'))
            msg[pathName] = mList[path].default
        }
    }
    return msg
}

export function editDefaultLang(lang: string): void {
    store.commit('config/setAndCache', {
        name: 'defaultLang',
        value: lang,
    })

    /*
     * 语言包是按需加载的,比如默认语言为中文,则只在app实例内加载了中文语言包
     * 查阅文档无数遍,无耐接受当前的 i18n 版本并不支持动态添加语言包(或需要在 setup 内动态添加,无法满足全局替换的需求)
     * 故 reload;如果您有已经实现的无需一次性加载全部语言包且无需 reload 的方案,请一定@我
     */
    location.reload()
}
