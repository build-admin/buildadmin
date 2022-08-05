<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('security.dataRecycle.Rule name') })"
            @action="baTable.onTableHeaderAction"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" @action="baTable.onTableAction" />

        <!-- 表单 -->
        <PopupForm ref="formRef" :form-data="addFormData" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, reactive, provide } from 'vue'
import baTableClass from '/@/utils/baTable'
import { securityDataRecycle } from '/@/api/controllerUrls'
import { add } from '/@/api/backend/security/dataRecycle'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const tableRef = ref()
const formRef = ref()
const baTable = new baTableClass(
    new baTableApi(securityDataRecycle),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: 'ID', prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query'), width: 70 },
            { label: t('security.dataRecycle.Rule name'), prop: 'name', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('security.dataRecycle.controller'),
                prop: 'controller',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.dataRecycle.data sheet'),
                prop: 'data_table',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
            },
            {
                label: t('security.dataRecycle.Data table primary key'),
                prop: 'primary_key',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                width: 100,
            },
            {
                label: t('state'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { '0': 'danger', '1': 'success' },
                replaceValue: { '0': t('Disable'), '1': t('security.dataRecycle.Deleting monitoring') },
            },
            { label: t('updatetime'), prop: 'updatetime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('createtime'), prop: 'createtime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            {
                label: t('operate'),
                align: 'center',
                width: '130',
                render: 'buttons',
                buttons: defaultOptButtons(['edit', 'delete']),
                operator: false,
            },
        ],
        dblClickNotEditColumn: [undefined, 'status'],
    },
    {
        defaultItems: {
            status: '1',
        },
    },
    {
        // 添加前获取控制器和数据表
        toggleForm: ({ operate }: { operate: string }) => {
            if (operate == 'add' || operate == 'edit') {
                baTable.form.loading = true
                add().then((res) => {
                    addFormData.tableList = res.data.tables
                    addFormData.controllerList = res.data.controllers
                    baTable.form.loading = false
                })
            }
        },
    }
)

const addFormData = reactive({
    tableList: {},
    controllerList: {},
})

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'security/dataRecycle',
})
</script>

<style scoped lang="scss"></style>
