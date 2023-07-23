import { CSSProperties } from 'vue'
import { useNavTabs } from '/@/stores/navTabs'
import { useConfig } from '/@/stores/config'
import { isAdminApp } from '/@/utils/common'

/**
 * main高度
 * @param extra main高度额外减去的px数,可以实现隐藏原有的滚动条
 * @returns CSSProperties
 */
export function mainHeight(extra = 0): CSSProperties {
    let height = extra
    const adminLayoutMainExtraHeight: anyObj = {
        Default: 70,
        Classic: 50,
        Streamline: 60,
    }
    if (isAdminApp()) {
        const config = useConfig()
        const navTabs = useNavTabs()
        if (!navTabs.state.tabFullScreen) {
            height += adminLayoutMainExtraHeight[config.layout.layoutMode]
        }
    } else {
        height += 60
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
