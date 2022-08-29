<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('auth.menu.title') })"
            @action="baTable.onTableHeaderAction"
        />
        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" @action="baTable.onTableAction" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
import { authMenu } from '/@/api/controllerUrls'
import PopupForm from './popupForm.vue'
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
        dblClickNotEditColumn: [undefined, 'keepalive', 'status'],
        column: [
            { type: 'selection', align: 'center' },
            { label: t('auth.menu.title'), prop: 'title', align: 'left' },
            { label: t('auth.menu.Icon'), prop: 'icon', align: 'center', width: '60', render: 'icon', default: 'el-icon-Minus' },
            { label: t('auth.menu.name'), prop: 'name', align: 'center', 'show-overflow-tooltip': true },
            {
                label: t('auth.menu.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                custom: { menu: 'danger', menu_dir: 'success', button: 'info' },
                replaceValue: { menu: t('auth.menu.type menu'), menu_dir: t('auth.menu.type menu_dir'), button: t('auth.menu.type button') },
            },
            { label: t('auth.menu.cache'), prop: 'keepalive', align: 'center', width: '80', render: 'switch' },
            { label: t('state'), prop: 'status', align: 'center', width: '80', render: 'switch' },
            { label: t('updatetime'), prop: 'updatetime', align: 'center', width: '160', render: 'datetime' },
            {
                label: t('operate'),
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

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'auth/menu',
})
</script>

<style scoped lang="scss"></style>
