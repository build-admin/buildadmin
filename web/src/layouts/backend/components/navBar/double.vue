<template>
    <div class="layouts-menu-horizontal-double">
        <el-scrollbar ref="layoutMenuScrollbarRef" class="double-menus-scrollbar">
            <el-menu ref="layoutMenuRef" class="menu-horizontal" mode="horizontal" :default-active="state.defaultActive">
                <MenuTree :extends="{ position: 'horizontal', level: 1 }" :menus="navTabs.state.tabsViewRoutes" />
            </el-menu>
        </el-scrollbar>
        <NavMenus />
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive } from 'vue'
import { onBeforeRouteUpdate, useRoute, type RouteLocationNormalizedLoaded } from 'vue-router'
import MenuTree from '/@/layouts/backend/components/menus/menuTree.vue'
import NavMenus from '/@/layouts/backend/components/navMenus.vue'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { layoutMenuRef, layoutMenuScrollbarRef } from '/@/stores/refs'
import horizontalScroll from '/@/utils/horizontalScroll'
import { getMenuKey } from '/@/utils/router'

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()

const state = reactive({
    defaultActive: '',
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
}

// 滚动条滚动到激活菜单所在位置
const verticalMenusScroll = () => {
    setTimeout(() => {
        let activeMenu: HTMLElement | null = document.querySelector('.el-menu.menu-horizontal li.is-active')
        if (activeMenu) {
            layoutMenuScrollbarRef.value?.setScrollLeft(activeMenu.offsetLeft)
        }
    }, 500)
}

onMounted(() => {
    currentRouteActive(route)
    verticalMenusScroll()

    new horizontalScroll(layoutMenuScrollbarRef.value!.wrapRef!)
})

onBeforeRouteUpdate((to) => {
    currentRouteActive(to)
})
</script>

<style scoped lang="scss">
.layouts-menu-horizontal-double {
    display: flex;
    align-items: center;
    height: var(--el-header-height);
    background-color: var(--ba-bg-color-overlay);
    border-bottom: 1px solid var(--el-color-info-light-8);
}
.double-menus-scrollbar {
    width: 70vw;
    height: var(--el-header-height);
}
.menu-horizontal {
    border: none;
    --el-menu-bg-color: v-bind('config.getColorVal("menuBackground")');
    --el-menu-text-color: v-bind('config.getColorVal("menuColor")');
    --el-menu-active-color: v-bind('config.getColorVal("menuActiveColor")');
}

.el-sub-menu .icon,
.el-menu-item .icon {
    vertical-align: middle;
    margin-right: 5px;
    width: 24px;
    text-align: center;
    flex-shrink: 0;
}
.is-active .icon {
    color: var(--el-menu-active-color) !important;
}
.el-menu-item.is-active {
    background-color: v-bind('config.getColorVal("menuActiveBackground")');
}
</style>
