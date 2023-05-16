<template>
    <div>
        <template v-for="(item, idx) in props.menus" :key="idx">
            <template v-if="!isEmpty(item.children)">
                <el-sub-menu v-blur :index="`column-${item.id}`">
                    <template #title>{{ item.title }}</template>
                    <el-menu-item
                        v-for="(subItem, subIndex) in item.children"
                        :key="subIndex"
                        @click="onClickMenu(subItem)"
                        v-blur
                        :index="'column-' + subItem.id"
                    >
                        {{ subItem.title }}
                    </el-menu-item>
                </el-sub-menu>
            </template>
            <template v-else>
                <el-menu-item @click="onClickMenu(item)" v-blur :index="'column-' + item.id">
                    {{ item.title }}
                </el-menu-item>
            </template>
        </template>
    </div>
</template>

<script setup lang="ts">
import { isEmpty } from 'lodash-es'
import { Menus } from '/@/stores/interface'
import { onClickMenu } from '/@/utils/router'

interface Props {
    menus: Menus[]
}

const props = withDefaults(defineProps<Props>(), {
    menus: () => [],
})
</script>

<style scoped lang="scss"></style>
