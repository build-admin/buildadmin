import type { CSSProperties } from 'vue'
import { useConfig } from '/@/stores/config'
import { useMemberCenter } from '/@/stores/memberCenter'
import { useNavTabs } from '/@/stores/navTabs'
import { isAdminApp } from '/@/utils/common'

/**
 * 管理员后台各个布局顶栏高度
 */
export const adminLayoutHeaderBarHeight = {
    Default: 70,
    Classic: 50,
    Streamline: 60,
    Double: 60,
}

/**
 * 前台会员中心各个布局顶栏高度
 */
export const userLayoutHeaderBarHeight = {
    Default: 60,
    Disable: 60,
}

/**
 * main高度
 * @param extra main高度额外减去的px数,可以实现隐藏原有的滚动条
 * @returns CSSProperties
 */
export function mainHeight(extra = 0): CSSProperties {
    let height = extra
    if (isAdminApp()) {
        const config = useConfig()
        const navTabs = useNavTabs()
        if (!navTabs.state.tabFullScreen) {
            height += adminLayoutHeaderBarHeight[config.layout.layoutMode as keyof typeof adminLayoutHeaderBarHeight]
        }
    } else {
        const memberCenter = useMemberCenter()
        height += userLayoutHeaderBarHeight[memberCenter.state.layoutMode as keyof typeof userLayoutHeaderBarHeight]
    }
    return {
        height: 'calc(100vh - ' + height.toString() + 'px)',
    }
}

/**
 * 设置导航栏宽度
 * @returns
 */
export function setNavTabsWidth() {
    const navTabs = document.querySelector('.nav-tabs') as HTMLElement
    if (!navTabs) {
        return
    }
    const navBar = document.querySelector('.nav-bar') as HTMLElement
    const navMenus = document.querySelector('.nav-menus') as HTMLElement
    const minWidth = navBar.offsetWidth - (navMenus.offsetWidth + 20)
    navTabs.style.width = minWidth.toString() + 'px'
}
