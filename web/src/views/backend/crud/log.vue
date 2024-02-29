<template>
    <div>
        <el-dialog
            @close="emits('update:modelValue', false)"
            width="60%"
            :model-value="modelValue"
            class="ba-crud-log-dialog"
            :title="t('crud.crud.CRUD record')"
            :append-to-body="true"
            :destroy-on-close="true"
        >
            <TableHeader
                :buttons="['refresh', 'quickSearch', 'columnDisplay']"
                :quick-search-placeholder="t('Quick search placeholder', { fields: t('crud.log.quick Search Fields') })"
            />
            <Table ref="tableRef">
                <template #tableName>
                    <el-table-column :show-overflow-tooltip="true" prop="table_name" align="center" :label="t('crud.log.table_name')">
                        <template #default="scope">
                            {{ (scope.row.table.databaseConnection ? scope.row.table.databaseConnection + '.' : '') + scope.row.table.name }}
                        </template>
                    </el-table-column>
                </template>
            </Table>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, reactive, watch, nextTick } from 'vue'
import baTableClass from '/@/utils/baTable'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { postDel } from '/@/api/backend/crud'
import { changeStep, state as crudState } from '/@/views/backend/crud/index'
import { auth } from '/@/utils/common'

interface Props {
    modelValue: boolean
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
}>()

const state = reactive({
    ready: false,
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = [
    {
        render: 'confirmButton',
        name: 'copy',
        title: 'crud.crud.copy',
        text: '',
        type: 'primary',
        icon: 'fa fa-copy',
        class: 'table-row-copy',
        popconfirm: {
            confirmButtonText: t('Confirm'),
            cancelButtonText: t('Cancel'),
            confirmButtonType: 'primary',
            title: t('crud.crud.Start CRUD design with this record?'),
            width: '220px',
        },
        disabledTip: false,
        click: (row) => {
            crudState.startData.logId = row[baTable.table.pk!]
            changeStep('log')
            emits('update:modelValue', false)
        },
    },
    {
        render: 'confirmButton',
        name: 'del',
        title: 'Delete',
        text: '',
        type: 'danger',
        icon: 'fa fa-trash',
        class: 'table-row-delete',
        popconfirm: {
            confirmButtonText: t('crud.crud.Delete Code'),
            cancelButtonText: t('Cancel'),
            confirmButtonType: 'danger',
            title: t('crud.crud.Are you sure to delete the generated CRUD code?'),
            width: '248px',
        },
        disabledTip: false,
        click: (row) => {
            postDel(row[baTable.table.pk!]).then(() => {
                baTable.onTableHeaderAction('refresh', {})
            })
        },
        display: (row) => {
            return row['status'] != 'delete' && auth('delete')
        },
    },
]
const baTable = new baTableClass(
    new baTableApi('/admin/crud.Log/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('crud.log.id'), prop: 'id', align: 'center', width: 70, operator: '=', operatorPlaceholder: t('Id'), sortable: 'custom' },
            {
                label: t('crud.log.table_name'),
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                render: 'slot',
                slotName: 'tableName',
            },
            {
                label: t('crud.log.status'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                sortable: false,
                replaceValue: {
                    delete: t('crud.log.status delete'),
                    success: t('crud.log.status success'),
                    error: t('crud.log.status error'),
                    start: t('crud.log.status start'),
                },
                custom: { delete: 'danger', success: 'success', error: 'warning', start: '' },
            },
            {
                label: t('crud.log.create_time'),
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
        defaultItems: { status: 'start' },
    }
)

provide('baTable', baTable)

const getIndex = () => {
    baTable.getIndex()?.then(() => {
        state.ready = true
    })
}

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
})

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal && !state.ready) {
            nextTick(() => {
                getIndex()
            })
        }
    }
)
</script>

<style lang="scss">
.ba-crud-log-dialog .el-dialog__body {
    padding: 10px 20px;
}
</style>
