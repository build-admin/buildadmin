<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'delete', 'comSearch']"
            :quick-search-placeholder="'通过规则名称模糊搜索'"
            @action="baTable.onTableHeaderAction"
        >
            <el-popconfirm
                @confirm="onRestoreAction"
                confirm-button-text="还原"
                cancel-button-text="取消"
                confirmButtonType="success"
                title="确定还原选中记录？"
            >
                <template #reference>
                    <div class="mlr-12">
                        <el-tooltip content="还原选中记录到原数据表" placement="top">
                            <el-button
                                v-blur
                                :disabled="baTable.table.selection!.length > 0 ? false:true"
                                class="table-header-operate"
                                type="success"
                            >
                                <Icon color="#ffffff" name="el-icon-RefreshRight" />
                                <span class="table-header-operate-text">还原</span>
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
import { securityDataRecycleLog } from '/@/api/controllerUrls'
import { info, restore } from '/@/api/backend/security/dataRecycleLog'
import InfoDialog from './info.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import useCurrentInstance from '/@/utils/useCurrentInstance'
import { buildJsonToElTreeData } from '/@/utils/common'

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
        name: 'restore',
        title: 'security/dataRecycleLog.restore',
        text: '',
        type: 'success',
        icon: 'el-icon-RefreshRight',
        class: 'table-row-edit',
        popconfirm: {
            confirmButtonText: '还原',
            cancelButtonText: '取消',
            confirmButtonType: 'success',
            title: '确认要还原记录吗？',
        },
        disabledTip: false,
    },
]
optButtons = optButtons.concat(defaultOptButtons(['delete']))
const baTable = new baTableClass(
    new baTableApi(securityDataRecycleLog),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: 'ID', prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询', width: 70 },
            { label: '操作管理员', prop: 'admin.nickname', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            { label: '回收规则名称', prop: 'recycle.name', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            { label: '控制器', prop: 'recycle.controller_as', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            { label: '数据表', prop: 'data_table', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            {
                label: '被删数据',
                prop: 'data',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: '任意片段模糊查询',
                'show-overflow-tooltip': true,
            },
            { label: 'IP', prop: 'ip', align: 'center', operator: 'LIKE', operatorPlaceholder: '模糊查询' },
            {
                show: false,
                label: 'User Agent',
                prop: 'useragent',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: '模糊查询',
                'show-overflow-tooltip': true,
            },
            { label: '删除时间', prop: 'createtime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            {
                label: '操作',
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
    {},
    {
        onTableDblclick: ({ row }: { row: TableRow }) => {
            infoButtonClick(row[baTable.table.pk!])
        },
    }
)

const onRestore = (ids: string[]) => {
    restore(ids).then((res) => {
        baTable.onTableHeaderAction('refresh', {})
    })
}

const onRestoreAction = () => {
    onRestore(baTable.getSelectionIds())
}

const infoButtonClick = (id: string) => {
    baTable.form.extend!['info'] = {}
    baTable.form.operate = 'info'
    baTable.form.loading = true
    info(id).then((res) => {
        res.data.row.data = res.data.row.data ? [{ label: '点击展开', children: buildJsonToElTreeData(res.data.row.data) }] : []
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
        if (data.name == 'restore') {
            onRestore([data.row[baTable.table.pk!]])
        } else if (data.name == 'info') {
            infoButtonClick(data.row[baTable.table.pk!])
        }
    })
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'security/dataRecycleLog',
})
</script>

<style scoped lang="scss">
.table-header-operate {
    margin-left: 12px;
}
</style>
