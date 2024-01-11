<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.expand.quick Search Fields') })"
        ></TableHeader>

        <Table ref="tableRef">
            <template #expandSlot>
                <el-table-column label="会员信息" :width="120" type="expand">
                    <template #default="props">
                        <div class="ml-25">
                            <p>ID:{{ props.row.user_id }}</p>
                            <p>用户名:{{ props.row.user.username }}</p>
                            <p>详细地址:{{ props.row.address }}</p>
                            <p>时间日期:{{ props.row.date }}</p>
                        </div>
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

defineOptions({
    name: 'examples/table/expand',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Expand/'),
    {
        pk: 'id',
        column: [
            // { type: 'selection', align: 'center', operator: false },
            // { label: t('examples.table.expand.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                render: 'slot',
                slotName: 'expandSlot',
                operator: false,
            },
            {
                show: false,
                label: t('examples.table.expand.user__username'),
                prop: 'user.username',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
            },
            { label: t('examples.table.expand.date'), prop: 'date', align: 'center', operator: 'eq', sortable: 'custom' },
            {
                label: t('examples.table.expand.address'),
                prop: 'address',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.expand.code'),
                prop: 'code',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.expand.create_time'),
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

<style scoped lang="scss">
.ml-25 {
    margin-left: 25px;
}
</style>
