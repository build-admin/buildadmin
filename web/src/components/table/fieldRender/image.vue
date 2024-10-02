<template>
    <div>
        <el-image
            :hide-on-click-modal="true"
            :preview-teleported="true"
            :preview-src-list="[fullUrl(cellValue)]"
            :src="fullUrl(cellValue)"
            class="ba-table-render-image"
        ></el-image>
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx } from 'element-plus'
import { getCellValue } from '/@/components/table/index'
import { fullUrl } from '/@/utils/common'

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
.ba-table-render-image {
    height: 36px;
    width: 36px;
}
</style>
