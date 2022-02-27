<template>
    <div class="nav-bar">
        <div v-if="layoutConfig.shrink && layoutConfig.menuCollapse" class="unfold">
            <Icon @click="onMenuCollapse" name="fa fa-indent" :color="layoutConfig.menuActiveColor" size="18" />
        </div>
        <NavTabs v-if="!layoutConfig.shrink" />
        <NavMenus />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useStore } from '/@/store'
import NavTabs from '/@/layouts/backend/components/navBar/tabs.vue'
import NavMenus from '../navMenus.vue'
import { showShade } from '/@/utils/pageShade'

const store = useStore()

const layoutConfig = computed(() => store.state.config.layout)

const onMenuCollapse = () => {
    showShade('ba-aside-menu-shade', () => {
        store.commit('config/setAndCache', {
            name: 'layout.menuCollapse',
            value: true,
        })
    })
    store.commit('config/setAndCache', {
        name: 'layout.menuCollapse',
        value: false,
    })
}
</script>

<style scoped lang="scss">
.nav-bar {
    display: flex;
    height: 50px;
    width: 100%;
    background-color: v-bind('layoutConfig.headerBarBackground');
    :deep(.nav-tabs) {
        display: flex;
        height: 100%;
        position: relative;
        .ba-nav-tab {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            cursor: pointer;
            z-index: 1;
            height: 100%;
            user-select: none;
            color: v-bind('layoutConfig.headerBarTabColor');
            transition: all 0.2s;
            -webkit-transition: all 0.2s;
            .close-icon {
                padding: 2px;
                margin: 2px 0 0 4px;
            }
            .close-icon:hover {
                background: var(--color-primary-sub-0);
                color: var(--color-sub-1) !important;
                border-radius: 50%;
            }
            &.active {
                color: v-bind('layoutConfig.headerBarTabActiveColor');
            }
            &:hover {
                background-color: v-bind('layoutConfig.headerBarHoverBackground');
            }
        }
        .nav-tabs-active-box {
            position: absolute;
            height: 50px;
            background-color: v-bind('layoutConfig.headerBarTabActiveBackground');
            transition: all 0.2s;
            -webkit-transition: all 0.2s;
        }
    }
}
.unfold {
    align-self: center;
    padding-left: var(--main-space);
}
</style>
