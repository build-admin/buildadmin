<template>
    <el-select @focus="getData('')" class="remote-select" :loading="state.loading" :filterable="true" :remote="true" :remote-method="getData">
        <el-option class="remote-select-option" v-for="item in state.options" :label="item[field]" :value="item[pk]"></el-option>
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
} = reactive({
    options: [],
    loading: false,
})

const getData = (q: string) => {
    state.loading = true
    getSelectData(props.remoteUrl, q, props.params)
        .then((res) => {
            state.loading = false
            state.options = res.data.options
        })
        .catch((err) => {
            state.loading = false
        })
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
