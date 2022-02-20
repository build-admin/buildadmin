import { Module } from 'vuex'
import { ConfigStateTypes, RootStateTypes } from '/@/store/interface/index'
import { Local } from '/@/utils/storage'
import { CONFIG } from '/@/store/constant/cacheKey'

interface setObj {
    name: string
    value: any
}

// State 默认值 => 与缓存中存有的数据合并来赋初始值
var state: ConfigStateTypes = {
    // 布局配置
    layout: {
        /* 全局 */
        showDrawer: false,
        // 是否收缩布局(小屏设备)
        shrink: false,
        // 后台布局方式，可选值<Default|Classic|Streamline>
        layoutMode: 'Default',
        // 后台主页面切换动画，可选值<slide-right|slide-left|el-fade-in-linear|el-fade-in|el-zoom-in-center|el-zoom-in-top|el-zoom-in-bottom>
        mainAnimation: 'slide-right',

        /* 侧边菜单 */
        // 侧边菜单背景色
        menuBackground: '#ffffff',
        // 侧边菜单文字颜色
        menuColor: '#303133',
        // 侧边菜单激活项背景色
        menuActiveBackground: '#ffffff',
        // 侧边菜单激活项文字色
        menuActiveColor: '#409eff',
        // 侧边菜单顶栏背景色
        menuTopBarBackground: '#fcfcfc',
        // 侧边菜单宽度(展开时)，单位px
        menuWidth: 260,
        // 侧边菜单项默认图标
        menuDefaultIcon: 'el-icon-Minus',
        // 是否水平折叠收起菜单
        menuCollapse: false,
        // 是否只保持一个子菜单的展开(手风琴)
        menuUniqueOpened: false,
        // 显示菜单栏顶栏(LOGO)
        menuShowTopBar: true,

        /* 顶栏 */
        // 顶栏文字色
        headerBarTabColor: '#000000',
        // 顶栏激活项背景色
        headerBarTabActiveBackground: '#ffffff',
        // 顶栏激活项文字色
        headerBarTabActiveColor: '#000000',
        // 顶栏背景色
        headerBarBackground: '#ffffff',
        // 顶栏悬停时背景色
        headerBarHoverBackground: '#f5f5f5',
    },
    // 默认语言，可选值<zh-cn|en>
    defaultLang: 'zh-cn',
    // 当在默认语言包找不到翻译时，继续在 fallbackLang 语言包内查找翻译
    fallbackLang: 'zh-cn',
    // 支持的语言列表
    langArray: [
        { name: 'zh-cn', value: '中文简体' },
        { name: 'en', value: 'English' },
    ],
}
const baConfig = Local.get(CONFIG) || {}
state = { ...state, ...baConfig }

const ConfigModule: Module<ConfigStateTypes, RootStateTypes> = {
    namespaced: true,
    state,
    getters: {
        menuWidth: (state) => {
            if (state.layout.shrink) {
                return state.layout.menuCollapse ? '0px' : state.layout.menuWidth + 'px'
            }
            // 菜单是否折叠
            return state.layout.menuCollapse ? '64px' : state.layout.menuWidth + 'px'
        },
    },
    mutations: {
        // 设置单个配置项
        // name 支持`.`，列如 layout.menuCollapse
        set(state: any, data: setObj): void {
            let name = data.name.split('.')
            if (name[1]) {
                state[name[0]][name[1]] = data.value
            } else {
                state[data.name] = data.value
            }
        },
        // 批量设置配置项
        setMulti(state: any, data: object): void {
            for (let [key, val] of Object.entries(data)) {
                state[key] = val
            }
        },
        // 设置单个配置项并缓存到本地存储
        // name 支持`.`，列如 layout.menuCollapse
        setAndCache(state: any, data: setObj): void {
            let baConfig = Local.get(CONFIG) || {}

            let name = data.name.split('.')
            if (name[1]) {
                state[name[0]][name[1]] = data.value
                if (!baConfig[name[0]]) {
                    baConfig[name[0]] = state[name[0]]
                }
                baConfig[name[0]][name[1]] = data.value
            } else {
                state[data.name] = baConfig[data.name] = data.value
            }
            if (baConfig.layout) {
                baConfig.layout.showDrawer = false
            }
            Local.set(CONFIG, baConfig)
        },
    },
    actions: {
        setLayoutMode({ state, commit }, data) {
            commit('setAndCache', {
                name: 'layout.layoutMode',
                value: data,
            })

            // 切换布局时，如果是为默认配色方案，对菜单激活背景色重新赋值
            if (data == 'Classic' && state.layout.headerBarBackground == '#ffffff' && state.layout.headerBarBackground == '#ffffff') {
                commit('setAndCache', {
                    name: 'layout.headerBarTabActiveBackground',
                    value: '#f5f5f5',
                })
            } else if (data == 'Default' && state.layout.headerBarBackground == '#ffffff' && state.layout.headerBarTabActiveBackground == '#f5f5f5') {
                commit('setAndCache', {
                    name: 'layout.headerBarTabActiveBackground',
                    value: '#ffffff',
                })
            }
        },
    },
}

export default ConfigModule
