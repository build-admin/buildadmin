<template>
    <div>
        <el-input :model-value="cellValue" :placeholder="$t('Link address')">
            <template #append>
                <el-button :disabled="!safeUrl" @click="openUrl(field)">
                    <Icon color="#606266" name="el-icon-Position" />
                </el-button>
            </template>
        </el-input>
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx } from 'element-plus'
import { getCellValue } from '/@/components/table/index'
import { safeURL } from '/@/utils/router'

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

// 无需 computed，外层组件的 :key 拼进了单元格值
const cellValue = getCellValue(props.row, props.field, props.column, props.index)
const safeUrl = safeURL(cellValue, true)

const openUrl = (field: TableColumn) => {
    if (!safeUrl) return
    if (field.target == '_blank') {
        window.open(safeUrl, '_blank', 'noopener,noreferrer')
    } else {
        window.location.href = safeUrl
    }
}
</script>
