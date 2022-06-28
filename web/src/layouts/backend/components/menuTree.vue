<template>
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
</template>
<script setup lang="ts">
import { useConfig } from '/@/stores/config'
import type { viewMenu } from '/@/stores/interface'
import { clickMenu } from '/@/utils/router'

const config = useConfig()

interface Props {
    menus: viewMenu[]
}
const props = withDefaults(defineProps<Props>(), {
    menus: () => [],
})
</script>

<style scoped lang="scss">
.el-sub-menu .icon,
.el-menu-item .icon {
    vertical-align: middle;
    margin-right: 5px;
    width: 24px;
    text-align: center;
}
.is-active > .icon {
    color: var(--el-menu-active-color) !important;
}
.el-menu-item.is-active {
    background-color: v-bind('config.layout.menuActiveBackground');
}
</style>
