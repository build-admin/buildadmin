<template>
    <component :is="layoutConfig.layoutMode"></component>
</template>

<script setup lang="ts">
import { useStore } from '/@/store'
import { useRoute } from 'vue-router'
import Default from '/@/layouts/container/default.vue'
import Classic from '/@/layouts/container/classic.vue'
import Streamline from '/@/layouts/container/streamline.vue'
import { computed, onBeforeMount, onUnmounted } from 'vue'
import { Session } from '/@/utils/storage'
import { index } from '/@/api/backend'
import { handleAdminRoute, getMenuPaths } from '/@/utils/common'
import router from '/@/router/index'
import { adminBaseRoute } from '/@/router/static'

const route = useRoute()

index().then((res) => {
    if (res.data.menus) {
        let menuRule = handleAdminRoute(res.data.menus)
        // 更新vuex中的路由菜单数据
        store.dispatch('navTabs/setTabsViewRoutes', menuRule)

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
        let routerNames = []
        let routers = router.getRoutes()
        for (const key in routers) {
            if (routers[key].name) routerNames.push(routers[key].name)
        }
        for (const key in res.data.menus) {
            if (res.data.menus[key].type != 'menu_dir' && routerNames.indexOf(res.data.menus[key].name) !== -1) {
                router.push({ name: res.data.menus[key].name })
                break
            }
        }
    }
})

const store = useStore()
const layoutConfig = computed(() => store.state.config.layout)

const onResize = () => {
    let defaultBeforeResizeLayout = {
        layoutMode: layoutConfig.value.layoutMode,
        menuCollapse: layoutConfig.value.menuCollapse,
    }

    if (!Session.get('beforeResizeLayout')) Session.set('beforeResizeLayout', defaultBeforeResizeLayout)
    const clientWidth = document.body.clientWidth
    if (clientWidth < 1024) {
        store.commit('config/setAndCache', {
            name: 'layout.menuCollapse',
            value: true,
        })
        store.commit('config/setAndCache', {
            name: 'layout.shrink',
            value: true,
        })
        store.dispatch('config/setLayoutMode', 'Classic')
    } else {
        let beforeResizeLayout = Session.get('beforeResizeLayout') ? Session.get('beforeResizeLayout') : defaultBeforeResizeLayout

        store.commit('config/setAndCache', {
            name: 'layout.menuCollapse',
            value: beforeResizeLayout.menuCollapse,
        })
        store.commit('config/setAndCache', {
            name: 'layout.shrink',
            value: false,
        })
        store.dispatch('config/setLayoutMode', beforeResizeLayout.layoutMode)
    }
}

onBeforeMount(() => {
    onResize()
    window.addEventListener('resize', onResize)
})
onUnmounted(() => {
    window.removeEventListener('resize', onResize)
})
</script>

<!-- 只有在 components 选项中的组件可以被动态组件使用-->
<script lang="ts">
export default {
    components: { Default, Classic, Streamline },
}
</script>
