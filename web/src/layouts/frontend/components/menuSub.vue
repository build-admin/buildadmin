<template>
    <template v-for="(item, idx) in props.menus" :key="idx">
        <template v-if="!isEmpty(item.children)">
            <el-sub-menu @click="onClickSubMenu(item)" v-blur :index="`column-${item.id}`">
                <template #title>
                    <Icon v-if="showIcon && item.icon" :name="item.icon" color="var(--el-text-color-primary)" />
                    {{ item.title }}
                </template>
                <el-menu-item
                    v-for="(subItem, subIndex) in item.children"
                    :key="subIndex"
                    @click="onClickMenu(subItem)"
                    v-blur
                    :index="'column-' + subItem.id"
                    :class="subItem.name.replace(/[\/]/g, '-')"
                >
                    <Icon v-if="showIcon && subItem.icon" :name="subItem.icon" color="var(--el-text-color-primary)" />
                    <template #title>{{ subItem.title }}</template>
                </el-menu-item>
            </el-sub-menu>
        </template>
        <template v-else>
            <el-menu-item @click="onClickMenu(item)" v-blur :index="'column-' + item.id" :class="item.name.replace(/[\/]/g, '-')">
                <Icon v-if="showIcon && item.icon" :name="item.icon" color="var(--el-text-color-primary)" />
                <template #title>{{ item.title }}</template>
            </el-menu-item>
        </template>
    </template>
</template>

<script setup lang="ts">
import { isEmpty } from 'lodash-es'
import { onClickMenu } from '/@/utils/router'
import { Menus } from '/@/stores/interface/index'

interface Props {
    menus: Menus[]
    showIcon?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    menus: () => [],
    showIcon: false,
})

const onClickSubMenu = (menu: Menus) => {
    /**
     * 1、'/'表示菜单规则的 path 为空
     * 2、会员中心菜单目录不需要跳转
     */
    if (menu.path == '/' || menu.type == 'menu_dir') return
    onClickMenu(menu)
}
</script>

<style scoped lang="scss">
.el-sub-menu .icon,
.el-menu-item .icon {
    vertical-align: middle;
    margin-right: 2px;
    width: 24px;
    text-align: center;
    flex-shrink: 0;
}
.is-active > .icon {
    color: var(--el-menu-active-color) !important;
}
</style>
