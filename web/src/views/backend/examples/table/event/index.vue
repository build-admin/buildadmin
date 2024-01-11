<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" type="info" show-icon>
            <template #default>
                <div class="ba-markdown">
                    <div>对表格内各种行为事件的监听示例，<code>ba-table 前后置钩子</code></div>
                    <div>1、请按下<code>F12</code>从浏览器控制台查看更多细节</div>
                    <div>2、包括数据初始化、切换表单、表单提交、编辑、删除等</div>
                </div>
            </template>
        </el-alert>

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.event.quick Search Fields') })"
        ></TableHeader>

        <Table ref="tableRef"></Table>

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
    name: 'examples/table/event',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Event/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.event.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.event.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.event.switch'),
                prop: 'switch',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('examples.table.event.switch 0'), '1': t('examples.table.event.switch 1') },
            },
            { label: t('examples.table.event.datetime'), prop: 'datetime', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            {
                label: t('examples.table.event.create_time'),
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
    },
    {
        // 示例核心代码(1/2)
        // 操作前置钩子，return false 可取消原操作
        getIndex() {
            console.info('%c-------获取表格数据前置--------', 'color:blue')
        },
        postDel({ ids }) {
            console.info('%c-------请求删除前置--------', 'color:blue')
            console.log('被删除数据', ids)
        },
        requestEdit({ id }) {
            console.info('%c-------请求编辑前置--------', 'color:blue')
            console.log('被编辑数据', id)
        },
        onTableDblclick({ row, column }) {
            console.info('%c-------双击了单元格，双击具体操作执行前置--------', 'color:blue')
            console.log('双击携带了数据', row, column)
        },
        toggleForm({ operate, operateIds }) {
            console.info('%c-------表单切换前置--------', 'color:blue')
            console.log('表单切换携带了数据', operate, operateIds)
        },
        onSubmit({ formEl, operate, items }) {
            console.info('%c-------表单提交前置（尚未请求提交）--------', 'color:blue')
            console.log('表单提交前携带了数据，您可以在此对数据进行预处理', formEl, operate, items)
        },
        onTableAction({ event, data }) {
            console.info('%c-------表格内部事件 - 前置--------', 'color:blue')
            console.log('触发的事件和携带的数据为', event, data)
        },
        onTableHeaderAction({ event, data }) {
            console.info('%c-------表头事件 - 前置--------', 'color:blue')
            console.log('触发的事件和携带的数据为', event, data)
        },
        mount() {
            console.info('%c-------表格初始化前置--------', 'color:blue')
        },
    },
    {
        // 示例核心代码(2/2)
        getIndex({ res }) {
            console.info('%c-------获取表格数据后置--------', 'color:green')
            console.log('获取数据请求的响应', res)
        },
        postDel({ res }) {
            console.info('%c-------请求删除后置--------', 'color:green')
            console.log('删除请求的响应', res)
        },
        requestEdit({ res }) {
            console.info('%c-------请求编辑后置--------', 'color:green')
            console.log('编辑请求的响应', res)
        },
        onTableDblclick({ row, column }) {
            console.info('%c-------双击了单元格，双击具体操作执行后置--------', 'color:green')
            console.log('双击携带了数据', row, column)
        },
        toggleForm({ operate, operateIds }) {
            console.info('%c-------表单切换后置--------', 'color:green')
            console.log('表单切换携带了数据', operate, operateIds)
        },
        onSubmit({ res }) {
            console.info('%c-------表单提交后置（已经发送请求）--------', 'color:green')
            console.log('表单提交请求的响应', res)
        },
        onTableAction({ event, data }) {
            console.info('%c-------表格内部事件 - 后置--------', 'color:green')
            console.log('触发的事件和携带的数据为', event, data)
        },
        onTableHeaderAction({ event, data }) {
            console.info('%c-------表头事件 - 后置--------', 'color:green')
            console.log('触发的事件和携带的数据为', event, data)
        },
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
