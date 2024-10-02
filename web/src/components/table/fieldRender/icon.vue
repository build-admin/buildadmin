<template>
    <div>
        <Icon color="var(--el-text-color-primary)" :name="getCellValue(props.row, props.field, props.column, props.index)" />
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
</script>
