import { CSSProperties } from 'vue'
import { useNavTabs } from '/@/stores/navTabs'
import { isAdminApp } from '/@/utils/common'

/**
 * main高度
 * @param extra main高度额外减去的px数,可以实现隐藏原有的滚动条
 * @returns CSSProperties
 */
export function mainHeight(extra: number = 0): CSSProperties {
    let height = extra
    if (isAdminApp()) {
        const navTabs = useNavTabs()
        if (!navTabs.state.tabFullScreen) {
            height += 75
        }
    } else {
        height += 60
    }
    return {
        height: 'calc(100vh - ' + height.toString() + 'px)',
    }
}
