<template>
    <div>
        <template v-if="isArray(cellValue)">
            <template v-for="(tag, idx) in cellValue" :key="idx">
                <el-tag
                    v-if="tag != ''"
                    class="m-4"
                    :type="getTagType(tag, field.custom)"
                    :effect="field.effect ?? 'light'"
                    :size="field.size ?? 'default'"
                >
                    {{ !isEmpty(field.replaceValue) ? field.replaceValue[tag] ?? tag : tag }}
                </el-tag>
            </template>
        </template>
        <template v-else>
            <el-tag
                v-if="cellValue != ''"
                :type="getTagType(cellValue, field.custom)"
                :effect="field.effect ?? 'light'"
                :size="field.size ?? 'default'"
            >
                {{ !isEmpty(field.replaceValue) ? field.replaceValue[cellValue] ?? cellValue : cellValue }}
            </el-tag>
        </template>
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx, TagProps } from 'element-plus'
import { isArray, isEmpty } from 'lodash-es'
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

<style scoped lang="scss">
.m-4 {
    margin: 4px;
}
</style>
