<template>
    <div>
        <el-input :model-value="cellValue" :placeholder="$t('Link address')">
            <template #append>
                <el-button @click="openUrl(cellValue, field)">
                    <Icon color="#606266" name="el-icon-Position" />
                </el-button>
            </template>
        </el-input>
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx } from 'element-plus'
import { getCellValue } from '/@/components/table/index'

interface Props {
    row: TableRow
    field: TableColumn
    column: TableColumnCtx<TableRow>
    index: number
}

const props = defineProps<Props>()

if (props.field.click) {
    console.warn('baTable.table.column.click 即将废弃，请使用 el-table 的 @cell-click 或单元格自定义渲染代替')
}

const cellValue = getCellValue(props.row, props.field, props.column, props.index)

const openUrl = (url: string, field: TableColumn) => {
    if (field.target == '_blank') {
        window.open(url)
    } else {
        window.location.href = url
    }
}
</script>
