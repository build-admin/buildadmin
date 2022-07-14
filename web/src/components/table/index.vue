<template>
    <el-table
        ref="tableRef"
        class="ba-data-table w100"
        header-cell-class-name="table-header-cell"
        :default-expand-all="baTable.table.expandAll"
        :data="baTable.table.data"
        :row-key="baTable.table.pk"
        :border="true"
        v-loading="baTable.table.loading"
        stripe
        @select-all="onSelectAll"
        @select="onSelect"
        @selection-change="onSelectionChange"
        @sort-change="onSortChange"
        @row-dblclick="baTable.onTableDblclick"
        v-bind="$attrs"
    >
        <template v-for="(item, key) in baTable.table.column">
            <Column v-if="item.show !== false" :attr="item">
                <template v-if="item.render" #default="scope">
                    <FieldRender
                        :field="item"
                        :key="item.render == 'switch' ? 'column-' + scope.column.property + '-' + key + '-' + scope.row[scope.column.property] : 0"
                        :row="scope.row"
                        :property="scope.column.property"
                    />
                </template>
            </Column>
        </template>
    </el-table>
    <div v-if="pagination" class="table-pagination">
        <el-pagination
            :currentPage="state.currentPage"
            :page-size="state.pageSize"
            :page-sizes="[10, 20, 50, 100]"
            background
            :layout="config.layout.shrink ? 'prev, next, jumper' : 'sizes, ->, prev, pager, next, jumper'"
            :total="baTable.table.total"
            @size-change="onTableSizeChange"
            @current-change="onTableCurrentChange"
        ></el-pagination>
    </div>
</template>

<script setup lang="ts">
import { ref, nextTick, reactive, inject } from 'vue'
import type { ElTable } from 'element-plus'
import Column from '/@/components/table/column/index.vue'
import FieldRender from '/@/components/table/fieldRender/index.vue'
import { useConfig } from '/@/stores/config'
import type baTableClass from '/@/utils/baTable'

const config = useConfig()
const tableRef = ref<InstanceType<typeof ElTable>>()
const baTable = inject('baTable') as baTableClass

interface Props extends Partial<InstanceType<typeof ElTable>> {
    pagination?: boolean
}
const props = withDefaults(defineProps<Props>(), {
    pagination: true,
})

const state = reactive({
    currentPage: 1,
    pageSize: 10,
})

const emits = defineEmits<{
    (e: 'action', event: string, data: anyObj): void
}>()

const onTableSizeChange = (val: number) => {
    state.pageSize = val
    emits('action', 'page-size-change', {
        size: val,
    })
}

const onTableCurrentChange = (val: number) => {
    state.currentPage = val
    emits('action', 'current-page-change', {
        page: val,
    })
}

const onSortChange = ({ order, prop }: { order: string; prop: string }) => {
    emits('action', 'sort-change', {
        prop: prop,
        order: order ? (order == 'ascending' ? 'asc' : 'desc') : '',
    })
}

/*
 * 全选和取消全选
 * 实现子级同时选择和取消选中
 */
const onSelectAll = (selection: TableRow[]) => {
    if (isSelectAll(selection.map((row: TableRow) => row[baTable.table.pk!].toString()))) {
        selection.map((row: TableRow) => {
            if (row.children) {
                selectChildren(row.children, true)
            }
        })
    } else {
        tableRef.value?.clearSelection()
    }
}

/*
 * 是否是全选操作
 * 只检查第一个元素是否被选择
 * 全选时：selectIds为所有元素的id
 * 取消全选时：selectIds为所有子元素的id
 */
const isSelectAll = (selectIds: string[]) => {
    let data = baTable.table.data as TableRow[]
    for (const key in data) {
        return selectIds.includes(data[key][baTable.table.pk!].toString())
    }
    return false
}

/*
 * 选择子项-递归
 */
const selectChildren = (children: TableRow[], type: boolean) => {
    children.map((j: TableRow) => {
        toggleSelection(j, type)
        if (j.children) {
            selectChildren(j.children, type)
        }
    })
}

/*
 * 执行选择操作
 */
const toggleSelection = (row: TableRow, type: boolean) => {
    if (row) {
        nextTick(() => {
            tableRef.value?.toggleRowSelection(row, type)
        })
    }
}

/*
 * 手动选择时，同时选择子级
 */
const onSelect = (selection: TableRow[], row: TableRow) => {
    if (
        selection.some((item: TableRow) => {
            return row[baTable.table.pk!] === item[baTable.table.pk!]
        })
    ) {
        if (row.children) {
            selectChildren(row.children, true)
        }
    } else {
        if (row.children) {
            selectChildren(row.children, false)
        }
    }
}

/*
 * 记录选择的项
 */
const onSelectionChange = (selection: TableRow[]) => {
    emits('action', 'selection-change', selection)
}

/*
 * 设置折叠所有-递归
 */
const setUnFoldAll = (children: TableRow[], unfold: boolean) => {
    for (const key in children) {
        tableRef.value?.toggleRowExpansion(children[key], unfold)
        if (children[key].children) {
            setUnFoldAll(children[key].children!, unfold)
        }
    }
}

/*
 * 折叠所有
 */
const unFoldAll = (unfold: boolean) => {
    setUnFoldAll(baTable.table.data!, unfold)
}

const getRef = () => {
    return tableRef.value
}

defineExpose({
    unFoldAll,
    getRef,
})
</script>

<style scoped lang="scss">
.ba-data-table :deep(.el-button + .el-button) {
    margin-left: 6px;
}
.ba-data-table :deep(.table-header-cell) .cell {
    color: var(--color-text-primary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.table-pagination {
    box-sizing: border-box;
    width: 100%;
    max-width: 100%;
    background-color: #ffffff;
    padding: 13px 15px;
}
</style>
