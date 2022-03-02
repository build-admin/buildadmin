import { reactive } from 'vue'
import { defineStore } from 'pinia'
import { STORE_CONFIG } from '/@/stores/constant/cacheKey'
import { Layout } from '/@/stores/interface'

export const useConfig = defineStore(
    'config',
    () => {
        const layout: Layout = reactive({
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
        })

        const lang = reactive({
            // 默认语言，可选值<zh-cn|en>
            defaultLang: 'zh-cn',
            // 当在默认语言包找不到翻译时，继续在 fallbackLang 语言包内查找翻译
            fallbackLang: 'zh-cn',
            // 支持的语言列表
            langArray: [
                { name: 'zh-cn', value: '中文简体' },
                { name: 'en', value: 'English' },
            ],
        })

        function menuWidth() {
            if (layout.shrink) {
                return layout.menuCollapse ? '0px' : layout.menuWidth + 'px'
            }
            // 菜单是否折叠
            return layout.menuCollapse ? '64px' : layout.menuWidth + 'px'
        }

        function setLang(val: string) {
            lang.defaultLang = val
        }

        function setLayoutMode(data: string) {
            layout.layoutMode = data

            // 切换布局时，如果是为默认配色方案，对菜单激活背景色重新赋值
            if (data == 'Classic' && layout.headerBarBackground == '#ffffff' && layout.headerBarBackground == '#ffffff') {
                layout.headerBarTabActiveBackground = '#f5f5f5'
            } else if (data == 'Default' && layout.headerBarBackground == '#ffffff' && layout.headerBarTabActiveBackground == '#f5f5f5') {
                layout.headerBarTabActiveBackground = '#ffffff'
            }
        }

        const setLayout = (name: keyof Layout, value: any) => {
            layout[name] = value as never
        }

        return { layout, lang, menuWidth, setLang, setLayoutMode, setLayout }
    },
    {
        persist: {
            key: STORE_CONFIG,
        },
    }
)
