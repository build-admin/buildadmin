<template>
    <el-scrollbar ref="verticalMenusRef" class="vertical-menus-scrollbar">
        <el-menu
            class="layouts-menu-vertical"
            router
            :collapse-transition="false"
            :unique-opened="layoutConfig.menuUniqueOpened"
            :default-active="state.defaultActive"
            :collapse="layoutConfig.menuCollapse"
            :background-color="layoutConfig.menuBackground"
            :text-color="layoutConfig.menuColor"
            :active-text-color="layoutConfig.menuActiveColor"
        >
            <MenuTree :menus="menus" />
        </el-menu>
    </el-scrollbar>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import MenuTree from './menuTree.vue'
import { useRoute, onBeforeRouteUpdate, RouteLocationNormalizedLoaded } from 'vue-router'
import type { ElScrollbar } from 'element-plus'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()

const verticalMenusRef = ref<InstanceType<typeof ElScrollbar>>()

const state = reactive({
    defaultActive: '',
})

const menus = computed(() => navTabs.state.tabsViewRoutes)
const layoutConfig = computed(() => config.layout)
const verticalMenusScrollbarHeight = computed(() => {
    let menuTopBarHeight = 0
    if (layoutConfig.value.menuShowTopBar) {
        menuTopBarHeight = 50
    }
    if (layoutConfig.value.layoutMode == 'Default') {
        return 'calc(100vh - ' + (32 + menuTopBarHeight) + 'px)'
    } else {
        return 'calc(100vh - ' + menuTopBarHeight + 'px)'
    }
})

// 激活当前路由的菜单
const currentRouteActive = (currentRoute: RouteLocationNormalizedLoaded) => {
    state.defaultActive = currentRoute.path
}

// 滚动条滚动到激活菜单所在位置
const verticalMenusScroll = () => {
    nextTick(() => {
        let activeMenu: HTMLElement | null = document.querySelector('.el-menu.layouts-menu-vertical li.is-active')
        if (!activeMenu) return false
        verticalMenusRef.value?.setScrollTop(activeMenu.offsetTop)
    })
}

onMounted(() => {
    currentRouteActive(route)
    verticalMenusScroll()
})

onBeforeRouteUpdate((to) => {
    currentRouteActive(to)
})
</script>
<style>
.vertical-menus-scrollbar {
    height: v-bind(verticalMenusScrollbarHeight);
}
.layouts-menu-vertical {
    border: 0;
}
</style>
