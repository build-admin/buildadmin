<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" type="info" show-icon>
            <template #default>
                <div class="ba-markdown">
                    <div><b>钩子的使用</b>，利用<code>baTableClass</code>内置的各种操作前后<b>钩子</b>，实现提交数据至服务端前，对数据进行预处理</div>
                    <div>1、您可以按下<code>F12</code>从浏览器控制台查看更多细节</div>
                    <div>2、本示例通过<code>CRUD</code>生成，随后仅对<code>index.vue</code>和<code>popupForm.vue</code>进行改动</div>
                </div>
            </template>
        </el-alert>

        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('examples.table.form.submit.quick Search Fields') })"
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
    name: 'examples/table/form/submit',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const baTable = new baTableClass(
    new baTableApi('/admin/examples.table.form.Submit/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('examples.table.form.submit.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('examples.table.form.submit.string'),
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
        defaultItems: { string: '我是添加默认值' },
    },
    {
        /**
         * 示例核心代码(1/1)
         * 此方法就是提交前的钩子，若 return false，可取消提交
         * 原实现位于 utils/baTable.ts 的 onSubmit，可在此处完全重写提交方法，并 return false
         */
        onSubmit({ formEl, operate, items }) {
            console.info('%c-------提交请求前--------', 'color:blue')
            console.log(`本次提交的操作标识符号：%c${operate}`, 'color:blue')
            console.log('本次提交的数据为：', items)
            console.log('表单 Ref：', formEl)

            baTable.form.items!.string += ' -- 我是由提交前钩子添加的'
        },

        // 还有其他 操作前置钩子，请看文档
    },
    {
        onSubmit({ res }) {
            console.info('%c-------提交请求后--------', 'color:blue')
            console.log('此处提交请求已经发送完成', res)
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
