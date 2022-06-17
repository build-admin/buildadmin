<template>
    <component :is="config.layout.layoutMode"></component>
</template>

<script setup lang="ts">
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { useTerminal } from '/@/stores/terminal'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useAdminInfo } from '/@/stores/adminInfo'
import { useRoute } from 'vue-router'
import Default from '/@/layouts/backend/container/default.vue'
import Classic from '/@/layouts/backend/container/classic.vue'
import Streamline from '/@/layouts/backend/container/streamline.vue'
import { onMounted, onBeforeMount, onUnmounted } from 'vue'
import { Session } from '/@/utils/storage'
import { index } from '/@/api/backend'
import { handleAdminRoute, getMenuPaths, pushFirstRoute } from '/@/utils/router'
import router from '/@/router/index'
import { adminBaseRoute } from '/@/router/static'
import { BEFORE_RESIZE_LAYOUT } from '/@/stores/constant/cacheKey'

const terminal = useTerminal()
const navTabs = useNavTabs()
const config = useConfig()
const route = useRoute()
const siteConfig = useSiteConfig()
const adminInfo = useAdminInfo()

onMounted(() => {
    if (!adminInfo.token) return router.push({ name: 'adminLogin' })

    init()
    onSetNavTabsMinWidth()
    window.addEventListener('resize', onSetNavTabsMinWidth)
})
onBeforeMount(() => {
    onAdaptiveLayout()
    window.addEventListener('resize', onAdaptiveLayout)
})
onUnmounted(() => {
    window.removeEventListener('resize', onAdaptiveLayout)
    window.removeEventListener('resize', onSetNavTabsMinWidth)
})

const init = () => {
    index().then((res) => {
        siteConfig.$state = res.data.siteConfig
        terminal.changePort(res.data.terminal.install_service_port)
        terminal.changePackageManager(res.data.terminal.npm_package_manager)

        if (res.data.menus) {
            let menuRule = handleAdminRoute(res.data.menus)
            // 更新vuex中的路由菜单数据
            navTabs.setTabsViewRoutes(menuRule)

            // 预跳转到上次路径
            if (route.query && route.query.url && route.query.url != adminBaseRoute.path) {
                // 检查路径是否有权限
                let menuPaths = getMenuPaths(menuRule)
                if (menuPaths.indexOf(route.query.url as string) !== -1) {
                    let query = JSON.parse(route.query.query as string)
                    router.push({ path: route.query.url as string, query: Object.keys(query).length ? query : {} })
                    return
                }
            }

            // 跳转到第一个菜单
            pushFirstRoute()
        }
    })
}

const onAdaptiveLayout = () => {
    let defaultBeforeResizeLayout = {
        layoutMode: config.layout.layoutMode,
        menuCollapse: config.layout.menuCollapse,
    }
    let beforeResizeLayout = Session.get(BEFORE_RESIZE_LAYOUT)
    if (!beforeResizeLayout) Session.set(BEFORE_RESIZE_LAYOUT, defaultBeforeResizeLayout)

    const clientWidth = document.body.clientWidth
    if (clientWidth < 1024) {
        config.setLayout('menuCollapse', true)
        config.setLayout('shrink', true)
        config.setLayoutMode('Classic')
    } else {
        let beforeResizeLayoutTemp = beforeResizeLayout || defaultBeforeResizeLayout

        config.setLayout('menuCollapse', beforeResizeLayoutTemp.menuCollapse)
        config.setLayout('shrink', false)
        config.setLayoutMode(beforeResizeLayoutTemp.layoutMode)
    }
}

// 在实例挂载后为navTabs设置一个min-width，防止宽度改变时闪现滚动条
const onSetNavTabsMinWidth = () => {
    const navTabs = document.querySelector('.nav-tabs') as HTMLElement
    if (!navTabs) {
        return
    }
    const navBar = document.querySelector('.nav-bar') as HTMLElement
    const navMenus = document.querySelector('.nav-menus') as HTMLElement
    const minWidth = navBar.offsetWidth - (navMenus.offsetWidth + 20)
    navTabs.style.width = minWidth.toString() + 'px'
}
</script>

<!-- 只有在 components 选项中的组件可以被动态组件使用-->
<script lang="ts">
export default {
    components: { Default, Classic, Streamline },
}
</script>
