<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />
        <!-- 表格顶部菜单 -->
        <TableHeader
            :field="baTable.table.column"
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold']"
            :enable-batch-opt="baTable.table.selection!.length > 0 ? true : false"
            :unfold="baTable.table.expandAll"
            :quick-search-placeholder="'通过标题模糊搜索'"
            @action="baTable.onTableHeaderAction"
        />
        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table
            ref="tableRef"
            :default-expand-all="baTable.table.expandAll"
            :data="baTable.table.data"
            :field="baTable.table.column"
            :row-key="baTable.table.pk"
            :loading="baTable.table.loading"
            :pagination="false"
            @action="baTable.onTableAction"
            @row-dblclick="baTable.onTableDblclick"
        />
        <Form :ba-table="baTable" />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { authMenu } from '/@/api/controllerUrls'
import Form from './form.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { useI18n } from 'vue-i18n'
import { baTableApi } from '/@/api/common'
import baTableClass from '/@/utils/baTable'

const { t } = useI18n()
const tableRef = ref()
const baTable = new baTableClass(
    new baTableApi(authMenu),
    {
        expandAll: false,
        dblClickNotEditColumn: [undefined, 'status'],
        column: [
            { type: 'selection', align: 'center' },
            { label: '标题', prop: 'title', align: 'left' },
            { label: '图标', prop: 'icon', align: 'center', width: '60', render: 'icon', default: 'el-icon-Minus' },
            { label: '名称', prop: 'name', align: 'center', 'show-overflow-tooltip': true },
            {
                label: '类型',
                prop: 'type',
                align: 'center',
                render: 'tag',
                custom: { menu: 'danger', menu_dir: 'success', button: 'info' },
                replaceValue: { menu: t('auth/menu.type menu'), menu_dir: t('auth/menu.type menu_dir'), button: t('auth/menu.type button') },
            },
            { label: '状态', prop: 'status', align: 'center', width: '80', render: 'switch' },
            { label: '更新时间', prop: 'updatetime', align: 'center', width: '160', render: 'datetime' },
            {
                label: '操作',
                align: 'center',
                width: '130',
                render: 'buttons',
                buttons: defaultOptButtons(),
            },
        ],
        dragSortLimitField: 'pid',
    },
    {
        defaultItems: {
            type: 'menu',
            menu_type: 'tab',
            extend: 'none',
            status: '1',
            icon: 'el-icon-Minus',
        },
    },
    {
        // 提交前
        onSubmit: () => {
            if (baTable.form.items?.pid == baTable.form.items?.pidebak) {
                delete baTable.form.items?.pid
            }
        },
    },
    {
        // 获得编辑数据后
        requestEdit: () => {
            if (baTable.form.items && !baTable.form.items.icon) baTable.form.items.icon = 'el-icon-Minus'
            baTable.form.items!['pidebak'] = baTable.form.items!.pid
        },
    }
)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex().then(() => {
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
