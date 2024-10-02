<template>
    <div>
        <template v-if="isArray(cellValue) && cellValue.length">
            <el-image
                v-for="(item, idx) in cellValue"
                :key="idx"
                :initial-index="idx"
                :preview-teleported="true"
                :preview-src-list="arrayFullUrl(cellValue)"
                class="ba-table-render-images-item"
                :src="fullUrl(item)"
                :hide-on-click-modal="true"
            ></el-image>
        </template>
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx } from 'element-plus'
import { isArray } from 'lodash-es'
import { getCellValue } from '/@/components/table/index'
import { arrayFullUrl, fullUrl } from '/@/utils/common'

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
.ba-table-render-images-item {
    height: 36px;
    width: 36px;
    margin: 0 5px;
}
</style>
