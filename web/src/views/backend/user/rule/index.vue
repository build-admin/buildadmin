<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('auth.rule.Rule title') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, useTemplateRef } from 'vue'
import baTableClass from '/@/utils/baTable'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'

defineOptions({
    name: 'user/rule',
})

const { t } = useI18n()
const tableRef = useTemplateRef('tableRef')

const baTable = new baTableClass(
    new baTableApi('/admin/user.Rule/'),
    {
        expandAll: false,
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('auth.rule.title'), prop: 'title', align: 'left', width: '200' },
            { label: t('auth.rule.Icon'), prop: 'icon', align: 'center', width: '60', render: 'icon', default: 'fa fa-circle-o' },
            { label: t('auth.rule.name'), prop: 'name', align: 'center', showOverflowTooltip: true },
            {
                label: t('auth.rule.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                custom: { menu: 'danger', menu_dir: 'success', route: 'info' },
                replaceValue: {
                    menu: t('user.rule.Member center menu items'),
                    menu_dir: t('user.rule.Member center menu contents'),
                    route: t('user.rule.Normal routing'),
                    nav: t('user.rule.Top bar menu items'),
                    button: t('user.rule.Page button'),
                    nav_user_menu: t('user.rule.Top bar user dropdown'),
                },
            },
            { label: t('State'), prop: 'status', align: 'center', width: '80', render: 'switch' },
            { label: t('Update time'), prop: 'update_time', align: 'center', width: '160', render: 'datetime' },
            { label: t('Create time'), prop: 'create_time', align: 'center', width: '160', render: 'datetime' },
            { label: t('Operate'), align: 'center', width: '130', render: 'buttons', buttons: defaultOptButtons() },
        ],
        dblClickNotEditColumn: [undefined, 'status'],
    },
    {
        defaultItems: {
            type: 'route',
            menu_type: 'tab',
            extend: 'none',
            no_login_valid: '0',
            keepalive: 0,
            status: 1,
            icon: 'fa fa-circle-o',
        },
    }
)

// 表单提交前
baTable.before.onSubmit = () => {
    if (baTable.form.items!.type == 'route') {
        baTable.form.items!.menu_type = 'tab'
    } else if (['menu', 'menu_dir', 'nav_user_menu'].includes(baTable.form.items!.type)) {
        baTable.form.items!.no_login_valid = '0'
    }
}

// 取得编辑行的数据后
baTable.after.getEditData = () => {
    if (baTable.form.items && !baTable.form.items.icon) {
        baTable.form.items.icon = 'fa fa-circle-o'
    }
}

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getData()?.then(() => {
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
