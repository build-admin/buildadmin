<template>
    <el-popover :placement="placement" trigger="focus" :hide-after="0" :width="state.selectorWidth" :visible="state.popoverVisible">
        <div @mouseover.stop="state.iconSelectorMouseover = true" @mouseout.stop="state.iconSelectorMouseover = false" class="icon-selector">
            <div class="icon-selector-box">
                <div class="selector-header">
                    <div class="selector-title">{{ title ? title : $t('utils.Please select an icon') }}</div>
                    <div class="selector-tab">
                        <span
                            :title="'Element Puls ' + $t('utils.Icon')"
                            @click="onChangeTab('ele')"
                            :class="state.iconType == 'ele' ? 'active' : ''"
                        >
                            ele
                        </span>
                        <span
                            :title="'Font Awesome ' + $t('utils.Icon')"
                            @click="onChangeTab('awe')"
                            :class="state.iconType == 'awe' ? 'active' : ''"
                        >
                            awe
                        </span>
                        <span :title="$t('utils.Ali iconcont Icon')" @click="onChangeTab('ali')" :class="state.iconType == 'ali' ? 'active' : ''">
                            ali
                        </span>
                        <span :title="$t('utils.Local icon title')" @click="onChangeTab('local')" :class="state.iconType == 'local' ? 'active' : ''">
                            local
                        </span>
                    </div>
                </div>
                <el-scrollbar class="selector-body">
                    <div v-if="renderFontIconNames.length > 0">
                        <div class="icon-selector-item" :title="item" @click="onIcon(item)" v-for="(item, key) in renderFontIconNames" :key="key">
                            <Icon :name="item" />
                        </div>
                    </div>
                </el-scrollbar>
            </div>
        </div>
        <template #reference>
            <el-input
                v-model="state.inputValue"
                :size="size"
                :disabled="disabled"
                :placeholder="$t('Search') + $t('utils.Icon')"
                ref="selectorInput"
                @focus="onInputFocus"
                @blur="onInputBlur"
                :class="'size-' + size"
            >
                <template #prepend>
                    <div class="icon-prepend">
                        <Icon :key="'icon' + state.iconKey" :name="state.prependIcon ? state.prependIcon : state.defaultModelValue" />
                        <div v-if="showIconName" class="name">{{ state.prependIcon ? state.prependIcon : state.defaultModelValue }}</div>
                    </div>
                </template>
                <template #append>
                    <Icon @click="onInputRefresh" name="el-icon-RefreshRight" />
                </template>
            </el-input>
        </template>
    </el-popover>
</template>

<script setup lang="ts">
import { useEventListener } from '@vueuse/core'
import type { Placement } from 'element-plus'
import { computed, nextTick, onMounted, reactive, useTemplateRef, watch } from 'vue'
import { getAwesomeIconfontNames, getElementPlusIconfontNames, getIconfontNames, getLocalIconfontNames } from '/@/utils/iconfont'

type IconType = 'ele' | 'awe' | 'ali' | 'local'

