<template>
    <el-aside v-if="!tabFullScreen" :class="'layout-aside-' + layoutMode + ' ' + (shrink ? 'shrink' : '')">
        <Logo v-if="menuShowTopBar" />
        <MenuVertical />
    </el-aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Logo from './logo.vue'
import MenuVertical from './menuVertical.vue'
import { useStore } from '/@/store/index'

const store = useStore()

const tabFullScreen = computed(() => store.state.navTabs.tabFullScreen)
const menuWidth = computed(() => store.getters['config/menuWidth'])
const layoutMode = computed(() => store.state.config.layout.layoutMode)
const menuShowTopBar = computed(() => store.state.config.layout.menuShowTopBar)
const shrink = computed(() => store.state.config.layout.shrink)
</script>

<style scoped lang="scss">
.layout-aside-Default {
    background: var(--color-basic-white);
    margin: 16px 0 16px 16px;
    height: calc(100vh - 32px);
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
    overflow: hidden;
    transition: width 0.3s ease;
    width: v-bind(menuWidth);
}
.layout-aside-Classic {
    background: var(--color-basic-white);
    margin: 0px;
    height: 100vh;
    overflow: hidden;
    transition: width 0.3s ease;
    width: v-bind(menuWidth);
}
.shrink {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999999;
}
</style>
