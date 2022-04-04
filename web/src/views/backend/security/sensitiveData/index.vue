<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="'通过控制器名模糊搜索'"
            @action="baTable.onTableHeaderAction"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" @action="baTable.onTableAction" />

        <!-- 表单 -->
        <Form ref="formRef" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, provide } from 'vue'
import { sensitiveDataClass } from './index'
import { securitySensitiveData } from '/@/api/controllerUrls'
import Form from './form.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'

const tableRef = ref()
const formRef = ref()
const baTable = new sensitiveDataClass(
    new baTableApi(securitySensitiveData),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: 'ID', prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 70 },
            { label: '规则名称', prop: 'name', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            { label: '控制器', prop: 'controller', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            { label: '数据表', prop: 'data_table', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            { label: '数据表主键', prop: 'primary_key', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 100 },
            { label: '敏感字段', prop: 'data_fields', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', render: 'tags' },
            {
                label: '状态',
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { '0': 'danger', '1': 'success' },
                replaceValue: { '0': '禁用', '1': '修改监控中' },
            },
            { label: '更新时间', prop: 'updatetime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: '创建时间', prop: 'createtime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            {
                label: '操作',
                align: 'center',
                width: '130',
                render: 'buttons',
                buttons: defaultOptButtons(['edit', 'delete']),
                operator: false,
            },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {
            status: '1',
        },
    },
    {
        // 提交前
        onSubmit: () => {
            baTable.form.items!.fields = formRef.value.getDataFields()
        },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.form.extend = Object.assign(baTable.form.extend, {
        parentRef: formRef.value,
    })
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'security/dataRecycle',
})
</script>

<style scoped lang="scss"></style>
