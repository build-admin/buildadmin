<template>
    <div class="nav-bar">
        <NavTabs />
        <NavMenus />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useStore } from '/@/store'
import NavTabs from '/@/layouts/backend/components/navBar/tabs.vue'
import NavMenus from '../navMenus.vue'

const store = useStore()

const headerBarTabColor = computed(() => store.state.config.layout.headerBarTabColor)
const headerBarTabActiveColor = computed(() => store.state.config.layout.headerBarTabActiveColor)
const headerBarTabActiveBackground = computed(() => store.state.config.layout.headerBarTabActiveBackground)
</script>

<style lang="scss" scoped>
.nav-bar {
    display: flex;
    height: 50px;
    margin: 20px var(--main-space) 0 var(--main-space);
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
            user-select: none;
            opacity: 0.7;
            color: v-bind(headerBarTabColor);
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
                color: v-bind(headerBarTabActiveColor);
            }
            &:hover {
                opacity: 1;
            }
        }
        .nav-tabs-active-box {
            position: absolute;
            height: 40px;
            border-radius: var(--el-border-radius-base);
            background-color: v-bind(headerBarTabActiveBackground);
            box-shadow: var(--el-box-shadow-light);
            transition: all 0.2s;
            -webkit-transition: all 0.2s;
        }
    }
}
</style>
