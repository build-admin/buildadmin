<template>
    <el-scrollbar ref="verticalMenusRef" class="vertical-menus-scrollbar1">
        <el-menu
            class="layouts-menu-vertical1"
            :collapse-transition="false"
            :unique-opened="config.layout.menuUniqueOpened"
            :default-active="state.defaultActive"
            :collapse="config.layout.menuCollapse"
        >
            <MenuTree v-if="state.routeChildren.length > 0" :menus="state.routeChildren" />
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
import {RouteLocationNormalized, RouteRecordRaw} from "_vue-router@4.0.16@vue-router";

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()

const verticalMenusRef = ref<InstanceType<typeof ElScrollbar>>()

const state:{
    defaultActive: String,
    routeChildren: any,
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

// 激活当前路由的菜单
const currentRouteActive = (currentRoute: RouteLocationNormalizedLoaded) => {
    let routeChildren = currentRouteTopActivity(currentRoute.path, navTabs.state.tabsViewRoutes);
    if(routeChildren){
        state.defaultActive = currentRoute.path
    }
}


const currentRouteTopActivity = (path:String, menus:RouteRecordRaw[], result:String[] = []) => {
    for(let i = 0; i < menus.length; i++){
        const item:anyObj = menus[i];
        // 找到目标
        if(item.path == path){
            result.push(item.path);
            return item;
        }
        if(item.children.length > 0){
            result.push(item.id)
            const find = currentRouteTopActivity(path, item.children, result);
            if(find){
                return item;
            }
        }
    }
    return false;
}

const currentRouteChildren = (currentRoute: RouteLocationNormalizedLoaded) => {
    let routeChildren = currentRouteTopActivity(currentRoute.path, navTabs.state.tabsViewRoutes);
    console.log('路由',routeChildren, navTabs.state.tabsViewRoutes)
    if(routeChildren){
        if(routeChildren.children.length > 0) {
            state.routeChildren = routeChildren.children;
        }else{
            state.routeChildren = [routeChildren];
        }
    }else{
        if(!state.routeChildren){
            state.routeChildren = navTabs.state.tabsViewRoutes;
        }
    }
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
    currentRouteChildren(route)
})

onBeforeRouteUpdate((to) => {
    currentRouteActive(to)

    currentRouteChildren(to)
})
</script>
<style>
.vertical-menus-scrollbar1 {
    height: v-bind(verticalMenusScrollbarHeight);
    background-color: v-bind('config.getColorVal("menuBackground")');
}
.layouts-menu-vertical1 {
    border: 0;
    --el-menu-bg-color: v-bind('config.getColorVal("menuBackground")');
    --el-menu-text-color: v-bind('config.getColorVal("menuColor")');
    --el-menu-active-color: v-bind('config.getColorVal("menuActiveColor")');
}
</style>
