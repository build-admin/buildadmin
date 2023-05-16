<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('auth.menu.Rule title') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, provide } from 'vue'
import baTableClass from '/@/utils/baTable'
import { userRule } from '/@/api/controllerUrls'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const tableRef = ref()
const baTable = new baTableClass(
    new baTableApi(userRule),
    {
        expandAll: true,
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('auth.menu.title'), prop: 'title', align: 'left', width: '200' },
            { label: t('auth.menu.Icon'), prop: 'icon', align: 'center', width: '60', render: 'icon', default: 'el-icon-Minus' },
            { label: t('auth.menu.name'), prop: 'name', align: 'center', 'show-overflow-tooltip': true },
            {
                label: t('auth.menu.type'),
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
            { label: t('state'), prop: 'status', align: 'center', width: '80', render: 'switch' },
            { label: t('updatetime'), prop: 'updatetime', align: 'center', width: '160', render: 'datetime' },
            { label: t('createtime'), prop: 'createtime', align: 'center', width: '160', render: 'datetime' },
            { label: t('operate'), align: 'center', width: '130', render: 'buttons', buttons: defaultOptButtons() },
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
            status: '1',
            icon: 'el-icon-Minus',
        },
    },
    {
        // 获得编辑数据后
        requestEdit: () => {
            if (baTable.form.items && !baTable.form.items.icon) baTable.form.items.icon = 'el-icon-Minus'
        },
        onSubmit: () => {
            if (baTable.form.items!.type == 'route') {
                baTable.form.items!.menu_type = 'tab'
            } else if (['menu', 'menu_dir', 'nav_user_menu'].includes(baTable.form.items!.type)) {
                baTable.form.items!.no_login_valid = '0'
            }
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
    name: 'user/rule',
})
</script>

<style scoped lang="scss"></style>
