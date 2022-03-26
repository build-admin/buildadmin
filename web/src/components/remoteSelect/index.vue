<template>
    <el-select
        @focus="getData"
        class="remote-select"
        :loading="state.loading"
        :filterable="true"
        :remote="true"
        clearable
        :remote-method="onLogKeyword"
    >
        <el-option class="remote-select-option" v-for="item in state.options" :label="item[field]" :value="item[pk]"></el-option>
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
import { reactive } from 'vue'
import { getSelectData } from '/@/api/common'

interface Props {
    pk?: string
    field?: string
    params?: anyObj
    remoteUrl: string
}
const props = withDefaults(defineProps<Props>(), {
    pk: 'id',
    field: 'name',
    params: () => {
        return {}
    },
    remoteUrl: '',
})

const state: {
    options: anyObj[]
    loading: boolean
    total: number
    currentPage: number
    pageSize: number
    params: anyObj
    keyword: string
} = reactive({
    options: [],
    loading: false,
    total: 0,
    currentPage: 1,
    pageSize: 10,
    params: props.params,
    keyword: '',
})

const onLogKeyword = (q: string) => {
    state.keyword = q
    getData()
}

const getData = () => {
    state.loading = true
    state.params.page = state.currentPage
    getSelectData(props.remoteUrl, state.keyword, state.params)
        .then((res) => {
            state.loading = false
            if (res.data.options) {
                state.options = res.data.options
                state.total = res.data.total ?? 0
            } else {
                state.options = res.data.list
                state.total = res.data.total
            }
        })
        .catch((err) => {
            state.loading = false
        })
}

const onSelectCurrentPageChange = (val: number) => {
    state.currentPage = val
    getData()
}
</script>

<style scoped lang="scss">
.remote-select {
    width: 100%;
}
.remote-select-option {
    white-space: pre;
}
</style>
