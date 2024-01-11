<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.cell.slot.quick Search Fields') })"
        ></TableHeader>

        <!-- 示例核心代码(1/3) -->
        <Table ref="tableRef">
            <template #stringSlot>
                <el-table-column label="string" align="center">
                    <template #default="scope">
                        <!-- 如何渲染由开发者随意发挥 -->
                        <div v-if="scope.row.id == 1">{{ scope.row.string }}</div>

                        <!-- 可以绑定事件 -->
                        <el-tag @click="stringTagClick(scope.row)" v-if="scope.row.id == 2">{{ scope.row.string }}</el-tag>

                        <el-input v-if="scope.row.id == 3" v-model="scope.row.string" />
                    </template>
                </el-table-column>
            </template>
        </Table>

        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { ElNotification } from 'element-plus'

defineOptions({
    name: 'examples/table/cell/slot',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const stringTagClick = (row: TableRow) => {
    ElNotification.success({
        message: row.string + '被点击了',
    })
}

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.cell.Slot/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.cell.slot.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            // 示例核心代码(2/3)
            {
                render: 'slot',
                slotName: 'stringSlot',
                operator: false,
            },
            // 示例核心代码(3/3)
            // show: false 表示不显示，仅在公共搜索中使用
            {
                show: false,
                label: t('examples.table.cell.slot.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('examples.table.cell.slot.date'), prop: 'date', align: 'center', operator: 'eq', sortable: 'custom' },
            {
                label: t('examples.table.cell.slot.address'),
                prop: 'address',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.cell.slot.code'),
                prop: 'code',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.cell.slot.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { date: null },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
