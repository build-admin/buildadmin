<template>
    <transition name="el-zoom-in-center">
        <div
            class="el-popper is-pure is-light el-dropdown__popper ba-contextmenu"
            :style="`top: ${state.axis.y + 5}px;left: ${state.axis.x - 14}px;width:${props.width}px`"
            :key="Math.random()"
            v-show="state.show"
            aria-hidden="false"
            data-popper-placement="bottom"
        >
            <ul class="el-dropdown-menu">
                <template v-for="(item, idx) in props.items" :key="idx">
                    <li class="el-dropdown-menu__item" :class="item.disabled ? 'is-disabled' : ''" tabindex="-1" @click="onContextmenuItem(item)">
                        <Icon size="12" :name="item.icon" />
                        <span>{{ item.label }}</span>
                    </li>
                </template>
            </ul>
            <span class="el-popper__arrow" :style="{ left: `${state.arrowAxis}px` }"></span>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, reactive, toRaw } from 'vue'
import type { ContextMenuItem, Axis, ContextmenuItemClickEmitArg } from './interface'
import { RouteLocationNormalized } from 'vue-router'

// 不能使用导出的 interface vue的issue已存在，尚未解决
interface Props {
    width?: number
    items: ContextMenuItem[]
}

const props = withDefaults(defineProps<Props>(), {
    width: 150,
    items: () => [],
})

const emits = defineEmits<{
    (e: 'contextmenuItemClick', item: ContextmenuItemClickEmitArg): void
}>()

const state: {
    show: boolean
    axis: {
        x: number
        y: number
    }
    menu: RouteLocationNormalized | undefined
    arrowAxis: number
} = reactive({
    show: false,
    axis: {
        x: 0,
        y: 0,
    },
    menu: undefined,
    arrowAxis: 10,
})

const onShowContextmenu = (menu: RouteLocationNormalized, axis: Axis) => {
    state.menu = menu
    state.axis = axis
    state.show = true
}

const onContextmenuItem = (item: ContextmenuItemClickEmitArg) => {
    if (item.disabled) return
    item.menu = toRaw(state.menu)
    emits('contextmenuItemClick', item)
}

const onHideContextmenu = () => {
    state.show = false
}

defineExpose({
    onShowContextmenu,
    onHideContextmenu,
})

onMounted(() => {
    document.body.addEventListener('click', onHideContextmenu)
})
onUnmounted(() => {
    document.body.removeEventListener('click', onHideContextmenu)
})
</script>

<style scoped lang="scss">
.ba-contextmenu {
    z-index: 9999;
}
.el-popper,
.el-popper.is-light .el-popper__arrow::before {
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
    border: none;
}
.el-dropdown-menu__item {
    padding: 8px 20px;
    user-select: none;
}
.el-dropdown-menu__item .icon {
    margin-right: 5px;
}
.el-dropdown-menu__item:not(.is-disabled) {
    &:hover {
        background-color: var(--el-dropdown-menuItem-hover-fill);
        color: var(--el-dropdown-menuItem-hover-color);
        .fa {
            color: var(--el-dropdown-menuItem-hover-color) !important;
        }
    }
}
</style>
