<template>
    <el-aside v-if="!navTabs.state.tabFullScreen" :class="['layout-aside-' + config.layout.layoutMode, config.layout.shrink ? 'shrink' : '']">
        <Logo v-if="config.layout.menuShowTopBar && config.layout.layoutMode != 'LeftSplit'" />

        <MenuVerticalChildren v-if="config.layout.layoutMode == 'Double'" />
        <MenuLeftSplit v-else-if="config.layout.layoutMode == 'LeftSplit'" />
        <MenuVertical v-else />

        <AsideFooterToolbar v-if="['Default', 'Classic', 'Double'].includes(config.layout.layoutMode)" />
    </el-aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import AsideFooterToolbar from '/@/layouts/backend/components/asideToolbar/footer.vue'
import Logo from '/@/layouts/backend/components/logo.vue'
import MenuLeftSplit from '/@/layouts/backend/components/menus/menuLeftSplit.vue'
import MenuVertical from '/@/layouts/backend/components/menus/menuVertical.vue'
import MenuVerticalChildren from '/@/layouts/backend/components/menus/menuVerticalChildren.vue'
import { useConfig } from '/@/stores/config'
import { SYSTEM_ZINDEX } from '/@/stores/constant/common'
import { useNavTabs } from '/@/stores/navTabs'

defineOptions({
    name: 'layout/aside',
})

const config = useConfig()
const navTabs = useNavTabs()
const menuWidth = computed(() => config.menuWidth())
</script>

<style scoped lang="scss">
.layout-aside-Default,
.layout-aside-LeftSplit,
.layout-aside-Classic,
.layout-aside-Double {
    width: v-bind(menuWidth);
}
.layout-aside-Default:not(.shrink),
.layout-aside-LeftSplit:not(.shrink) {
    background: var(--ba-bg-color-overlay);
    margin: var(--ba-main-space) 0 var(--ba-main-space) var(--ba-main-space);
    height: calc(100% - 32px);
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
    overflow: hidden;
    transition: width 0.3s ease;
}
.layout-aside-Default.shrink,
.layout-aside-LeftSplit.shrink,
.layout-aside-Classic,
.layout-aside-Double {
    background: var(--ba-bg-color-overlay);
    margin: 0;
    height: 100%;
    overflow: hidden;
    transition: width 0.3s ease;
}
.shrink {
    position: fixed;
    top: 0;
    left: 0;
    z-index: v-bind('SYSTEM_ZINDEX');
}
</style>
