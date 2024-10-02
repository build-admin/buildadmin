<template>
    <div>
        <el-switch v-if="field.prop" @change="onChange" :model-value="cellValue" :loading="loading" active-value="1" inactive-value="0" />
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx } from 'element-plus'
import { inject, ref } from 'vue'
import { getCellValue } from '/@/components/table/index'
import type baTableClass from '/@/utils/baTable'

interface Props {
    row: TableRow
    field: TableColumn
    column: TableColumnCtx<TableRow>
    index: number
}

const loading = ref(false)
const props = defineProps<Props>()
const baTable = inject('baTable') as baTableClass
const cellValue = ref(getCellValue(props.row, props.field, props.column, props.index))

if (typeof cellValue.value === 'number') {
    cellValue.value = cellValue.value.toString()
}

const onChange = (value: string | number | boolean) => {
    loading.value = true
    baTable.api
        .postData('edit', {
            [baTable.table.pk!]: props.row[baTable.table.pk!],
            [props.field.prop!]: value,
        })
        .then(() => {
            cellValue.value = value
            baTable.onTableAction('field-change', { value: value, ...props })
        })
        .finally(() => {
            loading.value = false
        })
}
</script>
