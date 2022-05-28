<template>
    <div class="layouts-menu-horizontal">
        <div class="menu-horizontal-logo" v-if="config.layout.menuShowTopBar">
            <Logo />
        </div>
        <el-scrollbar ref="horizontalMenusRef" class="horizontal-menus-scrollbar">
            <el-menu
                class="menu-horizontal"
                mode="horizontal"
                :default-active="state.defaultActive"
                :background-color="config.layout.menuBackground"
                :text-color="config.layout.menuColor"
                :active-text-color="config.layout.menuActiveColor"
                :key="state.menuKey"
            >
                <!-- 横向菜单直接使用 <MenuTree :menus="menus" /> 会报警告 -->
                <template v-for="menu in menus">
                    <template v-if="menu.children && menu.children.length > 0">
                        <el-sub-menu :index="menu.path" :key="menu.path">
                            <template #title>
                                <Icon :color="config.layout.menuColor" :name="menu.icon ? menu.icon : config.layout.menuDefaultIcon" />
                                <span>{{ menu.title ? menu.title : $t('noTitle') }}</span>
                            </template>
                            <menu-tree :menus="menu.children"></menu-tree>
                        </el-sub-menu>
                    </template>
                    <template v-else>
                        <el-menu-item :index="menu.path" :key="menu.path" @click="clickMenu(menu)">
                            <Icon :color="config.layout.menuColor" :name="menu.icon ? menu.icon : config.layout.menuDefaultIcon" />
                            <span>{{ menu.title ? menu.title : $t('noTitle') }}</span>
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
import Logo from './logo.vue'
import MenuTree from './menuTree.vue'
import { useRoute, onBeforeRouteUpdate, RouteLocationNormalizedLoaded } from 'vue-router'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import type { ElScrollbar } from 'element-plus'
import NavMenus from '/@/layouts/backend/components/navMenus.vue'
import { clickMenu } from '/@/utils/router'
import { uuid } from '../../../utils/random'

const horizontalMenusRef = ref<InstanceType<typeof ElScrollbar>>()

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()

const state = reactive({
    menuKey: uuid(),
    defaultActive: '',
})

const menus = computed(() => {
    state.menuKey = uuid()
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
    background-color: #ffffff;
    border-bottom: solid 1px #e6e6e6;
}
.menu-horizontal-logo {
    width: 180px;
    &:hover {
        background-color: v-bind('config.layout.headerBarHoverBackground');
    }
}
.menu-horizontal {
    border: none;
}

.el-sub-menu .icon,
.el-menu-item .icon {
    vertical-align: middle;
    margin-right: 5px;
    width: 24px;
    text-align: center;
}
.is-active .icon {
    color: var(--el-menu-active-color) !important;
}
.el-menu-item.is-active {
    background-color: v-bind('config.layout.menuActiveBackground');
}
</style>
