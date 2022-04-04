<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'delete', 'comSearch']"
            :quick-search-placeholder="'通过规则名称模糊搜索'"
            @action="baTable.onTableHeaderAction"
        >
            <el-tooltip content="将记录还原到原数据表" placement="top">
                <el-button type="success" :disabled="baTable.table.selection!.length > 0 ? false:true" v-blur class="table-header-operate">
                    <Icon color="#ffffff" name="el-icon-RefreshRight" />
                    <span class="table-header-operate-text">还原</span>
                </el-button>
            </el-tooltip>
        </TableHeader>

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table @action="baTable.onTableAction" />

        <!-- 表单 -->
        <!-- <Info /> -->
    </div>
</template>

<script setup lang="ts">
import { provide } from 'vue'
import baTableClass from '/@/utils/baTable'
import { securityDataRecycleLog } from '/@/api/controllerUrls'
import Info from './info.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'

let optButtons: OptButton[] = [
    {
        render: 'tipButton',
        name: 'restore',
        title: 'restore',
        text: '',
        type: 'success',
        icon: 'el-icon-RefreshRight',
        class: 'table-row-edit',
        disabledTip: false,
    },
]
optButtons = optButtons.concat(defaultOptButtons(['delete']))
const baTable = new baTableClass(new baTableApi(securityDataRecycleLog), {
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
        {
            label: '是否已还原',
            prop: 'is_restore',
            align: 'center',
            render: 'tag',
            custom: { '0': 'success', '1': 'danger' },
            replaceValue: { '0': '否', '1': '是' },
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
            width: '100',
            render: 'buttons',
            buttons: optButtons,
            operator: false,
        },
    ],
    dblClickNotEditColumn: [undefined],
})

provide('baTable', baTable)

baTable.mount()
baTable.getIndex()
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
