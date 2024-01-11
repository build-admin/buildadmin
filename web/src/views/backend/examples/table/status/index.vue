<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" type="info" show-icon>
            <template #default>
                <div class="ba-markdown">
                    <div>可将表格内容<code>highlight</code>显示，方便区分「成功、信息、警告、危险」等内容。</div>
                </div>
            </template>
        </el-alert>

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.status.quick Search Fields') })"
        ></TableHeader>

        <!-- 示例核心代码(1/2) -->
        <Table :stripe="false" :row-class-name="tableRowClassName" ref="tableRef"></Table>

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

defineOptions({
    name: 'examples/table/status',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

// 示例核心代码(2/2)
// row 参数前缀 _ 为避免编辑器提示变量未使用，若实际使用了，可去除 _
const tableRowClassName = ({ _row, rowIndex }: { _row: TableRow; rowIndex: number }) => {
    if (rowIndex === 1) {
        return 'warning-row'
    } else if (rowIndex === 3) {
        return 'success-row'
    }
    return 'test'
}

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Status/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.status.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.status.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('examples.table.status.number'), prop: 'number', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('examples.table.status.float'), prop: 'float', align: 'center', operator: 'RANGE', sortable: false },
            { label: t('examples.table.status.date'), prop: 'date', align: 'center', operator: 'eq', sortable: 'custom' },
            {
                label: t('examples.table.status.update_time'),
                prop: 'update_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('examples.table.status.datetime'), prop: 'datetime', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            {
                label: t('examples.table.status.create_time'),
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
        defaultItems: { number: 0, float: 0, date: null, datetime: null },
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

<style scoped lang="scss">
:deep(.el-table) .warning-row {
    --el-table-tr-bg-color: var(--el-color-warning-light-7);
}
:deep(.el-table) .success-row {
    --el-table-tr-bg-color: var(--el-color-success-light-7);
}
</style>
