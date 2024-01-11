<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" type="info" show-icon>
            <template #default>
                <div class="ba-markdown">
                    <div>对表格内各种行为事件的监听示例，<code>el-table 事件</code></div>
                    <div>1、请按下<code>F12</code>从浏览器控制台查看更多细节</div>
                    <div>2、请单击/右击/双击单元格、行、表头等触发 el-table 事件</div>
                </div>
            </template>
        </el-alert>

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.event2.quick Search Fields') })"
        ></TableHeader>

        <!-- 示例核心代码(1/2) -->
        <!-- 我们在此示例了 el-table 的各种事件 -->
        <!-- 以此类推，el-table 自带的属性也都是可以使用的 -->
        <Table
            @cell-mouse-enter="onCellMouseEnter"
            @cell-mouse-leave="onCellMouseLeave"
            @cell-click="onCellClick"
            @cell-contextmenu="onCellContextmenu"
            @row-click="onRowClick"
            @row-contextmenu="onRowContextmenu"
            @row-dblclick="onDblclick"
            @header-click="onHeaderClick"
            @header-contextmenu="onHeaderContextmenu"
            @header-dragend="onHeaderDragend"
            ref="tableRef"
        ></Table>

        <!-- 温馨提示 -->
        <!-- 事件 select、select-all、selection-change、cell-dblclick、sort-change 请使用 ba-table 的钩子 -->
        <!-- 事件 filter-change	可用，但请自行实现筛选，buildadmin 并未使用 el 提供的表格筛选模式 -->

        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

defineOptions({
    name: 'examples/table/event2',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * 示例核心代码(2/2)
 */
const onCellMouseEnter = (row: TableRow, column: TableColumn, cell: HTMLElement, event: MouseEvent) => {
    console.info('%c-------单元格 hover 进入--------', 'color:blue')
    console.log('参数', row, column, cell, event)
}

const onCellMouseLeave = (row: TableRow, column: TableColumn, cell: HTMLElement, event: MouseEvent) => {
    console.info('%c-------单元格 hover 退出--------', 'color:blue')
    console.log('参数', row, column, cell, event)
}

const onCellClick = (row: TableRow, column: TableColumn, cell: HTMLElement, event: PointerEvent) => {
    console.info('%c-------单元格被点击--------', 'color:blue')
    console.log('参数', row, column, cell, event)
}

const onCellContextmenu = (row: TableRow, column: TableColumn, cell: HTMLElement, event: PointerEvent) => {
    console.info('%c-------单元格被右击--------', 'color:blue')
    console.log('参数', row, column, cell, event)
}

const onRowClick = (row: TableRow, column: TableColumn, event: PointerEvent) => {
    console.info('%c-------某一行被点击--------', 'color:blue')
    console.log('参数', row, column, event)
}

const onRowContextmenu = (row: TableRow, column: TableColumn, event: PointerEvent) => {
    console.info('%c-------某一行被右击--------', 'color:blue')
    console.log('参数', row, column, event)
}

const onDblclick = (row: TableRow, column: TableColumn, event: MouseEvent) => {
    console.info('%c-------某一行被双击--------', 'color:blue')
    console.log('参数', row, column, event)
}

const onHeaderClick = (column: TableColumn, event: PointerEvent) => {
    console.info('%c-------表头被点击--------', 'color:blue')
    console.log('参数', column, event)
}

const onHeaderContextmenu = (column: TableColumn, event: PointerEvent) => {
    console.info('%c-------表头被右击--------', 'color:blue')
    console.log('参数', column, event)
}

const onHeaderDragend = (newWidth: number, oldWidth: number, column: TableColumn, event: MouseEvent) => {
    console.info('%c-------列宽度改变--------', 'color:blue')
    console.log(newWidth, oldWidth, column, event)
}

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Event2/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.event2.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.event2.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.event2.switch'),
                prop: 'switch',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('examples.table.event2.switch 0'), '1': t('examples.table.event2.switch 1') },
            },
            { label: t('examples.table.event2.datetime'), prop: 'datetime', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            {
                label: t('examples.table.event2.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'switch'],
    },
    {
        defaultItems: { switch: '1', datetime: null },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
