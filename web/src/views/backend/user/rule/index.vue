<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold']"
            :quick-search-placeholder="'通过用户名和昵称模糊搜索'"
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
            { label: '标题', prop: 'title', align: 'left' },
            { label: '图标', prop: 'icon', align: 'center', width: '60', render: 'icon', default: 'el-icon-Minus' },
            { label: '名称', prop: 'name', align: 'center', 'show-overflow-tooltip': true },
            {
                label: '类型',
                prop: 'type',
                align: 'center',
                render: 'tag',
                custom: { menu: 'danger', menu_dir: 'success', route: 'info' },
                replaceValue: { menu: '会员中心菜单', menu_dir: '会员中心菜单目录', route: '普通路由' },
            },
            { label: '状态', prop: 'status', align: 'center', width: '80', render: 'switch' },
            { label: '更新时间', prop: 'updatetime', align: 'center', width: '160', render: 'datetime' },
            { label: '创建时间', prop: 'createtime', align: 'center', width: '160', render: 'datetime' },
            {
                label: '操作',
                align: 'center',
                width: '130',
                render: 'buttons',
                buttons: defaultOptButtons(),
            },
        ],
        dblClickNotEditColumn: [undefined, 'status'],
    },
    {
        defaultItems: {
            type: 'route',
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
    name: 'user/rule',
})
</script>

<style scoped lang="scss"></style>
