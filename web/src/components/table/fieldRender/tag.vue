<template>
    <div>
        <el-tag v-if="cellValue != ''" :type="getTagType(cellValue, field.custom)" :effect="field.effect ?? 'light'" :size="field.size ?? 'default'">
            {{ !isEmpty(field.replaceValue) ? field.replaceValue[cellValue] ?? cellValue : cellValue }}
        </el-tag>
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx, TagProps } from 'element-plus'
import { isEmpty } from 'lodash-es'
import { getCellValue } from '/@/components/table/index'

interface Props {
    row: TableRow
    field: TableColumn
    column: TableColumnCtx<TableRow>
    index: number
}

const props = defineProps<Props>()

const cellValue = getCellValue(props.row, props.field, props.column, props.index)

const getTagType = (value: string, custom: any): TagProps['type'] => {
    return !isEmpty(custom) && custom[value] ? custom[value] : 'primary'
}
</script>