interface Props {
    size?: 'default' | 'small' | 'large'
    disabled?: boolean
    title?: string
    type?: IconType
    placement?: Placement
    modelValue?: string
    showIconName?: boolean
}
const props = withDefaults(defineProps<Props>(), {
    size: 'default',
    disabled: false,
    title: '',
    type: 'ele',
    placement: 'bottom',
    modelValue: '',
    showIconName: false,
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
    (e: 'change', value: string): void
}>()

const selectorInput = useTemplateRef('selectorInput')
const state: {
    iconType: IconType
    selectorWidth: number
    popoverVisible: boolean
    inputFocus: boolean
    iconSelectorMouseover: boolean
    fontIconNames: string[]
    inputValue: string
    prependIcon: string
    defaultModelValue: string
    iconKey: number
} = reactive({
    iconType: props.type,
    selectorWidth: 0,
    popoverVisible: false,
    inputFocus: false,
    iconSelectorMouseover: false,
    fontIconNames: [],
    inputValue: '',
    prependIcon: props.modelValue,
    defaultModelValue: props.modelValue || 'fa fa-circle-o',
    iconKey: 0, // 给icon标签准备个key，以随时使用 h 函数重新生成元素
})

const onInputFocus = () => {
    state.inputFocus = state.popoverVisible = true
}
const onInputBlur = () => {
    state.inputFocus = false
    state.popoverVisible = state.iconSelectorMouseover
}
const onInputRefresh = () => {
    state.iconKey++
    state.prependIcon = state.defaultModelValue
    state.inputValue = ''
    emits('update:modelValue', state.defaultModelValue)
    emits('change', state.defaultModelValue)
}
const onChangeTab = (name: IconType) => {
    state.iconType = name
    state.fontIconNames = []
    if (name == 'ele') {
        getElementPlusIconfontNames().then((res) => {
            state.fontIconNames = res
        })
    } else if (name == 'awe') {
        getAwesomeIconfontNames().then((res) => {
            state.fontIconNames = res.map((name) => `fa ${name}`)
        })
    } else if (name == 'ali') {
        getIconfontNames().then((res) => {
            state.fontIconNames = res.map((name) => `iconfont ${name}`)
        })
    } else if (name == 'local') {
        getLocalIconfontNames().then((res) => {
            state.fontIconNames = res
        })
    }
}
const onIcon = (icon: string) => {
    state.iconSelectorMouseover = state.popoverVisible = false
    state.iconKey++
    state.prependIcon = icon
    state.inputValue = ''
    emits('update:modelValue', icon)
    emits('change', icon)
    nextTick(() => {
        selectorInput.value?.blur()
    })
}

const renderFontIconNames = computed(() => {
    if (!state.inputValue) return state.fontIconNames

    let inputValue = state.inputValue.trim().toLowerCase()
    return state.fontIconNames.filter((icon: string) => {
        if (icon.toLowerCase().indexOf(inputValue) !== -1) {
            return icon
        }
    })
})

// 获取 input 的宽度
const getInputWidth = () => {
    nextTick(() => {
        state.selectorWidth = selectorInput.value?.$el.offsetWidth < 260 ? 260 : selectorInput.value?.$el.offsetWidth
    })
}

const popoverVisible = () => {
    state.popoverVisible = state.inputFocus || state.iconSelectorMouseover ? true : false
}

watch(
    () => props.modelValue,
    () => {
        state.iconKey++
        if (props.modelValue != state.prependIcon) state.defaultModelValue = props.modelValue
        if (props.modelValue == '') state.defaultModelValue = 'fa fa-circle-o'
        state.prependIcon = props.modelValue
    }
)

/**
 * 1. 图标选择面板一旦显示就监听 document 的点击事件
 * 2. 点击后输入框和面板会失去焦点，面板将自动隐藏
 * 3. 面板隐藏后删除点击事件监听
 */
let removeClickHidePopoverListenerFn = () => {}
watch(
    () => state.popoverVisible,
    () => {
        if (state.popoverVisible) {
            removeClickHidePopoverListenerFn = useEventListener(document, 'click', popoverVisible)
        } else {
            removeClickHidePopoverListenerFn()
        }
    }
)

onMounted(() => {
    getInputWidth()
    getElementPlusIconfontNames().then((res) => {
        state.fontIconNames = res
    })
})
</script>

<style scoped lang="scss">
.size-small {
    height: 24px;
}
.size-large {
    height: 40px;
}
.size-default {
    height: 32px;
}
.icon-prepend {
    display: flex;
    align-items: center;
    justify-content: center;
    .name {
        padding-left: 5px;
    }
}
.selector-header {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}
.selector-tab {
    margin-left: auto;
    span {
        padding: 0 5px;
        cursor: pointer;
        user-select: none;
        &.active,
        &:hover {
            color: var(--el-color-primary);
            text-decoration: underline;
        }
    }
}
.selector-body {
    height: 250px;
}
.icon-selector-item {
    display: inline-block;
    padding: 10px 10px 6px 10px;
    margin: 3px;
    border: 1px solid var(--ba-border-color);
    border-radius: var(--el-border-radius-base);
    cursor: pointer;
    font-size: 18px;
    .icon {
        height: 18px;
        width: 18px;
    }
    &:hover {
        border: 1px solid var(--el-color-primary);
    }
}
:deep(.el-input-group__prepend) {
    padding: 0 10px;
}
:deep(.el-input-group__append) {
    padding: 0 10px;
}
</style>
