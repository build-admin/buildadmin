import { nextTick } from 'vue'
import { loadCss, loadJs } from './common'
import * as elIcons from '@element-plus/icons-vue'

const cssUrls: Array<string> = [
    '//at.alicdn.com/t/font_3135462_5axiswmtpj.css',// 示例链接，建议替换
    '//netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
]
const jsUrls: Array<string> = []

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
    let sheets = []
    const styles: StyleSheetList = document.styleSheets
    for (const key in styles) {
        if (styles[key].href && (styles[key].href as string).indexOf(domain) > -1) {
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

            let svgEl = document.getElementById('local-icon')
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
            let iconfonts = []
            let sheets = getStylesFromDomain('netdna.bootstrapcdn.com/font-awesome/')
            for (const key in sheets) {
                let rules: any = sheets[key].cssRules
                for (const k in rules) {
                    if (rules[k].selectorText && /^\.fa-(.*)::before$/g.test(rules[k].selectorText)) {
                        if (rules[k].selectorText.indexOf(', ') > -1) {
                            let iconNames = rules[k].selectorText.split(', ')
                            /*
                            // 含图标别名
                            for (const i_k in iconNames) {
                                iconfonts.push(`${iconNames[i_k].substring(1, iconNames[i_k].length).replace(/\:\:before/gi, '')}`)
                            } */
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
            let iconfonts = []
            let sheets = getStylesFromDomain('at.alicdn.com')
            for (const key in sheets) {
                let rules: any = sheets[key].cssRules
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
            let iconfonts = []
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
