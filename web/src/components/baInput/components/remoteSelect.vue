<template>
    <div class="w100">
        <!-- el-select 的远程下拉只在有搜索词时，才会加载数据（显示出 option 列表） -->
        <!-- 使用 el-popover 在无数据/无搜索词时，显示一个无数据的提醒 -->
        <el-popover
            width="100%"
            placement="bottom"
            popper-class="remote-select-popper"
            :visible="state.focusStatus && !state.loading && !state.keyword && !state.options.length"
            :teleported="false"
            :content="$t('utils.No data')"
            :hide-after="0"
        >
            <template #reference>
                <el-select
                    ref="selectRef"
                    class="w100"
                    remote
                    clearable
                    filterable
                    automatic-dropdown
                    remote-show-suffix
                    v-model="state.value"
                    :loading="state.loading"
                    :disabled="props.disabled || !state.initializeFlag"
                    @blur="onBlur"
                    @focus="onFocus"
                    @clear="onClear"
                    @change="onChangeSelect"
                    @keydown.esc.capture="onKeyDownEsc"
                    :remote-method="onRemoteMethod"
                    v-bind="$attrs"
                >
                    <el-option
                        class="remote-select-option"
                        v-for="item in state.options"
                        :label="item[field]"
                        :value="item[state.primaryKey].toString()"
                        :key="item[state.primaryKey]"
                    >
                        <el-tooltip placement="right" effect="light" v-if="!isEmpty(tooltipParams)">
                            <template #content>
                                <p v-for="(tooltipParam, key) in tooltipParams" :key="key">{{ key }}: {{ item[tooltipParam] }}</p>
                            </template>
                            <div>{{ item[field] }}</div>
                        </el-tooltip>
                    </el-option>
                    <template v-if="state.total && props.pagination" #footer>
                        <el-pagination
                            :currentPage="state.currentPage"
                            :page-size="state.pageSize"
                            :pager-count="5"
                            class="select-pagination"
                            :layout="props.paginationLayout"
                            :total="state.total"
                            @current-change="onSelectCurrentPageChange"
                            :size="config.layout.shrink ? 'small' : 'default'"
                        />
                    </template>
                </el-select>
            </template>
        </el-popover>
    </div>
</template>

<script lang="ts" setup>
import type { ElSelect } from 'element-plus'
import { debounce, isEmpty } from 'lodash-es'
import { computed, getCurrentInstance, nextTick, onMounted, onUnmounted, reactive, toRaw, useAttrs, useTemplateRef, watch } from 'vue'
import { InputAttr } from '../index'
import { getSelectData } from '/@/api/common'
import { useConfig } from '/@/stores/config'
import { getArrayKey } from '/@/utils/common'
import { shortUuid } from '/@/utils/random'

const attrs = useAttrs()
const config = useConfig()
const selectRef = useTemplateRef('selectRef')
type ElSelectProps = Omit<Partial<InstanceType<typeof ElSelect>['$props']>, 'modelValue'>
type valueTypes = string | number | string[] | number[]

interface Props extends /* @vue-ignore */ ElSelectProps {
    pk?: string
    field?: string
    params?: anyObj
    remoteUrl: string
    modelValue: valueTypes | null
    pagination?: boolean
    tooltipParams?: anyObj
    paginationLayout?: string
    labelFormatter?: (optionData: anyObj, optionKey: string) => string
    // 按下 ESC 键时直接使下拉框脱焦（默认是清理搜索词或关闭下拉面板，并且不会脱焦，造成 dialog 的按下 ESC 关闭失效）
    escBlur?: boolean
}
const props = withDefaults(defineProps<Props>(), {
    pk: 'id',
    field: 'name',
    params: () => {
        return {}
    },
    remoteUrl: '',
    modelValue: '',
    tooltipParams: () => {
        return {}
    },
    pagination: true,
    paginationLayout: 'total, ->, prev, pager, next',
    disabled: false,
    escBlur: true,
})

/**
 * 点击清空按钮后的值，同时也是缺省值‌
 */
const valueOnClear = computed(() => {
    let valueOnClear = attrs.valueOnClear as InputAttr['valueOnClear']
    if (valueOnClear === undefined) {
        valueOnClear = attrs.multiple ? () => [] : () => null
    }
    return typeof valueOnClear == 'function' ? valueOnClear() : valueOnClear
})

/**
 * 被认为是空值的值列表
 */
const emptyValues = computed(() => (attrs.emptyValues as InputAttr['emptyValues']) || [null, undefined, ''])

const state: {
    // 主表字段名(不带表别名)
    primaryKey: string
    options: anyObj[]
    loading: boolean
    total: number
    currentPage: number
    pageSize: number
    params: anyObj
    keyword: string
    value: valueTypes
    initializeFlag: boolean
    optionValidityFlag: boolean
    focusStatus: boolean
} = reactive({
    primaryKey: props.pk,
    options: [],
    loading: false,
    total: 0,
    currentPage: 1,
    pageSize: 10,
    params: props.params,
    keyword: '',
    value: valueOnClear.value,
    initializeFlag: false,
    optionValidityFlag: false,
    focusStatus: false,
})

