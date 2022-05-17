<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold']"
            :quick-search-placeholder="'通过组名模糊搜索'"
            @action="baTable.onTableHeaderAction"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" @action="baTable.onTableAction" />

        <!-- 表单 -->
        <Form ref="formRef" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, provide } from 'vue'
import baTableClass from '/@/utils/baTable'
import { baTableApi } from '/@/api/common'
import { authGroup } from '/@/api/controllerUrls'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import Form from './form.vue'
import { defaultOptButtons } from '/@/components/table'
import { useI18n } from 'vue-i18n'

const formRef = ref()
const tableRef = ref()
const { t } = useI18n()

const baTable = new baTableClass(
    new baTableApi(authGroup),
    {
        expandAll: true,
        dblClickNotEditColumn: [undefined],
        column: [
            { type: 'selection', align: 'center' },
            { label: '组别名称', prop: 'name', align: 'left' },
            { label: '权限', prop: 'rules', align: 'center' },
            {
                label: '状态',
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { '0': 'danger', '1': 'success' },
                replaceValue: { '0': '禁用', '1': '启用' },
            },
            { label: '更新时间', prop: 'updatetime', align: 'center', width: '160', render: 'datetime' },
            { label: '创建时间', prop: 'createtime', align: 'center', width: '160', render: 'datetime' },
            {
                label: '操作',
                align: 'center',
                width: '130',
                render: 'buttons',
                buttons: defaultOptButtons(['edit', 'delete']),
            },
        ],
    },
    {
        defaultItems: {
            status: '1',
        },
    },
    {
        // 提交前
        onSubmit: () => {
            baTable.form.items!.rules = formRef.value.getCheckeds()
            baTable.form.items!.half_rules = formRef.value.getHalfCheckeds()
        },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'auth/group',
})
</script>

<style scoped lang="scss"></style>
