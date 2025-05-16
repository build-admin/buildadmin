<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('security.sensitiveData.controller') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm ref="formRef" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, useTemplateRef } from 'vue'
import { sensitiveDataClass } from './index'
import { url } from '/@/api/backend/security/sensitiveData'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'

defineOptions({
    name: 'security/dataRecycle',
})

const { t } = useI18n()
const formRef = useTemplateRef('formRef')
const tableRef = useTemplateRef('tableRef')

const baTable = new sensitiveDataClass(
    new baTableApi(url),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: 'ID', prop: 'id', align: 'center', operator: '=', operatorPlaceholder: t('Id'), width: 70 },
            { label: t('security.sensitiveData.Rule name'), prop: 'name', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('security.sensitiveData.controller'),
                prop: 'controller',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('Connection'),
                prop: 'connection',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.sensitiveData.data sheet'),
                prop: 'data_table',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.sensitiveData.Data table primary key'),
                prop: 'primary_key',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                width: 100,
            },
            {
                label: t('security.sensitiveData.Sensitive fields'),
                prop: 'data_fields',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
            },
            {
                label: t('State'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { 0: 'danger', 1: 'success' },
                replaceValue: { 0: t('Disable'), 1: t('security.sensitiveData.Modifying monitoring') },
            },
            { label: t('Update time'), prop: 'update_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('Create time'), prop: 'create_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            {
                label: t('Operate'),
                align: 'center',
                width: '130',
                render: 'buttons',
                buttons: defaultOptButtons(['edit', 'delete']),
                operator: false,
            },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {
            status: 1,
        },
    }
)

baTable.before.onSubmit = () => {
    baTable.form.items!.fields = formRef.value?.getDataFields()
}

provide('baTable', baTable)

onMounted(() => {
    baTable.form.extend!.parentRef = formRef.value
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getData()
})
</script>

<style scoped lang="scss"></style>
