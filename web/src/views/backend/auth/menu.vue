<template>
    <div class="default-main">
        <div class="ba-table-box">
            <!-- 表格顶部菜单 -->
            <TableHeader
                :buttons="['refresh', 'add', 'edit', 'delete', 'unfold']"
                :enable-batch-opt="state.tableSelection.length > 0 ? true : false"
            />
            <!-- 表格 -->
            <Table @selection-change="onSelectionChange" :data="state.tableData" :field="state.tableColumn" />
        </div>
    </div>
</template>

<script lang="ts" setup>
import { reactive } from 'vue'
import { index } from '/@/api/backend/auth/Menu'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'

const state: {
    // 表格列数据
    tableColumn: TableColumn[]
    // 表格数据
    tableData: TableRow[]
    // 表格选中项
    tableSelection: TableRow[]
} = reactive({
    tableColumn: [
        { type: 'selection', align: 'center' },
        { label: '标题', prop: 'title', align: 'left' },
        { label: '图片测试', prop: 'title', align: 'left', render: 'images' },
        { label: '图标', prop: 'icon', align: 'center', width: '60', render: 'icon' },
        { label: '名称', prop: 'name', align: 'center', 'show-overflow-tooltip': true },
        { label: '类型', prop: 'type', align: 'center' },
        { label: '组件路径', prop: 'component', align: 'center', 'show-overflow-tooltip': true },
        { label: '状态', prop: 'status', align: 'center', width: '80', render: 'switch' },
        {
            label: '操作',
            align: 'center',
            width: '100',
            render: 'buttons',
            buttons: defaultOptButtons(),
        },
    ],
    tableData: [],
    tableSelection: [],
})

index().then((res) => {
    state.tableData = res.data.menu
})

/*
 * 接受表格中选中的数据
 */
const onSelectionChange = (selection: TableRow[]) => {
    state.tableSelection = selection
}
</script>

<style lang="scss" scoped></style>
