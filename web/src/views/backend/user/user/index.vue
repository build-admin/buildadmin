<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('user.user.User name') + '/' + t('user.user.nickname') })"
            @action="baTable.onTableHeaderAction"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" @action="baTable.onTableAction" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide } from 'vue'
import baTableClass from '/@/utils/baTable'
import { userUser } from '/@/api/controllerUrls'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const tableRef = ref()
const baTable = new baTableClass(
    new baTableApi(userUser),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('id'), prop: 'id', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query'), width: 70 },
            { label: t('user.user.User name'), prop: 'username', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            { label: t('user.user.nickname'), prop: 'nickname', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('user.user.grouping'),
                prop: 'group.name',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tag',
            },
            { label: t('user.user.head portrait'), prop: 'avatar', align: 'center', render: 'image', operator: false },
            {
                label: t('user.user.Gender'),
                prop: 'gender',
                align: 'center',
                render: 'tag',
                custom: { '0': 'info', '1': '', '2': 'success' },
                replaceValue: { '0': t('unknown'), '1': t('user.user.male'), '2': t('user.user.female') },
            },
            { label: t('user.user.mobile'), prop: 'mobile', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('user.user.Last login IP'),
                prop: 'lastloginip',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tag',
            },
            {
                label: t('user.user.Last login'),
                prop: 'lastlogintime',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            { label: t('createtime'), prop: 'createtime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            {
                label: t('state'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { disable: 'danger', enable: 'success' },
                replaceValue: { disable: t('Disable'), enable: t('Enable') },
            },
            {
                label: t('operate'),
                align: 'center',
                width: '100',
                render: 'buttons',
                buttons: defaultOptButtons(['edit', 'delete']),
                operator: false,
            },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {
            gender: 0,
            money: '0',
            score: '0',
            status: 'enable',
        },
    }
)

baTable.mount()
baTable.getIndex()

provide('baTable', baTable)
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'user/user',
})
</script>

<style scoped lang="scss"></style>
