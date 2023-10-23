<template>
    <div class="nav-tabs" ref="tabScrollbarRef">
        <div
            v-for="(item, idx) in navTabs.state.tabsView"
            @click="onTab(item)"
            @contextmenu.prevent="onContextmenu(item, $event)"
            class="ba-nav-tab"
            :class="navTabs.state.activeIndex == idx ? 'active' : ''"
            :ref="tabsRefs.set"
            :key="idx"
        >
            {{ item.meta.title }}
            <transition @after-leave="selectNavTab(tabsRefs[navTabs.state.activeIndex])" name="el-fade-in">
                <Icon v-show="navTabs.state.tabsView.length > 1" class="close-icon" @click.stop="closeTab(item)" size="15" name="el-icon-Close" />
            </transition>
        </div>
        <div :style="activeBoxStyle" class="nav-tabs-active-box"></div>
    </div>
    <Contextmenu ref="contextmenuRef" :items="state.contextmenuItems" @contextmenuItemClick="onContextmenuItem" />
</template>

<script setup lang="ts">
import { nextTick, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter, onBeforeRouteUpdate, type RouteLocationNormalized } from 'vue-router'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { useTemplateRefsList } from '@vueuse/core'
import type { ContextMenuItem, ContextmenuItemClickEmitArg } from '/@/components/contextmenu/interface'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import Contextmenu from '/@/components/contextmenu/index.vue'
import horizontalScroll from '/@/utils/horizontalScroll'
import { getFirstRoute, routePush } from '/@/utils/router'
import { adminBaseRoutePath } from '/@/router/static/adminBase'

const route = useRoute()
const router = useRouter()
const config = useConfig()
const navTabs = useNavTabs()

const { proxy } = useCurrentInstance()
const tabScrollbarRef = ref()
const tabsRefs = useTemplateRefsList<HTMLDivElement>()

const contextmenuRef = ref()

const state: {
    contextmenuItems: ContextMenuItem[]
} = reactive({
    contextmenuItems: [
        { name: 'refresh', label: '重新加载', icon: 'fa fa-refresh' },
        { name: 'close', label: '关闭标签', icon: 'fa fa-times' },
        { name: 'fullScreen', label: '当前标签全屏', icon: 'el-icon-FullScreen' },
        { name: 'closeOther', label: '关闭其他标签', icon: 'fa fa-minus' },
        { name: 'closeAll', label: '关闭全部标签', icon: 'fa fa-stop' },
    ],
})

const activeBoxStyle = reactive({
    width: '0',
    transform: 'translateX(0px)',
})

const onTab = (menu: RouteLocationNormalized) => {
    router.push(menu)
}

const onContextmenu = (menu: RouteLocationNormalized, el: MouseEvent) => {
    // 禁用刷新
    state.contextmenuItems[0].disabled = route.path !== menu.path
    // 禁用关闭其他和关闭全部
    state.contextmenuItems[4].disabled = state.contextmenuItems[3].disabled = navTabs.state.tabsView.length == 1 ? true : false

    const { clientX, clientY } = el
    contextmenuRef.value.onShowContextmenu(menu, {
        x: clientX,
        y: clientY,
    })
}

// tab 激活状态切换
const selectNavTab = function (dom: HTMLDivElement) {
    if (!dom) {
        return false
    }
    activeBoxStyle.width = dom.clientWidth + 'px'
    activeBoxStyle.transform = `translateX(${dom.offsetLeft}px)`

    let scrollLeft = dom.offsetLeft + dom.clientWidth - tabScrollbarRef.value.clientWidth
    if (dom.offsetLeft < tabScrollbarRef.value.scrollLeft) {
        tabScrollbarRef.value.scrollTo(dom.offsetLeft, 0)
    } else if (scrollLeft > tabScrollbarRef.value.scrollLeft) {
        tabScrollbarRef.value.scrollTo(scrollLeft, 0)
    }
}

const toLastTab = () => {
    const lastTab = navTabs.state.tabsView.slice(-1)[0]
    if (lastTab) {
        router.push(lastTab)
    } else {
        router.push(adminBaseRoutePath)
    }
}

const closeTab = (route: RouteLocationNormalized) => {
    navTabs.closeTab(route)
    proxy.eventBus.emit('onTabViewClose', route)
    if (navTabs.state.activeRoute?.path === route.path) {
        toLastTab()
    } else {
        navTabs.setActiveRoute(navTabs.state.activeRoute!)
        nextTick(() => {
            selectNavTab(tabsRefs.value[navTabs.state.activeIndex])
        })
    }

    contextmenuRef.value.onHideContextmenu()
}

const closeOtherTab = (menu: RouteLocationNormalized) => {
    navTabs.closeTabs(menu)
    navTabs.setActiveRoute(menu)
    if (navTabs.state.activeRoute?.path !== route.path) {
        router.push(menu!.path)
    }
}

const closeAllTab = (menu: RouteLocationNormalized) => {
    let firstRoute = getFirstRoute(navTabs.state.tabsViewRoutes)
    if (firstRoute && firstRoute.path == menu.path) {
        return closeOtherTab(menu)
    }
    if (firstRoute && firstRoute.path == navTabs.state.activeRoute?.path) {
        return closeOtherTab(navTabs.state.activeRoute)
    }
    navTabs.closeTabs(false)
    if (firstRoute) routePush(firstRoute.path)
}

const onContextmenuItem = async (item: ContextmenuItemClickEmitArg) => {
    const { name, menu } = item
    if (!menu) return
    switch (name) {
        case 'refresh':
            proxy.eventBus.emit('onTabViewRefresh', menu)
            break
        case 'close':
            closeTab(menu)
            break
        case 'closeOther':
            closeOtherTab(menu)
            break
        case 'closeAll':
            closeAllTab(menu)
            break
        case 'fullScreen':
            if (route.path !== menu?.path) {
                router.push(menu?.path as string)
            }
            navTabs.setFullScreen(true)
            break
    }
}

const updateTab = function (newRoute: RouteLocationNormalized) {
    // 添加tab
    navTabs.addTab(newRoute)
    // 激活当前tab
    navTabs.setActiveRoute(newRoute)

    nextTick(() => {
        selectNavTab(tabsRefs.value[navTabs.state.activeIndex])
    })
}

onBeforeRouteUpdate(async (to) => {
    updateTab(to)
})

onMounted(() => {
    updateTab(router.currentRoute.value)
    new horizontalScroll(tabScrollbarRef.value)
})
</script>

<style scoped lang="scss">
.dark {
    .close-icon {
        color: v-bind('config.getColorVal("headerBarTabColor")') !important;
    }
    .ba-nav-tab.active {
        .close-icon {
            color: v-bind('config.getColorVal("headerBarTabActiveColor")') !important;
        }
    }
}
.nav-tabs {
    overflow-x: auto;
    overflow-y: hidden;
    margin-right: var(--ba-main-space);
    scrollbar-width: none;

    &::-webkit-scrollbar {
        height: 5px;
    }
    &::-webkit-scrollbar-thumb {
        background: #eaeaea;
        border-radius: var(--el-border-radius-base);
        box-shadow: none;
        -webkit-box-shadow: none;
    }
    &::-webkit-scrollbar-track {
        background: v-bind('config.layout.layoutMode == "Default" ? "none":config.getColorVal("headerBarBackground")');
    }
    &:hover {
        &::-webkit-scrollbar-thumb:hover {
            background: #c8c9cc;
        }
    }
}
.ba-nav-tab {
    white-space: nowrap;
    height: 40px;
}
</style>
