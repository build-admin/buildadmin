<template>
    <el-scrollbar ref="layoutMenuScrollbarRef" class="children-vertical-menus-scrollbar">
        <el-menu
            class="layouts-menu-vertical-children"
            :collapse-transition="false"
            :unique-opened="config.layout.menuUniqueOpened"
            :default-active="state.defaultActive"
            :collapse="config.layout.menuCollapse"
            ref="layoutMenuRef"
        >
            <MenuTree v-if="state.routeChildren.length > 0" :menus="state.routeChildren" />
        </el-menu>
    </el-scrollbar>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, useTemplateRef } from 'vue'
import type { RouteLocationNormalizedLoaded, RouteRecordRaw } from 'vue-router'
import { onBeforeRouteUpdate, useRoute } from 'vue-router'
import MenuTree from '/@/layouts/backend/components/menus/menuTree.vue'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { layoutMenuRef } from '/@/stores/refs'
import { getMenuKey } from '/@/utils/router'

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()

const layoutMenuScrollbarRef = useTemplateRef('layoutMenuScrollbarRef')

const state: {
    defaultActive: string
    routeChildren: RouteRecordRaw[]
} = reactive({
    defaultActive: '',
    routeChildren: [],
})

const verticalMenusScrollbarHeight = computed(() => {
    let menuTopBarHeight = 0
    if (config.layout.menuShowTopBar) {
        menuTopBarHeight = 50
    }
    if (config.layout.layoutMode == 'Default') {
        return 'calc(100vh - ' + (32 + menuTopBarHeight) + 'px)'
    } else {
        return 'calc(100vh - ' + menuTopBarHeight + 'px)'
    }
})

/**
 * 激活当前路由的菜单
 */
const currentRouteActive = (currentRoute: RouteLocationNormalizedLoaded) => {
    // 以路由 fullPath 匹配的菜单优先，且 fullPath 无匹配时，回退到 path 的匹配菜单
    const tabView = navTabs.getTabsViewDataByRoute(currentRoute)
    if (tabView) {
        state.defaultActive = getMenuKey(tabView, tabView.meta!.matched as string)
    }

    let routeChildren = navTabs.getTabsViewDataByRoute(currentRoute, 'above')
    if (routeChildren) {
        if (routeChildren.children && routeChildren.children.length > 0) {
            state.routeChildren = routeChildren.children
        } else {
            state.routeChildren = [routeChildren]
        }
    } else if (!state.routeChildren) {
        state.routeChildren = navTabs.state.tabsViewRoutes
    }
}

/**
 * 侧栏菜单滚动条滚动到激活菜单所在位置
 */
const verticalMenusScroll = () => {
    setTimeout(() => {
        let activeMenu: HTMLElement | null = document.querySelector('.el-menu.layouts-menu-vertical-children li.is-active')
        if (activeMenu) {
            layoutMenuScrollbarRef.value?.setScrollTop(activeMenu.offsetTop)
        }
    }, 500)
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
.children-vertical-menus-scrollbar {
    height: v-bind(verticalMenusScrollbarHeight);
    background-color: v-bind('config.getColorVal("menuBackground")');
}
.layouts-menu-vertical-children {
    border: 0;
    --el-menu-bg-color: v-bind('config.getColorVal("menuBackground")');
    --el-menu-text-color: v-bind('config.getColorVal("menuColor")');
    --el-menu-active-color: v-bind('config.getColorVal("menuActiveColor")');
}
</style>
