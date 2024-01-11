<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" type="info" show-icon>
            <template #default>
                <div class="ba-markdown">
                    <div><b>钩子的使用</b>，利用<code>baTableClass</code>内置的各种操作前后<b>钩子</b>，实现获取到被编辑数据后，对数据预处理</div>
                    <div>1、您可以按下<code>F12</code>从浏览器控制台查看更多细节</div>
                    <div>2、本示例通过<code>CRUD</code>生成，随后仅对<code>index.vue</code>和<code>popupForm.vue</code>进行改动</div>
                </div>
            </template>
        </el-alert>

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.form.edit.quick Search Fields') })"
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
    name: 'examples/table/form/edit',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.form.Edit/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.form.edit.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.form.edit.string'),
                prop: 'string',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { string: '我是 string 字段的添加默认值' },
    },
    {
        // 示例核心代码(1/3)
        requestEdit: ({ id }) => {
            console.info('%c-------请求编辑前--------', 'color:blue')
            console.log('此时还未开始请求，return false 可取消请求，被编辑的行主键为：', id)
        },

        // 还有其他 操作前置钩子，请看文档
    },
    {
        // 示例核心代码(2/3)
        requestEdit({ res }) {
            console.info('%c-------请求编辑后--------', 'color:blue')
            console.log('此时已经从服务端拿到了被请求的数据：', res)
            console.log('根据请求到的数据，初始化了表单项：', baTable.form.items)
            console.info('服务端亦可通过模型、控制器对数据进行预处理再返回给前端')

            baTable.form.items!.string += ' -- 我是数据预处理时加的文字'
        },

        // 还有其他 操作后置钩子，请看文档
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
