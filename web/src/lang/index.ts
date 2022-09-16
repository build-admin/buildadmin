import type { App } from 'vue'
import { createI18n } from 'vue-i18n'
import type { I18n } from 'vue-i18n'
import { useConfig } from '/@/stores/config'

/*
 * 默认只引入 element-plus 的中英文语言包
 * 其他语言包请自行在此 import,并添加到 assignLocale 内
 * 动态 import 只支持相对路径，所以无法按需 import element-plus 的语言包
 * 但i18n的 messages 内是按需载入的
 */
import elementZhcnLocale from 'element-plus/lib/locale/lang/zh-cn'
import elementEnLocale from 'element-plus/lib/locale/lang/en'

export let i18n: I18n<{ [x: string]: any }, unknown, unknown, false>

interface assignLocale {
    [key: string]: any
}
// 准备要合并的语言包
const assignLocale: assignLocale = {
    'zh-cn': [elementZhcnLocale],
    en: [elementEnLocale],
}

export async function loadLang(app: App) {
    const config = useConfig()
    const locale = config.lang.defaultLang

    // 加载框架语言包
    const lang = await import(`./globs-${locale}.ts`)
    const message = lang.default ?? {}

    /*
     * 0、加载页面语言包 import.meta.globEager 的路径不能使用变量
     * 1、vue3 setup 内只能使用 useI18n({messages:{}}) 来动态载入当前页面单独的语言包，不方便使用
     * 2、直接载入所有 /@/lang/pages/语言/*.ts 文件，若某页面有特别大量的语言配置，可在其他位置单独建立语言包文件，并在对应页面加载语言包
     */
    if (locale == 'zh-cn') {
        assignLocale[locale].push(getLangFileMessage(import.meta.globEager('./pages/zh-cn/**/*.ts'), locale))
    } else if (locale == 'en') {
        assignLocale[locale].push(getLangFileMessage(import.meta.globEager('./pages/en/**/*.ts'), locale))
    }

    const messages = {
        [locale]: {
            ...message,
        },
    }

    // 合并语言包(含element-puls、页面语言包)
    Object.assign(messages[locale], ...assignLocale[locale])

    i18n = createI18n({
        locale: locale,
        legacy: false, // 组合式api
        globalInjection: true, // 挂载$t,$d等到全局
        fallbackLocale: config.lang.fallbackLang,
        messages,
    })

    app.use(i18n)
    return i18n
}

function getLangFileMessage(mList: any, locale: string) {
    let msg: anyObj = {}
    locale = '/' + locale
    for (const path in mList) {
        if (mList[path].default) {
            //  获取文件名
            const pathName = path.slice(path.lastIndexOf(locale) + (locale.length + 1), path.lastIndexOf('.'))
            if (pathName.indexOf('/') > 0) {
                msg = handleMsglist(msg, mList[path].default, pathName)
            } else {
                msg[pathName] = mList[path].default
            }
        }
    }
    return msg
}

export function handleMsglist(msg: anyObj, mList: anyObj, pathName: string) {
    const pathNameTmp = pathName.split('/')
    let obj: anyObj = {}
    for (let i = pathNameTmp.length - 1; i >= 0; i--) {
        if (i == pathNameTmp.length - 1) {
            obj = {
                [pathNameTmp[i]]: mList,
            }
        } else {
            obj = {
                [pathNameTmp[i]]: obj,
            }
        }
    }
    return mergeMsg(msg, obj)
}

export function mergeMsg(msg: anyObj, obj: anyObj) {
    for (const key in obj) {
        if (typeof msg[key] == 'undefined') {
            msg[key] = obj[key]
        } else if (typeof msg[key] == 'object') {
            msg[key] = mergeMsg(msg[key], obj[key])
        }
    }
    return msg
}

export function editDefaultLang(lang: string): void {
    const config = useConfig()
    config.setLang(lang)

    /*
     * 语言包是按需加载的,比如默认语言为中文,则只在app实例内加载了中文语言包
     * 查阅文档无数遍,无耐接受当前的 i18n 版本并不支持动态添加语言包(或需要在 setup 内动态添加,无法满足全局替换的需求)
     * 故 reload;如果您有已经实现的无需一次性加载全部语言包且无需 reload 的方案,请一定@我
     */
    location.reload()
}