let io: IntersectionObserver | null = null
const instance = getCurrentInstance()

const emits = defineEmits<{
    (e: 'update:modelValue', value: valueTypes): void
    (e: 'row', value: any): void
}>()

const onChangeSelect = (val: valueTypes) => {
    val = updateValue(val)
    if (typeof instance?.vnode.props?.onRow == 'function') {
        if (typeof val == 'number' || typeof val == 'string') {
            const dataKey = getArrayKey(state.options, state.primaryKey, '' + val)
            emits('row', dataKey !== false ? toRaw(state.options[dataKey]) : {})
        } else {
            const valueArr = []
            for (const key in val) {
                const dataKey = getArrayKey(state.options, state.primaryKey, '' + val[key])
                if (dataKey !== false) {
                    valueArr.push(toRaw(state.options[dataKey]))
                }
            }
            emits('row', valueArr)
        }
    }
}

const onKeyDownEsc = (e: KeyboardEvent) => {
    if (props.escBlur) {
        e.stopPropagation()
        selectRef.value?.blur()
    }
}

const onFocus = () => {
    state.focusStatus = true
    if (!state.optionValidityFlag) {
        getData()
    }
}

const onClear = () => {
    // 点击清理按钮后，内部 input 呈聚焦状态，但选项面板不会展开，特此处理（element-plus@2.9.1）
    nextTick(() => {
        selectRef.value?.blur()
        selectRef.value?.focus()
    })
}

const onBlur = () => {
    state.keyword = ''
    state.focusStatus = false
}

const onRemoteMethod = (q: string) => {
    if (state.keyword != q) {
        state.keyword = q
        state.currentPage = 1
        getData()
    }
}

const getData = debounce((initValue: valueTypes = '') => {
    state.loading = true
    state.params.page = state.currentPage
    state.params.initKey = props.pk
    state.params.initValue = initValue
    getSelectData(props.remoteUrl, state.keyword, state.params)
        .then((res) => {
            let opts = res.data.options ? res.data.options : res.data.list
            if (typeof props.labelFormatter === 'function') {
                for (const key in opts) {
                    opts[key][props.field] = props.labelFormatter(opts[key], key)
                }
            }
            state.options = opts
            state.total = res.data.total ?? 0
            state.optionValidityFlag = state.keyword || (typeof initValue === 'object' ? !isEmpty(initValue) : initValue) ? false : true
        })
        .finally(() => {
            state.loading = false
            state.initializeFlag = true
        })
}, 100)

const onSelectCurrentPageChange = (val: number) => {
    state.currentPage = val
    getData()
}

const updateValue = (newVal: any) => {
    if (emptyValues.value.includes(newVal)) {
        state.value = valueOnClear.value
    } else {
        state.value = newVal

        // number[] 转 string[] 确保默认值能够选中
        if (typeof state.value === 'object') {
            for (const key in state.value) {
                state.value[key] = '' + state.value[key]
            }
        } else if (typeof state.value === 'number') {
            state.value = '' + state.value
        }
    }
    emits('update:modelValue', state.value)
    return state.value
}

onMounted(() => {
    // 避免两个远程下拉组件共存时，可能带来的重复请求自动取消
    state.params.uuid = shortUuid()

    // 去除主键中的表名
    let pkArr = props.pk.split('.')
    state.primaryKey = pkArr[pkArr.length - 1]

    // 初始化值
    updateValue(props.modelValue)
    getData(state.value)

    setTimeout(() => {
        if (window?.IntersectionObserver) {
            io = new IntersectionObserver((entries) => {
                for (const key in entries) {
                    if (!entries[key].isIntersecting) selectRef.value?.blur()
                }
            })
            if (selectRef.value?.$el instanceof Element) {
                io.observe(selectRef.value.$el)
            }
        }
    }, 500)
})

onUnmounted(() => {
    io?.disconnect()
})

watch(
    () => props.modelValue,
    (newVal) => {
        /**
         * 防止 number 到 string 的类型转换触发默认值多次初始化
         * 相当于忽略数据类型进行比较 [1, 2] == ['1', '2']
         */
        if (getString(state.value) != getString(newVal)) {
            updateValue(newVal)
            getData(state.value)
        }
    }
)

const getString = (val: valueTypes | null) => {
    // 确保 [] 和 '' 的返回值不一样
    return `${typeof val}:${String(val)}`
}

const getRef = () => {
    return selectRef.value
}

const focus = () => {
    selectRef.value?.focus()
}

const blur = () => {
    selectRef.value?.blur()
}

defineExpose({
    blur,
    focus,
    getRef,
})
</script>

<style scoped lang="scss">
:deep(.remote-select-popper) {
    color: var(--el-text-color-secondary);
    font-size: 12px;
    text-align: center;
}
.remote-select-option {
    white-space: pre;
}
</style>
