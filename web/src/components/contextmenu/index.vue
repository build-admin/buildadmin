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
                    <li class="el-dropdown-menu__item" :class="item.disabled ? 'is-disabled' : ''" tabindex="-1" @click="onMenuItemClick(item)">
                        <Icon size="12" :name="item.icon" />
                        <span>{{ item.label }}</span>
                    </li>
                </template>
            </ul>
            <span v-if="state.showArrow" class="el-popper__arrow" :style="{ left: `${state.arrowAxis}px` }"></span>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { useEventListener } from '@vueuse/core'
import { reactive, toRaw } from 'vue'
import type { Axis, ContextMenuItemClickEmitArg, Props } from './interface'
import { SYSTEM_ZINDEX } from '/@/stores/constant/common'

const props = withDefaults(defineProps<Props>(), {
    width: 150,
    items: () => [],
})

const emits = defineEmits<{
    // 菜单项被点击
    (e: 'menuClick', item: ContextMenuItemClickEmitArg): void
    // 右击菜单隐藏回调，它可能在组件内部被触发，所以提供 emit
    (e: 'hideContextmenu'): void
}>()

const state: {
    show: boolean
    axis: {
        x: number
        y: number
    }
    sourceData: any
    showArrow: boolean
    arrowAxis: number
} = reactive({
    show: false,
    axis: {
        x: 0,
        y: 0,
    },
    sourceData: null,
    showArrow: true,
    arrowAxis: 10,
})

/**
 * 删除事件监听的函数
 */
const removeEventListenerFn: Record<string, () => void> = {
    click: () => {},
    scroll: () => {},
    keydown: () => {},
}

/**
 * 显示右击菜单
 * @param sourceData 来源数据，开发者可于右击菜单项被点击的事件中访问到它
 * @param axis 右击坐标信息
 */
const onShowContextmenu = (sourceData: any, axis: Axis) => {
    state.showArrow = true
    state.sourceData = sourceData

    const yOffset = document.documentElement.clientHeight - axis.y - (props.items.length * 40 + 20)
    const xOffset = document.documentElement.clientWidth - axis.x - (props.width + 20)
    if (yOffset < 0) {
        axis.y += yOffset
        state.showArrow = false
    }
    if (xOffset < 0) {
        axis.x += xOffset
        state.showArrow = false
    }

    state.axis = axis
    state.show = true

    removeEventListenerFn.click = useEventListener(document, 'click', onHideContextmenu)
    removeEventListenerFn.scroll = useEventListener(document, 'scroll', onHideContextmenu)
    removeEventListenerFn.keydown = useEventListener(document, 'keydown', (e) => {
        if (e.key === 'Escape') {
            onHideContextmenu()
        }
    })
}

/**
 * 隐藏右击菜单
 */
const onHideContextmenu = () => {
    state.show = false

    for (const key in removeEventListenerFn) {
        removeEventListenerFn[key]()
    }

    emits('hideContextmenu')
}

const onMenuItemClick = (item: ContextMenuItemClickEmitArg) => {
    if (item.disabled) return
    item.sourceData = toRaw(state.sourceData)
    emits('menuClick', item)
}

defineExpose({
    onShowContextmenu,
    onHideContextmenu,
})
</script>

<style scoped lang="scss">
.ba-contextmenu {
    position: fixed;
    z-index: v-bind('SYSTEM_ZINDEX');
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
