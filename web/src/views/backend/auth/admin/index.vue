<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />
        <!-- 表格顶部菜单 -->
        <TableHeader
            :field="baTable.table.column"
            :buttons="['refresh', 'edit', 'delete', 'comSearch']"
            :enable-batch-opt="baTable.table.selection!.length > 0 ? true : false"
            :quick-search-placeholder="'通过用户名和昵称模糊搜索'"
            @action="baTable.onTableHeaderAction"
        />
        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table
            ref="tableRef"
            :data="baTable.table.data"
            :field="baTable.table.column"
            :row-key="baTable.table.pk"
            :total="baTable.table.total"
            :loading="baTable.table.loading"
            @action="baTable.onTableAction"
            @row-dblclick="baTable.onTableDblclick"
        />
        <!-- 表单 -->
        <Form :ba-table="baTable" />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import baTableClass from '/@/utils/baTable'
import { authAdmin } from '/@/api/controllerUrls'
import Form from './form.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'

const tableRef = ref()
const baTable = new baTableClass(new baTableApi(authAdmin), {
    column: [
        { type: 'selection', align: 'center', operator: false },
        { label: 'ID', prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 70 },
        { label: '用户名', prop: 'username', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        { label: '昵称', prop: 'nickname', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        { label: '头像', prop: 'avatar', align: 'center', render: 'image', operator: false },
        { label: '邮箱', prop: 'email', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        { label: '手机号', prop: 'mobile', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
        { label: '最后登录', prop: 'lastlogintime', align: 'center', render: 'datetime', operator: 'RANGE', width: 160 },
        {
            label: '状态',
            prop: 'status',
            align: 'center',
            render: 'tag',
            custom: { '0': 'danger', '1': 'success' },
            replaceValue: { '0': '禁用', '1': '启用' },
        },
        {
            label: '操作',
            align: 'center',
            width: '100',
            render: 'buttons',
            buttons: defaultOptButtons(['edit', 'delete']),
            operator: false,
        },
    ],
    dblClickNotEditColumn: [undefined, 'status'],
})

baTable.mount()
baTable.getIndex()
</script>

<style scoped lang="scss"></style>
