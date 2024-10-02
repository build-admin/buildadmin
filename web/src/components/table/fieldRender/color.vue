<template>
    <div>
        <div :style="{ background: cellValue }" class="ba-table-render-color"></div>
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

<style scoped lang="scss">
.ba-table-render-color {
    height: 25px;
    width: 100%;
}
</style>
