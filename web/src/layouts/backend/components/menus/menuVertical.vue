<template>
    <el-scrollbar ref="layoutMenuScrollbarRef" class="vertical-menus-scrollbar">
        <el-menu
            class="layouts-menu-vertical"
            :collapse-transition="false"
            :unique-opened="config.layout.menuUniqueOpened"
            :default-active="state.defaultActive"
            :collapse="config.layout.menuCollapse"
            ref="layoutMenuRef"
        >
            <MenuTree :menus="navTabs.state.tabsViewRoutes" />
        </el-menu>
    </el-scrollbar>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive } from 'vue'
import { onBeforeRouteUpdate, useRoute, type RouteLocationNormalizedLoaded } from 'vue-router'
import MenuTree from '/@/layouts/backend/components/menus/menuTree.vue'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { layoutMenuRef, layoutMenuScrollbarRef } from '/@/stores/refs'
import { getMenuKey } from '/@/utils/router'

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()

const state = reactive({
    defaultActive: '',
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
 * 激活当前路由对应的菜单
 */
const currentRouteActive = (currentRoute: RouteLocationNormalizedLoaded) => {
    // 以路由 fullPath 匹配的菜单优先，且 fullPath 无匹配时，回退到 path 的匹配菜单
    const tabView = navTabs.getTabsViewDataByRoute(currentRoute)
    if (tabView) {
        state.defaultActive = getMenuKey(tabView, tabView.meta!.matched as string)
    }
}

/**
 * 滚动条滚动到激活菜单所在位置
 */
const verticalMenusScroll = () => {
    setTimeout(() => {
        let activeMenu: HTMLElement | null = document.querySelector('.el-menu.layouts-menu-vertical li.is-active')
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
.vertical-menus-scrollbar {
    height: v-bind(verticalMenusScrollbarHeight);
    background-color: v-bind('config.getColorVal("menuBackground")');
}
.layouts-menu-vertical {
    border: 0;
    padding-bottom: 30px;
    --el-menu-bg-color: v-bind('config.getColorVal("menuBackground")');
    --el-menu-text-color: v-bind('config.getColorVal("menuColor")');
    --el-menu-active-color: v-bind('config.getColorVal("menuActiveColor")');
}
</style>
