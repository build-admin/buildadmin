<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'comSearch']"
            :quick-search-placeholder="'通过用户名/用户昵称模糊搜索'"
            @action="baTable.onTableHeaderAction"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" @action="baTable.onTableAction" />

        <!-- 表单 -->
        <Form />
    </div>
</template>

<script setup lang="ts">
import { ref, provide } from 'vue'
import baTableClass from '/@/utils/baTable'
import { userMoneyLog } from '/@/api/controllerUrls'
import Form from './form.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { baTableApi } from '/@/api/common'
import { useRoute } from 'vue-router'

const tableRef = ref()
const route = useRoute()
const defalutUser = route.query.user_id ?? ''

const baTable = new baTableClass(
    new baTableApi(userMoneyLog),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: 'ID', prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 70 },
            { label: '用户ID', prop: 'user_id', align: 'center', operator: '=', width: 70 },
            { label: '用户名', prop: 'user.username', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            { label: '用户昵称', prop: 'user.nickname', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            { label: '变更余额', prop: 'money', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: '变更前', prop: 'before', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: '变更后', prop: 'after', align: 'center', operator: 'RANGE', sortable: 'custom' },
            { label: '备注', prop: 'memo', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', 'show-overflow-tooltip': true },
            { label: '创建时间', prop: 'createtime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        items: {
            user_id: defalutUser,
        },
        defaultItems: {
            user_id: defalutUser,
            memo: '',
        },
    }
)

baTable.mount()
baTable.getIndex()

provide('baTable', baTable)
</script>

<style scoped lang="scss"></style>
