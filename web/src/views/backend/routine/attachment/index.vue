<template>
    <div class="default-main">
        <div class="ba-table-box">
            <el-alert class="ba-table-alert" v-if="table.remark" :title="table.remark" type="info" show-icon />
            <!-- 表格顶部菜单 -->
            <TableHeader
                :field="table.column"
                :buttons="['refresh', 'edit', 'delete', 'comSearch']"
                :enable-batch-opt="table.selection.length > 0 ? true : false"
                :quick-search-placeholder="'通过原始名称模糊搜索'"
                @action="onTableHeaderAction"
            />
            <!-- 表格 -->
            <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
            <Table
                ref="tableRef"
                :data="table.data"
                :field="table.column"
                :row-key="table.pk"
                :total="table.total"
                :loading="table.loading"
                @action="onTableAction"
                @row-dblclick="onTableDblclick"
            />
            <!-- 编辑和新增表单 -->
            <Form />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Form from './form.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { table, onTableHeaderAction, onTableAction, onTableDblclick, getIndex, mount } from './index'

const tableRef = ref()

mount()
getIndex().then(() => {
    initSort()
})

/**
 * 初始化默认排序
 * el表格的`default-sort`在自定义排序时无效
 * 此方法只有在表格数据请求结束后执行有效
 */
const initSort = () => {
    if (table.defaultOrder.prop) {
        let defaultOrder = table.defaultOrder.prop + ',' + table.defaultOrder.order
        if (table.filter.order != defaultOrder) {
            table.filter.order = defaultOrder
            tableRef.value.getRef().sort(table.defaultOrder.prop, table.defaultOrder.order == 'desc' ? 'descending' : 'ascending')
        }
    }
}
</script>

<style scoped lang="scss"></style>
