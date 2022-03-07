<template>
    <div class="default-main">
        <div class="ba-table-box">
            <!-- 表格顶部菜单 -->
            <TableHeader
                :buttons="['refresh', 'add', 'edit', 'delete', 'unfold']"
                :enable-batch-opt="table.selection.length > 0 ? true : false"
                :unfold="table.expandAll"
                @on-unfold="onTableUnfold"
            />
            <!-- 表格 -->
            <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
            <Table
                ref="tableRef"
                :default-expand-all="table.expandAll"
                @selection-change="onTableSelection"
                :data="table.data"
                :field="table.column"
            />
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { index } from '/@/api/backend/auth/Menu'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'

const tableRef = ref()
const table: {
    // 表格列数据
    column: TableColumn[]
    // 表格数据
    data: TableRow[]
    // 表格选中项
    selection: TableRow[]
    // 表格是否展开所有子项
    expandAll: boolean
} = reactive({
    column: [
        { type: 'selection', align: 'center' },
        { label: '标题', prop: 'title', align: 'left' },
        { label: '图片', prop: 'title', align: 'left', render: 'image', width: '60' },
        { label: '图片测试', prop: 'title', align: 'left', render: 'images', width: '164' },
        { label: '图标', prop: 'icon', align: 'center', width: '60', render: 'icon' },
        { label: '名称', prop: 'name', align: 'center', 'show-overflow-tooltip': true },
        {
            label: '类型',
            prop: 'type',
            align: 'center',
            render: 'tag',
            custom: { menu: 'danger', menu_dir: 'success', button: 'info' },
        },
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
    data: [],
    selection: [],
    expandAll: true,
})

index().then((res) => {
    table.data = res.data.menu
})

/*
 * 表格折叠展开子级
 */
const onTableUnfold = (unfold: boolean) => {
    table.expandAll = unfold
    tableRef.value.unFoldAll(unfold)
}
/*
 * 接受表格中选中的数据
 */
const onTableSelection = (selection: TableRow[]) => {
    table.selection = selection
}
</script>

<style lang="scss" scoped></style>
