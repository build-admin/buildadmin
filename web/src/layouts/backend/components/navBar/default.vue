<template>
    <div class="nav-bar" :class="config.layout.shrink ? 'shrink' : ''">
        <!-- 小屏设备下的展开菜单按钮 -->
        <div v-if="config.layout.shrink && config.layout.menuCollapse" class="unfold">
            <Icon @click="onMenuCollapse" name="fa fa-indent" :color="config.getColorVal('menuActiveColor')" size="18" />
        </div>
        <NavTabs v-if="!config.layout.shrink" ref="layoutNavTabsRef" />
        <NavMenus />
    </div>
</template>

<script setup lang="ts">
import { useConfig } from '/@/stores/config'
import NavTabs from '/@/layouts/backend/components/navBar/tabs.vue'
import NavMenus from '../navMenus.vue'
import { layoutNavTabsRef } from '/@/stores/refs'
import { showShade } from '/@/utils/pageShade'

const config = useConfig()

const onMenuCollapse = () => {
    showShade('ba-aside-menu-shade', () => {
        config.setLayout('menuCollapse', true)
    })
    config.setLayout('menuCollapse', false)
}
</script>

<style lang="scss" scoped>
.nav-bar {
    display: flex;
    height: 46px;
    position: relative;
    margin: var(--ba-main-space);
    margin-bottom: 0;
    :deep(.nav-tabs) {
        display: flex;
        height: 100%;
        .ba-nav-tab {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            cursor: pointer;
            z-index: 1;
            user-select: none;
            opacity: 0.7;
            color: v-bind('config.getColorVal("headerBarTabColor")');
            .close-icon {
                padding: 2px;
                margin: 2px 0 0 4px;
            }
            .close-icon:hover {
                background: var(--ba-color-primary-light);
                color: var(--el-border-color) !important;
                border-radius: 50%;
            }
            &.active {
                color: v-bind('config.getColorVal("headerBarTabActiveColor")');
            }
            &:hover {
                opacity: 1;
            }
        }
        .nav-tabs-active-box {
            position: absolute;
            height: 46px;
            border-radius: var(--el-border-radius-base);
            background-color: v-bind('config.getColorVal("headerBarTabActiveBackgroundFloating")');
            box-shadow: var(--el-box-shadow-light);
            transition: all 0.2s;
            -webkit-transition: all 0.2s;
        }
    }
}
.nav-bar.shrink {
    width: 100%;
    background-color: v-bind('config.getColorVal("headerBarBackground")');
    margin: 0;
    .unfold {
        align-self: center;
        padding-left: var(--ba-main-space);
    }
}
</style>
