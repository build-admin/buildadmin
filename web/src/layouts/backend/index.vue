<template>
    <component :is="config.layout.layoutMode"></component>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { useTerminal } from '/@/stores/terminal'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useAdminInfo } from '/@/stores/adminInfo'
import { useRoute } from 'vue-router'
import Default from '/@/layouts/backend/container/default.vue'
import Classic from '/@/layouts/backend/container/classic.vue'
import Streamline from '/@/layouts/backend/container/streamline.vue'
import Double from '/@/layouts/backend/container/double.vue'
import { onMounted, onBeforeMount } from 'vue'
import { Session } from '/@/utils/storage'
import { index } from '/@/api/backend'
import { handleAdminRoute, getFirstRoute, routePush } from '/@/utils/router'
import router from '/@/router/index'
import { adminBaseRoutePath } from '/@/router/static/adminBase'
import { useEventListener } from '@vueuse/core'
import { BEFORE_RESIZE_LAYOUT } from '/@/stores/constant/cacheKey'
import { isEmpty } from 'lodash-es'
import { setNavTabsWidth } from '/@/utils/layout'

defineOptions({
    components: { Default, Classic, Streamline, Double },
})

const terminal = useTerminal()
const navTabs = useNavTabs()
const config = useConfig()
const route = useRoute()
const siteConfig = useSiteConfig()
const adminInfo = useAdminInfo()

const state = reactive({
    autoMenuCollapseLock: false,
})

onMounted(() => {
    if (!adminInfo.token) return router.push({ name: 'adminLogin' })

    init()
    setNavTabsWidth()
    useEventListener(window, 'resize', setNavTabsWidth)
})
onBeforeMount(() => {
    onAdaptiveLayout()
    useEventListener(window, 'resize', onAdaptiveLayout)
})

const init = () => {
    /**
     * 后台初始化请求，获取站点配置，动态路由等信息
     */
    index().then((res) => {
        siteConfig.dataFill(res.data.siteConfig)
        terminal.changePackageManager(res.data.terminal.npmPackageManager)
        terminal.changePHPDevelopmentServer(res.data.terminal.phpDevelopmentServer)
        siteConfig.setInitialize(true)

        if (!isEmpty(res.data.adminInfo)) {
            adminInfo.dataFill(res.data.adminInfo)
            siteConfig.setUserInitialize(true)
        }

        if (res.data.menus) {
            handleAdminRoute(res.data.menus)

            // 预跳转到上次路径
            if (route.params.to) {
                const lastRoute = JSON.parse(route.params.to as string)
                if (lastRoute.path != adminBaseRoutePath) {
                    let query = !isEmpty(lastRoute.query) ? lastRoute.query : {}
                    routePush({ path: lastRoute.path, query: query })
                    return
                }
            }

            // 跳转到第一个菜单
            let firstRoute = getFirstRoute(navTabs.state.tabsViewRoutes)
            if (firstRoute) routePush(firstRoute.path)
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
        /**
         * 锁定窗口改变自动调整 menuCollapse
         * 避免已是小窗且打开了菜单栏时，意外的自动关闭菜单栏
         */
        if (!state.autoMenuCollapseLock) {
            state.autoMenuCollapseLock = true
            config.setLayout('menuCollapse', true)
        }
        config.setLayout('shrink', true)
        config.setLayoutMode('Classic')
    } else {
        state.autoMenuCollapseLock = false
        let beforeResizeLayoutTemp = beforeResizeLayout || defaultBeforeResizeLayout

        config.setLayout('menuCollapse', beforeResizeLayoutTemp.menuCollapse)
        config.setLayout('shrink', false)
        config.setLayoutMode(beforeResizeLayoutTemp.layoutMode)
    }
}
</script>
