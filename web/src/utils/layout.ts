import { CSSProperties } from 'vue'
import { useNavTabs } from '/@/stores/navTabs'

/**
 * main高度
 * @param extra main高度额外减去的px数,可以实现隐藏原有的滚动条
 * @returns CSSProperties
 */
export function mainHeight(extra: number = 0): CSSProperties {
    const navTabs = useNavTabs()

    let height = extra
    if (!navTabs.state.tabFullScreen) {
        height += 75
    }
    return {
        height: 'calc(100vh - ' + height.toString() + 'px)',
    }
}
