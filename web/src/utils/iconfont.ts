import { nextTick } from 'vue'
import { loadCss, loadJs } from './common'
import * as elIcons from '@element-plus/icons-vue'
import { getUrl } from '/@/utils/axios'

/**
 * 动态加载的 css 和 js
 */
const cssUrls: Array<string> = ['//at.alicdn.com/t/font_3135462_5axiswmtpj.css']
const jsUrls: Array<string> = []

/*
 * 加载预设的字体图标资源
 */
export default function init() {
    if (cssUrls.length > 0) {
        cssUrls.map((v) => {
            loadCss(v)
        })
    }

    if (jsUrls.length > 0) {
        jsUrls.map((v) => {
            loadJs(v)
        })
    }
}

/*
 * 获取当前页面中从指定域名加载到的样式表内容
 * 样式表未载入前无法获取
 */
function getStylesFromDomain(domain: string) {
    const sheets = []
    const styles: StyleSheetList = document.styleSheets
    for (const key in styles) {
        if (styles[key].href && (styles[key].href as string).indexOf(domain) > -1) {
            sheets.push(styles[key])
        }
    }
    return sheets
}

/**
 * 获取Vite开发服务/编译后的样式表内容
 * @param devID style 标签的 viteDevId，只开发服务有
 */
function getStylesFromVite(devId: string) {
    const sheets = []
    const styles: StyleSheetList = document.styleSheets
    if (import.meta.env.MODE == 'production') {
        const url = getUrl()
        for (const key in styles) {
            if (styles[key].href && styles[key].href?.indexOf(url) === 0) {
                sheets.push(styles[key])
            }
        }
        return sheets
    }
    for (const key in styles) {
        const ownerNode = styles[key].ownerNode as HTMLMapElement
        if (ownerNode && ownerNode.dataset?.viteDevId && ownerNode.dataset.viteDevId!.indexOf(devId) > -1) {
            sheets.push(styles[key])
        }
    }
    return sheets
}

/*
 * 获取本地自带的图标
 * /src/assets/icons文件夹内的svg文件
 */
export function getLocalIconfontNames() {
    return new Promise<string[]>((resolve, reject) => {
        nextTick(() => {
            let iconfonts: string[] = []

            const svgEl = document.getElementById('local-icon')
            if (svgEl?.dataset.iconName) {
                iconfonts = (svgEl?.dataset.iconName as string).split(',')
            }

            if (iconfonts.length > 0) {
                resolve(iconfonts)
            } else {
                reject('No Local Icons')
            }
        })
    })
}

/*
 * 获取 Awesome-Iconfont 的 name 列表
 */
export function getAwesomeIconfontNames() {
    return new Promise<string[]>((resolve, reject) => {
        nextTick(() => {
            const iconfonts = []
            const sheets = getStylesFromVite('font-awesome.min.css')
            for (const key in sheets) {
                const rules: any = sheets[key].cssRules
                for (const k in rules) {
                    if (!rules[k].selectorText || rules[k].selectorText.indexOf('.fa-') !== 0) {
                        continue
                    }
                    if (/^\.fa-(.*)::before$/g.test(rules[k].selectorText)) {
                        if (rules[k].selectorText.indexOf(', ') > -1) {
                            const iconNames = rules[k].selectorText.split(', ')
                            iconfonts.push(`${iconNames[0].substring(1, iconNames[0].length).replace(/\:\:before/gi, '')}`)
                        } else {
                            iconfonts.push(`${rules[k].selectorText.substring(1, rules[k].selectorText.length).replace(/\:\:before/gi, '')}`)
                        }
                    }
                }
            }

            if (iconfonts.length > 0) {
                resolve(iconfonts)
            } else {
                reject('No AwesomeIcon style sheet')
            }
        })
    })
}

/*
 * 获取 Iconfont 的 name 列表
 */
export function getIconfontNames() {
    return new Promise<string[]>((resolve, reject) => {
        nextTick(() => {
            const iconfonts = []
            const sheets = getStylesFromDomain('at.alicdn.com')
            for (const key in sheets) {
                const rules: any = sheets[key].cssRules
                for (const k in rules) {
                    if (rules[k].selectorText && /^\.icon-(.*)::before$/g.test(rules[k].selectorText)) {
                        iconfonts.push(`${rules[k].selectorText.substring(1, rules[k].selectorText.length).replace(/\:\:before/gi, '')}`)
                    }
                }
            }

            if (iconfonts.length > 0) {
                resolve(iconfonts)
            } else {
                reject('No Iconfont style sheet')
            }
        })
    })
}

/*
 * 获取element plus 自带的图标
 */
export function getElementPlusIconfontNames() {
    return new Promise<string[]>((resolve, reject) => {
        nextTick(() => {
            const iconfonts = []
            const icons = elIcons as any
            for (const i in icons) {
                iconfonts.push(`el-icon-${icons[i].name}`)
            }
            if (iconfonts.length > 0) {
                resolve(iconfonts)
            } else {
                reject('No ElementPlus Icons')
            }
        })
    })
}
