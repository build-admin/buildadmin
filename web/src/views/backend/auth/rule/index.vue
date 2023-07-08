<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('auth.rule.title') })"
        />
        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { useI18n } from 'vue-i18n'
import { baTableApi } from '/@/api/common'
import baTableClass from '/@/utils/baTable'

defineOptions({
    name: 'auth/rule',
})

const { t } = useI18n()
const tableRef = ref()
const baTable = new baTableClass(
    new baTableApi('/admin/auth.Rule/'),
    {
        expandAll: false,
        dblClickNotEditColumn: [undefined, 'keepalive', 'status'],
        column: [
            { type: 'selection', align: 'center' },
            { label: t('auth.rule.title'), prop: 'title', align: 'left', width: '200' },
            { label: t('auth.rule.Icon'), prop: 'icon', align: 'center', width: '60', render: 'icon', default: 'el-icon-Minus' },
            { label: t('auth.rule.name'), prop: 'name', align: 'center', showOverflowTooltip: true },
            {
                label: t('auth.rule.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                custom: { menu: 'danger', menu_dir: 'success', button: 'info' },
                replaceValue: { menu: t('auth.rule.type menu'), menu_dir: t('auth.rule.type menu_dir'), button: t('auth.rule.type button') },
            },
            { label: t('auth.rule.cache'), prop: 'keepalive', align: 'center', width: '80', render: 'switch' },
            { label: t('State'), prop: 'status', align: 'center', width: '80', render: 'switch' },
            { label: t('Update time'), prop: 'update_time', align: 'center', width: '160', render: 'datetime' },
            {
                label: t('Operate'),
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
            keepalive: 0,
            status: '1',
            icon: 'el-icon-Minus',
        },
    },
    {
        getIndex: () => {
            baTable.table.expandAll = baTable.table.filter?.quickSearch ? true : false
        },
        // 获得编辑数据后
        requestEdit: () => {
            if (baTable.form.items && !baTable.form.items.icon) baTable.form.items.icon = 'el-icon-Minus'
        },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
