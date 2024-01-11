<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.summary.quick Search Fields') })"
        ></TableHeader>

        <!-- 示例核心代码(1/2) -->
        <Table :show-summary="true" :summary-method="summaryMethod" ref="tableRef"></Table>

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
import type { TableColumnCtx } from 'element-plus'

defineOptions({
    name: 'examples/table/summary',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

// 示例核心代码(2/2)
const summaryMethod = ({ columns, data }: { columns: TableColumnCtx<any>[]; data: any }) => {
    const sums: string[] = []
    columns.forEach((column, index) => {
        if (index == 0) {
            sums[index] = '计'
            return
        } else if (['1', '6', '7'].includes(index.toString())) {
            sums[index] = ' - '
            return
        }

        const values = data.map((item: any) => Number(item[column.property]))
        if (!values.every((value: any) => Number.isNaN(value))) {
            let symbol = index == 2 ? '￥' : ''
            sums[index] = `${symbol}${values.reduce((prev: any, curr: any) => {
                const value = Number(curr)
                if (!Number.isNaN(value)) {
                    return prev + curr
                } else {
                    return prev
                }
            }, 0)}`
        } else {
            sums[index] = 'N/A'
        }
    })
    return sums
}

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Summary/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.summary.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            { label: t('examples.table.summary.number1'), prop: 'number1', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('examples.table.summary.number2'), prop: 'number2', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('examples.table.summary.float1'), prop: 'float1', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('examples.table.summary.float2'), prop: 'float2', align: 'center', operator: 'RANGE', sortable: false },
            {
                label: t('examples.table.summary.create_time'),
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
        defaultItems: { number1: 0, number2: 0, float1: 0, float2: 0 },
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
