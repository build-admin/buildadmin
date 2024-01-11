<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.dialog2.quick Search Fields') })"
        ></TableHeader>

        <Table ref="tableRef">
            <!-- 示例核心代码(1/4) -->
            <template #detail>
                <el-table-column label="详情" width="160" align="center">
                    <template #default="scope">
                        <el-link type="success" @click="showInfo(scope.row)">点击查看</el-link>
                    </template>
                </el-table-column>
            </template>
        </Table>

        <PopupForm />

        <!-- 示例核心代码(2/4) -->
        <!-- 详情组件在此处使用，但显示与否的判断是写在组件内的 -->
        <Info />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Info from './info.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

defineOptions({
    name: 'examples/table/dialog2',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

// 示例核心代码(3/4)
const showInfo = (row: TableRow) => {
    baTable.form.operate = 'Info'
    baTable.form.extend!.info = row
}

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Dialog2/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.dialog2.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.dialog2.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.dialog2.switch'),
                prop: 'switch',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('examples.table.dialog2.switch 0'), '1': t('examples.table.dialog2.switch 1') },
                width: 80,
            },
            { label: t('examples.table.dialog2.datetime'), prop: 'datetime', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            {
                label: t('examples.table.dialog2.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { render: 'slot', slotName: 'detail', operator: false },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'switch'],
    },
    {
        defaultItems: { switch: '1', datetime: null },
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
