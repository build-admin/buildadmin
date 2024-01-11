<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" type="info" show-icon>
            <template #default>
                <div class="ba-markdown">
                    <div>您可以按下<code>F12</code>从浏览器控制台查看更多细节</div>
                </div>
            </template>
        </el-alert>

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.dialog.quick Search Fields') })"
        ></TableHeader>

        <Table ref="tableRef"></Table>

        <PopupForm />

        <!-- 示例核心代码(1/3) -->
        <!-- 详情组件在此处使用，但显示与否的判断是写在组件内的 -->
        <Info />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Info from './info.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

defineOptions({
    name: 'examples/table/dialog',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * 示例核心代码(2/3)
 * 表格操作按钮组 optButtons 只是个普通的数组，此处向其 push 一个 OptButton
 */
optButtons.push({
    render: 'tipButton',
    // name 是任意的
    name: 'info',
    // title 是语言翻译 key
    title: 'examples.table.dialog.infoBtnTitle',
    text: '',
    type: 'warning',
    icon: 'fa fa-search-plus icon',
    click(row, field) {
        console.info('%c-------详情按钮被点击了--------', 'color:blue')
        console.log('接受到行数据和列数据', row, field)
        console.log('%c赋值：baTable.table.extend!.showInfo = true', 'color:red')

        // 在 extend 上自定义一个变量标记详情弹窗显示状态，详情组件内以此判断显示即可！
        baTable.table.extend!.showInfo = true

        // 您也可以使用 baTable.form.operate，默认情况它有三个值`Add、Edit、空字符串`，前两个值将显示添加和编辑弹窗

        // 您也可以再来个 loading 态，然后请求详情数据等
        baTable.table.extend!.infoLoading = true
        setTimeout(() => {
            baTable.table.extend!.infoData = row
            baTable.table.extend!.infoLoading = false
        }, 1000)
    },
})

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.Dialog/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.dialog.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.dialog.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('examples.table.dialog.switch'),
                prop: 'switch',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('examples.table.dialog.switch 0'), '1': t('examples.table.dialog.switch 1') },
            },
            { label: t('examples.table.dialog.datetime'), prop: 'datetime', align: 'center', operator: 'eq', sortable: 'custom', width: 160 },
            {
                label: t('examples.table.dialog.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            // 加了按钮，您可以将 width 设置得更大一点
            { label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
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
