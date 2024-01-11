<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.headerBtn.quick Search Fields') })"
        >
            <!-- 示例核心代码(1/1) -->
            <el-tooltip content="全选/取消全选" placement="top">
                <el-button @click="onSelectAll" v-blur class="table-header-operate" type="success">
                    <Icon name="fa fa-check-square-o" />
                    <span class="table-header-operate-text">全选/取消全选</span>
                </el-button>
            </el-tooltip>
        </TableHeader>

        <Table ref="tableRef"></Table>

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
    name: 'examples/table/headerBtn',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const onSelectAll = () => {
    let ref = tableRef.value.getRef()
    ref.toggleAllSelection()
}

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.HeaderBtn/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.headerBtn.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.headerBtn.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('examples.table.headerBtn.number'), prop: 'number', align: 'center', operator: 'RANGE', sortable: false },
            {
                label: t('examples.table.headerBtn.create_time'),
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
        defaultItems: { number: 0 },
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
.table-header-operate {
    margin-left: 12px;
}
</style>
