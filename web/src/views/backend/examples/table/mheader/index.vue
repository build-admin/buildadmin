<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.mheader.quick Search Fields') })"
        ></TableHeader>

        <!-- 示例核心代码(1/2) -->
        <!-- 请注意 class 不设置则表头的分隔线不会显示 -->
        <Table class="m-header-table" ref="tableRef">
            <template #mHeader>
                <el-table-column label="用户信息">
                    <el-table-column label="用户名" align="center" prop="user.username" />
                    <el-table-column label="地址信息">
                        <el-table-column label="城市" align="center" prop="city" />
                        <el-table-column label="详细地址" align="center" prop="address" />
                        <el-table-column label="邮编" align="center" prop="code" />
                    </el-table-column>
                </el-table-column>
            </template>
            <template #city> </template>
            <template #address> </template>
            <template #code> </template>
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
    name: 'examples/table/mheader',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Mheader/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.mheader.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            { label: t('examples.table.mheader.date'), prop: 'date', align: 'center', operator: 'eq', sortable: 'custom' },
            // 示例核心代码(2/2)
            // 表格内的渲染主要就是通过这个 slot
            {
                render: 'slot',
                slotName: 'mHeader',
                operator: false,
            },
            // 以下定义了很多列的 show:false，这是为了公共搜索
            {
                label: t('examples.table.mheader.user__username'),
                prop: 'user.username',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
                show: false,
            },
            { label: t('examples.table.mheader.city'), prop: 'city', align: 'center', operator: 'LIKE', show: false },
            {
                label: t('examples.table.mheader.address'),
                prop: 'address',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                show: false,
            },
            {
                label: t('examples.table.mheader.code'),
                prop: 'code',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                show: false,
            },
            {
                label: t('examples.table.mheader.create_time'),
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
:deep(.m-header-table) {
    --el-table-border-color: var(--el-border-color-lighter);
}
</style>
