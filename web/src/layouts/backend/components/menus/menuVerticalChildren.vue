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
import { computed, nextTick, onMounted, reactive } from 'vue'
import MenuTree from '/@/layouts/backend/components/menus/menuTree.vue'
import type { RouteLocationNormalizedLoaded, RouteRecordRaw } from 'vue-router'
import { layoutMenuRef, layoutMenuScrollbarRef } from '/@/stores/refs'
import { useRoute, onBeforeRouteUpdate } from 'vue-router'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { currentRouteTopActivity } from '/@/layouts/backend/components/menus/helper'

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()

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
 * @param currentRoute 当前路由
 */
const currentRouteActive = (currentRoute: RouteLocationNormalizedLoaded) => {
    let routeChildren = currentRouteTopActivity(currentRoute.path, navTabs.state.tabsViewRoutes)
    if (routeChildren) {
        state.defaultActive = currentRoute.path
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
    nextTick(() => {
        let activeMenu: HTMLElement | null = document.querySelector('.el-menu.layouts-menu-vertical-children li.is-active')
        if (!activeMenu) return false
        layoutMenuScrollbarRef.value?.setScrollTop(activeMenu.offsetTop)
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
