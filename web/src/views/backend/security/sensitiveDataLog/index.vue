<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('security.sensitiveDataLog.Rule name') })"
            @action="baTable.onTableHeaderAction"
        >
            <el-popconfirm
                @confirm="onRollbackAction"
                :confirm-button-text="t('security.sensitiveDataLog.RollBACK')"
                :cancel-button-text="t('Cancel')"
                confirmButtonType="success"
                :title="t('security.sensitiveDataLog.Are you sure you want to rollback the record?')"
                :disabled="baTable.table.selection!.length > 0 ? false:true"
            >
                <template #reference>
                    <div class="mlr-12">
                        <el-tooltip :content="t('security.sensitiveDataLog.Rollback the selected record to the original data table')" placement="top">
                            <el-button
                                v-blur
                                :disabled="baTable.table.selection!.length > 0 ? false:true"
                                class="table-header-operate"
                                type="success"
                            >
                                <Icon size="16" color="#ffffff" name="fa fa-sign-in" />
                                <span class="table-header-operate-text">{{ t('security.sensitiveDataLog.RollBACK') }}</span>
                            </el-button>
                        </el-tooltip>
                    </div>
                </template>
            </el-popconfirm>
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table @action="baTable.onTableAction" />

        <!-- 表单 -->
        <InfoDialog />
    </div>
</template>

<script setup lang="ts">
import { provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { securitySensitiveDataLog } from '/@/api/controllerUrls'
import { info, rollback } from '/@/api/backend/security/sensitiveDataLog'
import InfoDialog from './info.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

let optButtons: OptButton[] = [
    {
        render: 'tipButton',
        name: 'info',
        title: 'info',
        text: '',
        type: 'primary',
        icon: 'fa fa-search-plus',
        class: 'table-row-info',
        disabledTip: false,
    },
    {
        render: 'confirmButton',
        name: 'rollback',
        title: 'security.sensitiveDataLog.RollBACK',
        text: '',
        type: 'success',
        icon: 'fa fa-sign-in',
        class: 'table-row-edit',
        popconfirm: {
            confirmButtonText: t('security.sensitiveDataLog.RollBACK'),
            cancelButtonText: t('Cancel'),
            confirmButtonType: 'success',
            title: t('security.sensitiveDataLog.Are you sure you want to rollback the record?'),
        },
        disabledTip: false,
    },
]
optButtons = optButtons.concat(defaultOptButtons(['delete']))
const baTable = new baTableClass(
    new baTableApi(securitySensitiveDataLog),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('id'), prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query'), width: 70 },
            {
                label: t('security.sensitiveDataLog.Operation administrator'),
                prop: 'admin.nickname',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.sensitiveDataLog.Rule name'),
                prop: 'sensitive.name',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.sensitiveDataLog.controller'),
                prop: 'sensitive.controller_as',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.sensitiveDataLog.data sheet'),
                prop: 'data_table',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.sensitiveDataLog.Modify line'),
                prop: 'id_value',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.sensitiveDataLog.Modification'),
                prop: 'data_comment',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.sensitiveDataLog.Before modification'),
                prop: 'before',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                'show-overflow-tooltip': true,
            },
            {
                label: t('security.sensitiveDataLog.After modification'),
                prop: 'after',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                'show-overflow-tooltip': true,
            },
            { label: 'IP', prop: 'ip', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('security.sensitiveDataLog.Modification time'),
                prop: 'createtime',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            {
                label: t('operate'),
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
        onTableDblclick: ({ row }: { row: TableRow }) => {
            infoButtonClick(row[baTable.table.pk!])
            return false
        },
    }
)

const onRollback = (ids: string[]) => {
    rollback(ids).then((res) => {
        baTable.onTableHeaderAction('refresh', {})
    })
}

const onRollbackAction = () => {
    onRollback(baTable.getSelectionIds())
}

const infoButtonClick = (id: string) => {
    baTable.form.extend!['info'] = {}
    baTable.form.operate = 'info'
    baTable.form.loading = true
    info(id).then((res) => {
        baTable.form.extend!['info'] = res.data.row
        baTable.form.loading = false
    })
}

provide('baTable', baTable)

onMounted(() => {
    const { proxy } = useCurrentInstance()
    baTable.mount()
    baTable.getIndex()

    /**
     * 表格内的按钮响应
     * @param name 按钮name
     * @param row 被操作行数据
     */
    proxy.eventBus.on('onTableButtonClick', (data: { name: string; row: TableRow }) => {
        if (!baTable.activate) return
        if (data.name == 'rollback') {
            onRollback([data.row[baTable.table.pk!]])
        } else if (data.name == 'info') {
            infoButtonClick(data.row[baTable.table.pk!])
        }
    })
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'security/sensitiveDataLog',
})
</script>

<style scoped lang="scss">
.table-header-operate {
    margin-left: 12px;
}
.table-header-operate-text {
    margin-left: 6px;
}
</style>
