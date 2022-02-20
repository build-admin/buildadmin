<template>
    <template v-for="menu in menus">
        <template v-if="menu.children && menu.children.length > 0">
            <el-sub-menu :index="menu.path" :key="menu.path">
                <template #title>
                    <Icon :color="menuColor" :name="menu.icon ? menu.icon : defaultIcon" />
                    <span>{{ menu.title ? menu.title : $t('noTitle') }}</span>
                </template>
                <menu-tree :menus="menu.children"></menu-tree>
            </el-sub-menu>
        </template>
        <template v-else>
            <el-menu-item v-if="menu.type == 'tab'" :index="menu.path" :key="menu.path">
                <Icon :color="menuColor" :name="menu.icon ? menu.icon : defaultIcon" />
                <span>{{ menu.title ? menu.title : $t('noTitle') }}</span>
            </el-menu-item>
            <el-menu-item v-if="menu.type == 'link'" index="" :key="menu.path" @click="onLink(menu.path)">
                <Icon :color="menuColor" :name="menu.icon ? menu.icon : defaultIcon" />
                <span>{{ menu.title ? menu.title : $t('noTitle') }}</span>
            </el-menu-item>
            <el-menu-item v-if="menu.type == 'iframe'" :index="menu.path" :key="menu.path">
                <Icon :color="menuColor" :name="menu.icon ? menu.icon : defaultIcon" />
                <span>{{ menu.title ? menu.title : $t('noTitle') }}</span>
            </el-menu-item>
        </template>
    </template>
</template>
<script setup lang="ts">
import { computed } from 'vue'
import { useStore } from '/@/store'
import type { viewMenu } from '/@/store/interface'

const store = useStore()

interface Props {
    menus: viewMenu[]
}
const props = withDefaults(defineProps<Props>(), {
    menus: () => [],
})

const defaultIcon = computed(() => store.state.config.layout.menuDefaultIcon)
const menuColor = computed(() => store.state.config.layout.menuColor)
const menuActiveBackground = computed(() => store.state.config.layout.menuActiveBackground)

const onLink = (url: string) => {
    window.open(url, '_blank')
}
</script>

<style scoped lang="scss">
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
    background-color: v-bind(menuActiveBackground);
}
</style>
