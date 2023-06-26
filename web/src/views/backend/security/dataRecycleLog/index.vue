<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('security.dataRecycleLog.Rule name') })"
        >
            <el-popconfirm
                @confirm="onRestoreAction"
                :confirm-button-text="t('security.dataRecycleLog.restore')"
                :cancel-button-text="t('Cancel')"
                confirmButtonType="success"
                :title="t('security.dataRecycleLog.Are you sure to restore the selected records?')"
                :disabled="baTable.table.selection!.length > 0 ? false:true"
            >
                <template #reference>
                    <div class="mlr-12">
                        <el-tooltip :content="t('security.dataRecycleLog.Restore the selected record to the original data table')" placement="top">
                            <el-button
                                v-blur
                                :disabled="baTable.table.selection!.length > 0 ? false:true"
                                class="table-header-operate"
                                type="success"
                            >
                                <Icon color="#ffffff" name="el-icon-RefreshRight" />
                                <span class="table-header-operate-text">{{ t('security.dataRecycleLog.restore') }}</span>
                            </el-button>
                        </el-tooltip>
                    </div>
                </template>
            </el-popconfirm>
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table />

        <!-- 表单 -->
        <InfoDialog />
    </div>
</template>

<script setup lang="ts">
import { provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { securityDataRecycleLog } from '/@/api/controllerUrls'
import { info, restore } from '/@/api/backend/security/dataRecycleLog'
import InfoDialog from './info.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { buildJsonToElTreeData } from '/@/utils/common'
import { useI18n } from 'vue-i18n'

defineOptions({
    name: 'security/dataRecycleLog',
})

const { t } = useI18n()
let optButtons: OptButton[] = [
    {
        render: 'tipButton',
        name: 'info',
        title: 'Info',
        text: '',
        type: 'primary',
        icon: 'fa fa-search-plus',
        class: 'table-row-info',
        disabledTip: false,
        click: (row: TableRow) => {
            infoButtonClick(row[baTable.table.pk!])
        },
    },
    {
        render: 'confirmButton',
        name: 'restore',
        title: 'security.dataRecycleLog.restore',
        text: '',
        type: 'success',
        icon: 'el-icon-RefreshRight',
        class: 'table-row-edit',
        popconfirm: {
            confirmButtonText: t('security.dataRecycleLog.restore'),
            cancelButtonText: t('Cancel'),
            confirmButtonType: 'success',
            title: t('security.dataRecycleLog.Are you sure to restore the selected records?'),
        },
        disabledTip: false,
        click: (row: TableRow) => {
            onRestore([row[baTable.table.pk!]])
        },
    },
]
optButtons = optButtons.concat(defaultOptButtons(['delete']))
const baTable = new baTableClass(
    new baTableApi(securityDataRecycleLog),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('Id'), prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query'), width: 70 },
            {
                label: t('security.dataRecycleLog.Operation administrator'),
                prop: 'admin.nickname',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.dataRecycleLog.Recycling rule name'),
                prop: 'recycle.name',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.dataRecycleLog.controller'),
                prop: 'recycle.controller_as',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.dataRecycleLog.data sheet'),
                prop: 'data_table',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.dataRecycleLog.DeletedData'),
                prop: 'data',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('security.dataRecycleLog.Arbitrary fragment fuzzy query'),
                showOverflowTooltip: true,
            },
            { label: 'IP', prop: 'ip', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                show: false,
                label: 'User Agent',
                prop: 'useragent',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                showOverflowTooltip: true,
            },
            {
                label: t('security.dataRecycleLog.Delete time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            {
                label: t('Operate'),
                align: 'center',
                width: 120,
                render: 'buttons',
                buttons: optButtons,
                operator: false,
            },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {},
    {
        onTableDblclick: ({ row }) => {
            infoButtonClick(row[baTable.table.pk!])
            return false
        },
    }
)

const onRestore = (ids: string[]) => {
    restore(ids).then(() => {
        baTable.onTableHeaderAction('refresh', {})
    })
}

const onRestoreAction = () => {
    onRestore(baTable.getSelectionIds())
}

const infoButtonClick = (id: string) => {
    baTable.form.extend!['info'] = {}
    baTable.form.operate = 'Info'
    baTable.form.loading = true
    info(id).then((res) => {
        res.data.row.data = res.data.row.data
            ? [{ label: t('security.dataRecycleLog.Click to expand'), children: buildJsonToElTreeData(res.data.row.data) }]
            : []
        baTable.form.extend!['info'] = res.data.row
        baTable.form.loading = false
    })
}

provide('baTable', baTable)

onMounted(() => {
    baTable.mount()
    baTable.getIndex()
})
</script>

<style scoped lang="scss">
.table-header-operate {
    margin-left: 12px;
}
</style>
