<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('modulesdev.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef" :pagination="false"></Table>

        <!-- 表单 -->
        <File />

        <!-- 管理 -->
        <Manage />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import File from './file.vue'
import Manage from './manage.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { auth } from '/@/utils/common'
import { ElNotification } from 'element-plus'

defineOptions({
    name: 'modulesdev',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = [
    {
        render: 'tipButton',
        name: 'file',
        title: 'modulesdev.file',
        text: '',
        type: 'success',
        icon: 'fa fa-th-list',
        class: 'table-row-file',
        disabledTip: false,
        click: (row) => {
            if (row['install_mode'] == 'pure') {
                ElNotification.error({
                    message: '请先于管理操作内将模块转为完整安装！',
                })
                return
            }
            baTable.form.extend!.uid = row.uid
            baTable.form.extend!.showFile = true
        },
        display() {
            return auth('dir')
        },
    },
    {
        render: 'tipButton',
        name: 'manage',
        title: 'modulesdev.manage',
        text: '',
        type: 'primary',
        icon: 'fa fa-th-large',
        class: 'table-row-manage',
        disabledTip: false,
        click: (row) => {
            baTable.form.extend!.info = row
            baTable.form.extend!.showManage = true
        },
        display() {
            return auth('manage')
        },
    },
]

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/Modulesdev/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('modulesdev.uid'), prop: 'uid', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: t('modulesdev.title'),
                prop: 'title',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('modulesdev.intro'),
                prop: 'intro',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                showOverflowTooltip: true,
            },
            {
                label: t('modulesdev.version'),
                prop: 'version',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('modulesdev.state'),
                prop: 'state',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                replaceValue: {
                    '0': t('modulesdev.state 0'),
                    '1': t('modulesdev.state 1'),
                    '2': t('modulesdev.state 2'),
                    '3': t('modulesdev.state 3'),
                    '4': t('modulesdev.state 4'),
                    '5': t('modulesdev.state 5'),
                    '6': t('modulesdev.state 6'),
                },
            },
            {
                label: t('modulesdev.install_mode'),
                prop: 'install_mode',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                custom: { full: '', pure: 'success' },
                replaceValue: { full: t('modulesdev.install_mode full'), pure: t('modulesdev.install_mode pure') },
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {},
    {
        onTableDblclick({ row }) {
            baTable.form.extend!.info = row
            baTable.form.extend!.showManage = true
            return false
        },
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
