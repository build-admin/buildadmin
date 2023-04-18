<template>
    <div class="layouts-menu-horizontal">
        <div class="menu-horizontal-logo" v-if="config.layout.menuShowTopBar">
            <Logo />
        </div>
        <el-scrollbar ref="horizontalMenusRef" class="horizontal-menus-scrollbar">
            <el-menu class="menu-horizontal" mode="horizontal" :default-active="state.defaultActive" :key="state.menuKey">
                <!-- 横向菜单直接使用 <MenuTree :menus="menus" /> 会报警告 -->
                <template v-for="menu in menus">
                    <template v-if="menu.children && menu.children.length > 0">
                        <el-sub-menu :index="menu.path" :key="menu.path">
                            <template #title>
                                <Icon
                                    :color="config.getColorVal('menuColor')"
                                    :name="menu.meta?.icon ? menu.meta?.icon : config.layout.menuDefaultIcon"
                                />
                                <span>{{ menu.meta?.title ? menu.meta?.title : $t('noTitle') }}</span>
                            </template>
                            <menu-tree :menus="menu.children"></menu-tree>
                        </el-sub-menu>
                    </template>
                    <template v-else>
                        <el-menu-item v-blur :index="menu.path" :key="menu.path" @click="onClickMenu(menu)">
                            <Icon
                                :color="config.getColorVal('menuColor')"
                                :name="menu.meta?.icon ? menu.meta?.icon : config.layout.menuDefaultIcon"
                            />
                            <span>{{ menu.meta?.title ? menu.meta?.title : $t('noTitle') }}</span>
                        </el-menu-item>
                    </template>
                </template>
            </el-menu>
        </el-scrollbar>
        <NavMenus />
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import Logo from '/@/layouts/backend/components/logo.vue'
import MenuTree from '/@/layouts/backend/components/menus/menuTree.vue'
import { useRoute, onBeforeRouteUpdate, RouteLocationNormalizedLoaded } from 'vue-router'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import type { ElScrollbar } from 'element-plus'
import NavMenus from '/@/layouts/backend/components/navMenus.vue'
import { onClickMenu } from '/@/utils/router'
import { uuid } from '/@/utils/random'

const horizontalMenusRef = ref<InstanceType<typeof ElScrollbar>>()

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()

const state = reactive({
    menuKey: uuid(),
    defaultActive: '',
})

const menus = computed(() => {
    state.menuKey = uuid() // eslint-disable-line
    return navTabs.state.tabsViewRoutes
})

// 激活当前路由的菜单
const currentRouteActive = (currentRoute: RouteLocationNormalizedLoaded) => {
    state.defaultActive = currentRoute.path
}

// 滚动条滚动到激活菜单所在位置
const verticalMenusScroll = () => {
    nextTick(() => {
        let activeMenu: HTMLElement | null = document.querySelector('.el-menu.menu-horizontal li.is-active')
        if (!activeMenu) return false
        horizontalMenusRef.value?.setScrollTop(activeMenu.offsetTop)
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

<style scoped lang="scss">
.layouts-menu-horizontal {
    display: flex;
    align-items: center;
    width: 100vw;
    height: 60px;
    background-color: var(--ba-bg-color-overlay);
    border-bottom: solid 1px var(--el-color-info-light-8);
}
.menu-horizontal-logo {
    width: 180px;
    &:hover {
        background-color: v-bind('config.getColorVal("headerBarHoverBackground")');
    }
}
.horizontal-menus-scrollbar {
    flex: 1;
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
