<template>
    <component :is="layoutConfig.layoutMode"></component>
</template>

<script setup lang="ts">
import { useStore } from '/@/store'
import Default from '/@/layouts/container/default.vue'
import Classic from '/@/layouts/container/classic.vue'
import Streamline from '/@/layouts/container/streamline.vue'
import menus from '/@/mock/router.json' // 模拟api请求数据
import { computed, onBeforeMount, onUnmounted } from 'vue'
import { Session } from '/@/utils/storage'

const store = useStore()
const layoutConfig = computed(() => store.state.config.layout)

// 更新vuex中的路由菜单数据
store.dispatch('navTabs/setTabsViewRoutes', menus)

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
