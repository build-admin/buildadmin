<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.cell.pre.quick Search Fields') })"
        ></TableHeader>

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
    name: 'examples/table/cell/pre',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['weigh-sort', 'edit', 'delete'])

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.cell.Pre/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.cell.pre.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.cell.pre.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.cell.pre.url'),
                prop: 'url',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                render: 'url',
                width: 260,
            },
            { label: t('examples.table.cell.pre.image'), prop: 'image', align: 'center', render: 'image', operator: false },
            { label: t('examples.table.cell.pre.icon'), prop: 'icon', align: 'center', render: 'icon', operator: false },
            { label: t('examples.table.cell.pre.color'), prop: 'color', align: 'center', render: 'color', operator: false },
            {
                label: t('examples.table.cell.pre.status'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                operator: false,
                sortable: false,
                replaceValue: { '0': t('examples.table.cell.pre.status 0'), '1': t('examples.table.cell.pre.status 1') },
            },
            {
                label: t('examples.table.cell.pre.status'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                operator: false,
                size: 'large',
                effect: 'plain',
                custom: { 0: 'success', 1: 'danger' },
                sortable: false,
                replaceValue: { '0': t('examples.table.cell.pre.status 0'), '1': t('examples.table.cell.pre.status 1') },
            },
            {
                label: t('examples.table.cell.pre.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('examples.table.cell.pre.status 0'), '1': t('examples.table.cell.pre.status 1') },
            },
            { label: t('examples.table.cell.pre.weigh'), prop: 'weigh', align: 'center', operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.cell.pre.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'status'],
        defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: { status: '1', weigh: 0 },
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
