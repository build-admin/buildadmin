<template>
    <div>
        <div v-html="field.customTemplate ? field.customTemplate(row, field, cellValue, column, index) : ''"></div>
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

const cellValue = getCellValue(props.row, props.field, props.column, props.index)
</script>
