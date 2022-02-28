<template>
    <div class="nav-tabs" ref="tabScrollbarRef">
        <div
            v-for="(item, idx) in tabsView"
            @click="router.push(item.path)"
            @contextmenu.prevent="onContextmenu(item, $event)"
            class="ba-nav-tab"
            :class="activeIndex == idx ? 'active' : ''"
            :ref="tabsRefs.set"
            :key="idx"
        >
            {{ item.title }}
            <transition @after-leave="selectNavTab(tabsRefs[activeIndex])" name="el-fade-in">
                <Icon v-show="tabsView.length > 1" class="close-icon" @click.stop="closeTab(item)" size="15" name="el-icon-Close" />
            </transition>
        </div>
        <div :style="activeBoxStyle" class="nav-tabs-active-box"></div>
    </div>
    <Contextmenu ref="contextmenuRef" :items="state.contextmenuItems" @contextmenuItemClick="onContextmenuItem" />
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter, onBeforeRouteUpdate, RouteLocationNormalized } from 'vue-router'
import { useState } from '/@/store/useMapper'
import { useStore } from '/@/store'
import { viewMenu } from '/@/store/interface'
import { useTemplateRefsList } from '@vueuse/core'
import type { ContextMenuItem, ContextmenuItemClickEmitArg } from '/@/components/contextmenu/interface'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import Contextmenu from '/@/components/contextmenu/index.vue'
import horizontalScroll from '/@/utils/horizontalScroll'

const route = useRoute()
const router = useRouter()
const store = useStore()

const { activeIndex, activeRoute, tabsView } = useState('navTabs', ['activeIndex', 'activeRoute', 'tabsView'])
const layoutConfig = computed(() => store.state.config.layout)

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

const onContextmenu = (menu: viewMenu, el: MouseEvent) => {
    // 禁用刷新
    state.contextmenuItems[0].disabled = route.path !== menu.path
    // 禁用关闭其他和关闭全部
    state.contextmenuItems[4].disabled = state.contextmenuItems[3].disabled = store.state.navTabs.tabsView.length == 1 ? true : false

    const { clientX, clientY } = el
    contextmenuRef.value.onShowContextmenu(menu, {
        x: clientX,
        y: clientY,
    })
}

// tab 激活状态切换
const selectNavTab = function (dom: HTMLDivElement) {
    if(!dom) {
        return false;
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
    const lastTab = tabsView.value.slice(-1)[0]
    if (lastTab) {
        router.push(lastTab.path)
    } else {
        router.push('/admin')
    }
}

const closeTab = (route: viewMenu) => {
    store.commit('navTabs/closeTab', route)
    proxy.eventBus.emit('onTabViewClose', route)
    if (activeRoute.value.path === route.path) {
        toLastTab()
    } else {
        store.commit('navTabs/setActiveRoute', activeRoute.value.path)
        nextTick(() => {
            selectNavTab(tabsRefs.value[activeIndex.value])
        })
    }

    contextmenuRef.value.onHideContextmenu()
}

const onContextmenuItem = async (item: ContextmenuItemClickEmitArg) => {
    const { name, menu } = item
    switch (name) {
        case 'refresh':
            proxy.eventBus.emit('onTabViewRefresh', menu)
            break
        case 'close':
            closeTab(menu as viewMenu)
            break
        case 'closeOther':
            store.commit('navTabs/closeTabs', menu)
            store.commit('navTabs/setActiveRoute', menu?.path)
            break
        case 'closeAll':
            store.commit('navTabs/closeTabs')
            if (route.path === '/admin/dashboard') {
                router.go(0)
            } else {
                router.push('/admin')
            }
            break
        case 'fullScreen':
            if (route.path !== menu?.path) {
                router.push(menu?.path as string)
            }
            store.commit('navTabs/setFullScreen', true)
            break
    }
}

const updateTab = function (newRoute: RouteLocationNormalized | viewMenu) {
    // 添加tab
    store.commit('navTabs/addTab', newRoute.path)
    // 激活当前tab
    store.commit('navTabs/setActiveRoute', newRoute.path)

    nextTick(() => {
        selectNavTab(tabsRefs.value[activeIndex.value])
    })
}

onBeforeRouteUpdate(async (to, from) => {
    updateTab(to)
})

onMounted(() => {
    updateTab(route)
    new horizontalScroll(tabScrollbarRef.value)
})
</script>

<style scoped lang="scss">
.nav-tabs {
    overflow-x: auto;
    overflow-y: hidden;
    margin-right: var(--main-space);
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
        background: v-bind('layoutConfig.layoutMode == "Default" ? "none":"layoutConfig.headerBarBackground"');
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
