<template>
    <el-select
        @focus="onFocus"
        class="remote-select"
        :loading="state.loading"
        :filterable="true"
        :remote="true"
        clearable
        :remote-method="onLogKeyword"
        v-model="state.value"
        @change="onChangeSelect"
        :multiple="multiple"
        :key="state.selectKey"
    >
        <el-option
            class="remote-select-option"
            v-for="item in state.options"
            :label="item[field]"
            :value="item[pk].toString()"
            :key="item[pk]"
        ></el-option>
        <el-pagination
            v-if="state.total"
            :currentPage="state.currentPage"
            :page-size="state.pageSize"
            class="select-pagination"
            layout="->, prev, next"
            :total="state.total"
            @current-change="onSelectCurrentPageChange"
        />
    </el-select>
</template>

<script setup lang="ts">
import { reactive, watch, onMounted } from 'vue'
import { getSelectData } from '/@/api/common'
import { uuid } from '/@/utils/random'

type valType = string | number | string[] | number[]

interface Props {
    pk?: string
    field?: string
    params?: anyObj
    multiple?: boolean
    remoteUrl: string
    modelValue: valType
}
const props = withDefaults(defineProps<Props>(), {
    pk: 'id',
    field: 'name',
    params: () => {
        return {}
    },
    remoteUrl: '',
    modelValue: '',
    multiple: false,
})

const state: {
    options: anyObj[]
    loading: boolean
    total: number
    currentPage: number
    pageSize: number
    params: anyObj
    keyword: string
    value: valType
    selectKey: string
    initializeData: boolean
} = reactive({
    options: [],
    loading: false,
    total: 0,
    currentPage: 1,
    pageSize: 10,
    params: props.params,
    keyword: '',
    value: props.modelValue,
    selectKey: uuid(),
    initializeData: false,
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: valType): void
}>()

const onChangeSelect = (val: valType) => {
    emits('update:modelValue', val)
}

const onFocus = () => {
    if (!state.initializeData) {
        getData()
    }
}

const onLogKeyword = (q: string) => {
    if (state.keyword != q) {
        state.keyword = q
        getData()
    }
}

const getData = (initValue: valType = '') => {
    state.loading = true
    state.params.page = state.currentPage
    state.params.initKey = props.pk
    state.params.initValue = initValue
    getSelectData(props.remoteUrl, state.keyword, state.params)
        .then((res) => {
            let initializeData = true
            let opts = res.data.options ? res.data.options : res.data.list
            state.options = opts
            state.total = res.data.total ?? 0
            if (initValue) {
                // 重新渲染组件,确保在赋值前,opts已加载到-兼容 modelValue 更新
                state.selectKey = uuid()
                initializeData = false
            }
            state.loading = false
            state.initializeData = initializeData
        })
        .catch((err) => {
            state.loading = false
        })
}

const onSelectCurrentPageChange = (val: number) => {
    state.currentPage = val
    getData()
}

const initDefaultValue = () => {
    if (state.value) {
        getData(state.value)
    }
}

onMounted(() => {
    initDefaultValue()
})

watch(
    () => props.modelValue,
    (newVal) => {
        if (state.value != newVal) {
            state.value = newVal
            initDefaultValue()
        }
    }
)
</script>

<style scoped lang="scss">
.remote-select {
    width: 100%;
}
.remote-select-option {
    white-space: pre;
}
</style>
